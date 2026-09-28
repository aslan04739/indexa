<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Site;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Every order state change goes through here, so the wallet ledger always matches order state.
 *
 * pending → accepted → published → completed
 *    ↘ refused / cancelled (refund)   published → disputed → completed | cancelled (refund)
 */
class OrderWorkflow
{
    public function __construct(private Wallet $wallet, private LinkChecker $checker) {}

    /** @param array{target_url: string, anchor_text: string, content?: ?string, brief?: ?string} $data */
    public function place(User $buyer, Site $site, array $data): Order
    {
        return DB::transaction(function () use ($buyer, $site, $data) {
            $order = new Order($data);
            $order->buyer()->associate($buyer);
            $order->publisher()->associate($site->user);
            $order->site()->associate($site);
            $order->publisher_price = $site->price_dzd;
            $order->buyer_price = $site->buyerPrice();
            $order->status = Order::PENDING;
            $order->deadline_at = now()->addDays(config('marketplace.accept_within_days'));
            $order->save();

            // Throws InsufficientFunds and rolls the order back.
            $this->wallet->debit($buyer, $order->buyer_price, WalletTransaction::ORDER_PAYMENT, $order, 'Order #'.$order->id);

            return $order;
        });
    }

    public function accept(Order $order): void
    {
        $this->expect($order, Order::PENDING);
        $order->forceFill([
            'status' => Order::ACCEPTED,
            'accepted_at' => now(),
            'deadline_at' => now()->addDays($order->site->turnaround_days),
        ])->save();
    }

    public function refuse(Order $order, ?string $reason): void
    {
        $this->expect($order, Order::PENDING, Order::ACCEPTED);
        $this->closeWithRefund($order, Order::REFUSED, ['refusal_reason' => $reason]);
    }

    /** The publisher submits the live URL. It is accepted only if the link is really there. */
    public function publish(Order $order, string $url): LinkCheckResult
    {
        $this->expect($order, Order::ACCEPTED);

        $result = $this->checker->check($url, $order->target_url);
        $this->storeCheck($order, $result);

        if ($result->ok()) {
            $order->forceFill([
                'status' => Order::PUBLISHED,
                'published_url' => $url,
                'published_at' => now(),
                'deadline_at' => now()->addDays(config('marketplace.auto_validate_days')),
            ])->save();
        }

        return $result;
    }

    public function complete(Order $order): void
    {
        $this->expect($order, Order::PUBLISHED, Order::DISPUTED);

        DB::transaction(function () use ($order) {
            $order->forceFill([
                'status' => Order::COMPLETED,
                'completed_at' => now(),
                'deadline_at' => null,
                'monitor_until' => now()->addMonths(config('marketplace.monitor_months')),
            ])->save();

            $this->wallet->credit($order->publisher, $order->publisher_price, WalletTransaction::EARNING, $order, 'Order #'.$order->id);
        });
    }

    public function dispute(Order $order, string $reason): void
    {
        $this->expect($order, Order::PUBLISHED);
        $order->forceFill(['status' => Order::DISPUTED, 'dispute_reason' => $reason, 'deadline_at' => null])->save();
    }

    public function cancel(Order $order): void
    {
        $this->expect($order, Order::PENDING, Order::ACCEPTED, Order::DISPUTED);
        $this->closeWithRefund($order, Order::CANCELLED);
    }

    public function recheck(Order $order): LinkCheckResult
    {
        $result = $this->checker->check($order->published_url, $order->target_url);
        $this->storeCheck($order, $result);

        return $result;
    }

    /** Scheduled: cancel orders the publisher did not handle in time, auto-complete unanswered publications. */
    public function processDeadlines(): array
    {
        $cancelled = 0;
        $completed = 0;

        Order::whereIn('status', [Order::PENDING, Order::ACCEPTED])->where('deadline_at', '<', now())->each(function (Order $order) use (&$cancelled) {
            $this->cancel($order);
            $cancelled++;
        });

        Order::where('status', Order::PUBLISHED)->where('deadline_at', '<', now())->each(function (Order $order) use (&$completed) {
            $this->complete($order);
            $completed++;
        });

        return compact('cancelled', 'completed');
    }

    private function closeWithRefund(Order $order, string $status, array $extra = []): void
    {
        DB::transaction(function () use ($order, $status, $extra) {
            $order->forceFill(['status' => $status, 'deadline_at' => null] + $extra)->save();
            $this->wallet->credit($order->buyer, $order->buyer_price, WalletTransaction::REFUND, $order, 'Refund order #'.$order->id);
        });
    }

    private function storeCheck(Order $order, LinkCheckResult $result): void
    {
        $order->forceFill([
            'link_status' => $result->status,
            'link_rel' => $result->rel,
            'link_check_detail' => $result->detail,
            'last_checked_at' => now(),
        ])->save();
    }

    private function expect(Order $order, string ...$statuses): void
    {
        if (! in_array($order->status, $statuses, true)) {
            throw new LogicException("Order #{$order->id} is {$order->status}, expected ".implode('|', $statuses));
        }
    }
}
