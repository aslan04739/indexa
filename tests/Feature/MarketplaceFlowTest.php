<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\Site;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(int $balance = 0): User
    {
        $buyer = User::factory()->create(['role' => User::BUYER]);
        if ($balance) {
            app(Wallet::class)->credit($buyer, $balance, WalletTransaction::TOPUP);
        }

        return $buyer;
    }

    private function articleWithLink(string $target, string $rel = 'sponsored', string $head = ''): string
    {
        return "<html><head>$head</head><body><p>Texte <a href=\"$target\" rel=\"$rel\">ancre</a></p></body></html>";
    }

    public function test_register_as_publisher_then_submit_verify_and_get_approved(): void
    {
        $this->post('/app/register', [
            'name' => 'Amine', 'email' => 'amine@example.dz', 'password' => 'secret-pass', 'password_confirmation' => 'secret-pass', 'role' => 'publisher',
        ])->assertRedirect(route('app.sites.create'));

        $this->post('/app/sites', [
            'name' => 'Mon Blog', 'url' => 'https://www.monblog.dz/', 'language' => 'fr', 'category' => 'tech',
            'price_dzd' => 10000, 'link_attribute' => 'sponsored', 'turnaround_days' => 5,
        ])->assertRedirect();

        $site = Site::firstOrFail();
        $this->assertSame('monblog.dz', $site->domain);

        Http::fake(['https://www.monblog.dz/' => Http::sequence()
            ->push('<html><head></head></html>')
            ->push('<html><head>'.$site->verificationMetaTag().'</head></html>')]);
        $this->post(route('app.sites.verify', $site))->assertSessionHasErrors('verify');
        $this->assertNull($site->fresh()->verified_at);

        $this->post(route('app.sites.verify', $site))->assertSessionHasNoErrors();
        $this->assertNotNull($site->fresh()->verified_at);

        $admin = User::factory()->create(['role' => User::ADMIN]);
        $this->actingAs($admin)->post(route('app.admin.sites.approve', $site), ['domain_rating' => 22])->assertRedirect();
        $this->assertSame(Site::APPROVED, $site->fresh()->status);
    }

    public function test_full_order_lifecycle_moves_money_correctly(): void
    {
        $site = Site::factory()->approved()->create(['price_dzd' => 10000]);
        $publisher = $site->user;
        $buyer = $this->buyer(50000);

        // Buyer pays publisher price + 20% commission.
        $this->actingAs($buyer)->post(route('app.orders.store', $site), [
            'target_url' => 'https://client.dz/offre', 'anchor_text' => 'offre client', 'brief' => 'Article sur notre offre',
        ])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame(12000, $order->buyer_price);
        $this->assertSame(38000, $buyer->balance());

        $this->actingAs($publisher)->post(route('app.orders.accept', $order))->assertRedirect();
        $this->assertSame(Order::ACCEPTED, $order->fresh()->status);

        // Publication without the link is rejected.
        Http::fake([
            'https://site.dz/article-1' => Http::response('<html><body>no link</body></html>'),
            'https://site.dz/article-2' => Http::response($this->articleWithLink('https://www.client.dz/offre/')),
        ]);
        $this->post(route('app.orders.publish', $order), ['published_url' => 'https://site.dz/article-1'])->assertSessionHasErrors('published_url');
        $this->assertSame(Order::ACCEPTED, $order->fresh()->status);

        // Publication with the link (trailing slash and www. differences tolerated) passes.
        $this->post(route('app.orders.publish', $order), ['published_url' => 'https://site.dz/article-2'])->assertSessionHasNoErrors();
        $order->refresh();
        $this->assertSame(Order::PUBLISHED, $order->status);
        $this->assertSame('sponsored', $order->link_rel);

        $this->assertSame(0, $publisher->balance());
        $this->actingAs($buyer)->post(route('app.orders.validate', $order))->assertRedirect();
        $this->assertSame(Order::COMPLETED, $order->fresh()->status);
        $this->assertSame(10000, $publisher->balance());
        $this->assertNotNull($order->fresh()->monitor_until);
    }

    public function test_order_is_refused_when_balance_is_too_low(): void
    {
        $site = Site::factory()->approved()->create(['price_dzd' => 10000]);
        $buyer = $this->buyer(5000);

        $this->actingAs($buyer)->post(route('app.orders.store', $site), [
            'target_url' => 'https://client.dz', 'anchor_text' => 'client', 'brief' => 'brief',
        ])->assertSessionHasErrors('balance');

        $this->assertSame(0, Order::count());
        $this->assertSame(5000, $buyer->balance());
    }

    public function test_publisher_refusal_refunds_the_buyer(): void
    {
        $site = Site::factory()->approved()->create(['price_dzd' => 10000]);
        $buyer = $this->buyer(12000);
        $this->actingAs($buyer)->post(route('app.orders.store', $site), ['target_url' => 'https://client.dz', 'anchor_text' => 'a', 'brief' => 'b']);
        $order = Order::firstOrFail();

        $this->actingAs($site->user)->post(route('app.orders.refuse', $order), ['refusal_reason' => 'Hors thématique']);

        $this->assertSame(Order::REFUSED, $order->fresh()->status);
        $this->assertSame(12000, $buyer->balance());
    }

    public function test_deadlines_cancel_stale_orders_and_auto_complete_publications(): void
    {
        $site = Site::factory()->approved()->create(['price_dzd' => 10000]);
        $buyer = $this->buyer(24000);
        $this->actingAs($buyer)->post(route('app.orders.store', $site), ['target_url' => 'https://a.dz', 'anchor_text' => 'a', 'brief' => 'b']);
        $this->actingAs($buyer)->post(route('app.orders.store', $site), ['target_url' => 'https://b.dz', 'anchor_text' => 'b', 'brief' => 'b']);
        [$stale, $published] = Order::orderBy('id')->get()->all();
        $published->forceFill(['status' => Order::PUBLISHED, 'published_url' => 'https://site.dz/x'])->save();

        $this->travel(6)->days();
        $this->artisan('orders:deadlines')->assertSuccessful();

        $this->assertSame(Order::CANCELLED, $stale->fresh()->status);
        $this->assertSame(Order::COMPLETED, $published->fresh()->status);
        $this->assertSame(12000, $buyer->balance());
        $this->assertSame(10000, $site->user->balance());
    }

    public function test_dispute_resolved_by_admin_refund(): void
    {
        $site = Site::factory()->approved()->create(['price_dzd' => 10000]);
        $buyer = $this->buyer(12000);
        $this->actingAs($buyer)->post(route('app.orders.store', $site), ['target_url' => 'https://a.dz', 'anchor_text' => 'a', 'brief' => 'b']);
        $order = Order::firstOrFail();
        $order->forceFill(['status' => Order::PUBLISHED, 'published_url' => 'https://site.dz/x'])->save();

        $this->actingAs($buyer)->post(route('app.orders.dispute', $order), ['dispute_reason' => 'Lien en nofollow au lieu de sponsored']);
        $this->assertSame(Order::DISPUTED, $order->fresh()->status);

        $admin = User::factory()->create(['role' => User::ADMIN]);
        $this->actingAs($admin)->post(route('app.admin.orders.resolve', $order), ['decision' => 'refund']);
        $this->assertSame(Order::CANCELLED, $order->fresh()->status);
        $this->assertSame(12000, $buyer->balance());
    }

    public function test_link_monitoring_flags_removed_and_noindexed_links(): void
    {
        $order = $this->completedOrder();

        Http::fake(['https://site.dz/x' => Http::sequence()
            ->push($this->articleWithLink('https://a.dz', 'nofollow', '<meta name="robots" content="noindex">'))
            ->push('gone', 404)]);
        $this->artisan('links:check')->assertSuccessful();
        $this->assertSame('noindex', $order->fresh()->link_status);

        $this->artisan('links:check');
        $this->assertSame('unreachable', $order->fresh()->link_status);
    }

    public function test_other_users_cannot_act_on_an_order(): void
    {
        $order = $this->completedOrder();
        $stranger = $this->buyer(0);

        $this->actingAs($stranger)->get(route('app.orders.show', $order))->assertNotFound();
        $this->actingAs($stranger)->post(route('app.orders.validate', $order))->assertNotFound();
        $this->actingAs($order->buyer)->post(route('app.orders.accept', $order))->assertForbidden();
    }

    public function test_payout_request_debits_balance_and_rejection_recredits(): void
    {
        $publisher = User::factory()->create(['role' => User::PUBLISHER]);
        app(Wallet::class)->credit($publisher, 20000, WalletTransaction::EARNING);

        $this->actingAs($publisher)->post(route('app.payouts.store'), ['amount' => 25000, 'method' => 'ccp', 'account_details' => '0012345 67'])->assertSessionHasErrors('amount');
        $this->actingAs($publisher)->post(route('app.payouts.store'), ['amount' => 15000, 'method' => 'ccp', 'account_details' => '0012345 67'])->assertSessionHasNoErrors();
        $this->assertSame(5000, $publisher->balance());

        $admin = User::factory()->create(['role' => User::ADMIN]);
        $payout = PayoutRequest::firstOrFail();
        $this->actingAs($admin)->post(route('app.admin.payouts.reject', $payout), ['admin_note' => 'CCP invalide']);
        $this->assertSame(20000, $publisher->balance());
    }

    public function test_chargily_topup_credits_wallet_once_on_signed_webhook(): void
    {
        config(['marketplace.chargily.secret_key' => 'test_sk_123']);
        Http::fake(['pay.chargily.net/test/api/v2/checkouts' => Http::response(['id' => 'chk_01', 'checkout_url' => 'https://pay.chargily.dz/test/checkouts/chk_01/pay'])]);
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post(route('app.wallet.topup'), ['amount' => 20000])
            ->assertRedirect('https://pay.chargily.dz/test/checkouts/chk_01/pay');
        Http::assertSent(fn ($request) => $request['amount'] === 20000 && $request['currency'] === 'dzd' && $request->hasHeader('Authorization', 'Bearer test_sk_123'));

        $payload = json_encode(['type' => 'checkout.paid', 'data' => ['id' => 'chk_01', 'amount' => 20000, 'status' => 'paid']]);

        $this->call('POST', '/webhooks/chargily', [], [], [], ['HTTP_SIGNATURE' => 'forged', 'CONTENT_TYPE' => 'application/json'], $payload)->assertForbidden();
        $this->assertSame(0, $buyer->balance());

        $signature = hash_hmac('sha256', $payload, 'test_sk_123');
        $this->call('POST', '/webhooks/chargily', [], [], [], ['HTTP_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();
        $this->call('POST', '/webhooks/chargily', [], [], [], ['HTTP_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();

        $this->assertSame(20000, $buyer->balance());
        $payment = Payment::firstOrFail();
        $this->assertSame(Payment::PAID, $payment->status);

        $this->actingAs($buyer)->get(route('app.wallet'))->assertSee(route('app.wallet.invoice', $payment));
        $this->actingAs($buyer)->get(route('app.wallet.invoice', $payment))->assertOk()->assertSee($payment->invoiceNumber())->assertSee('chk_01');
        $this->actingAs($this->buyer())->get(route('app.wallet.invoice', $payment))->assertNotFound();
    }

    public function test_topup_without_gateway_is_refused_unless_fake_payments_enabled(): void
    {
        $buyer = $this->buyer();

        $this->actingAs($buyer)->post(route('app.wallet.topup'), ['amount' => 5000])->assertStatus(503);
        $this->assertSame(0, $buyer->balance());

        config(['marketplace.fake_payments' => true]);
        $this->actingAs($buyer)->post(route('app.wallet.topup'), ['amount' => 5000])->assertRedirect(route('app.wallet'));
        $this->assertSame(5000, $buyer->balance());
    }

    public function test_public_catalog_hides_domains_and_noindexes_filters(): void
    {
        Site::factory()->approved()->create(['domain' => 'secret-media.dz', 'category' => 'tech']);
        Site::factory()->create(['domain' => 'not-approved.dz']);

        $this->get('/fr/catalogue')->assertOk()->assertDontSee('secret-media.dz')->assertDontSee('not-approved.dz')
            ->assertDontSee('noindex, follow', false);
        $this->get('/fr/catalogue?category=tech')->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.url('/fr/catalogue').'">', false);

        $this->actingAs($this->buyer())->get('/app/catalog')->assertOk()->assertSee('secret-media.dz')->assertDontSee('not-approved.dz');
    }

    public function test_app_pages_are_noindexed_and_role_gated(): void
    {
        $this->get('/app')->assertRedirect(route('app.login'));
        $this->get('/app/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $buyer = $this->buyer();
        foreach (['/app', '/app/catalog', '/app/orders', '/app/wallet'] as $url) {
            $this->actingAs($buyer)->get($url)->assertOk();
        }
        $site = Site::factory()->approved()->create();
        $this->actingAs($buyer)->get(route('app.orders.create', $site))->assertOk();
        $this->actingAs($buyer)->get('/app/sites')->assertForbidden();
        $this->actingAs($buyer)->get('/app/admin')->assertForbidden();

        $publisher = User::factory()->create(['role' => User::PUBLISHER]);
        $own = Site::factory()->for($publisher)->create();
        foreach (['/app', '/app/sites', '/app/sites/create', "/app/sites/{$own->id}", "/app/sites/{$own->id}/edit", '/app/orders', '/app/wallet', '/app/payouts'] as $url) {
            $this->actingAs($publisher)->get($url)->assertOk();
        }

        $admin = User::factory()->create(['role' => User::ADMIN]);
        $this->actingAs($admin)->get('/app/admin')->assertOk();
    }

    public function test_order_pages_render_for_every_party_and_status(): void
    {
        $order = $this->completedOrder();
        $admin = User::factory()->create(['role' => User::ADMIN]);

        foreach (Order::OPEN_STATUSES + [9 => Order::COMPLETED] as $status) {
            $order->forceFill(['status' => $status])->save();
            foreach ([$order->buyer, $order->publisher, $admin] as $user) {
                $this->actingAs($user)->get(route('app.orders.show', $order))->assertOk();
            }
        }
    }

    public function test_app_is_translated_and_arabic_is_rtl(): void
    {
        $this->post('/app/locale', ['locale' => 'ar']);
        $this->get('/app/login')->assertSee('dir="rtl"', false)->assertSee('تسجيل الدخول');

        $this->post('/app/locale', ['locale' => 'en']);
        $this->get('/app/register')->assertSee('Create an account')->assertSee('dir="ltr"', false);
    }

    private function completedOrder(): Order
    {
        $site = Site::factory()->approved()->create(['price_dzd' => 10000]);
        $buyer = $this->buyer(12000);
        $this->actingAs($buyer)->post(route('app.orders.store', $site), ['target_url' => 'https://a.dz', 'anchor_text' => 'a', 'brief' => 'b']);
        $order = Order::firstOrFail();
        $order->forceFill(['status' => Order::COMPLETED, 'published_url' => 'https://site.dz/x', 'monitor_until' => now()->addYear()])->save();

        return $order;
    }
}
