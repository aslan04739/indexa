<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Site;
use App\Services\InsufficientFunds;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderWorkflow $workflow) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->isAdmin() ? Order::query() : ($user->isPublisher() ? $user->publisherOrders() : $user->buyerOrders());

        return view('app.orders.index', [
            'orders' => $query->with('site')->latest()->paginate(30),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeParty($request, $order);

        return view('app.orders.show', ['order' => $order->load('site', 'buyer', 'publisher')]);
    }

    public function create(Request $request, Site $site): View
    {
        abort_unless($site->status === Site::APPROVED && $site->verified_at, 404);

        return view('app.orders.create', ['site' => $site, 'balance' => $request->user()->balance()]);
    }

    public function store(Request $request, Site $site): RedirectResponse
    {
        abort_unless($site->status === Site::APPROVED && $site->verified_at, 404);

        $data = $request->validate([
            'target_url' => ['required', 'url:http,https', 'max:255'],
            'anchor_text' => ['required', 'string', 'max:120'],
            'content' => ['nullable', 'required_without:brief', 'string', 'max:20000'],
            'brief' => ['nullable', 'required_without:content', 'string', 'max:5000'],
        ]);

        try {
            $order = $this->workflow->place($request->user(), $site, $data);
        } catch (InsufficientFunds) {
            return back()->withInput()->withErrors(['balance' => __('Solde insuffisant. Rechargez votre portefeuille pour passer cette commande.')]);
        }

        return redirect()->route('app.orders.show', $order)->with('status', __('Commande envoyée à l\'éditeur.'));
    }

    public function accept(Request $request, Order $order): RedirectResponse
    {
        $this->authorizePublisher($request, $order);
        $this->workflow->accept($order);

        return back()->with('status', __('Commande acceptée.'));
    }

    public function refuse(Request $request, Order $order): RedirectResponse
    {
        $this->authorizePublisher($request, $order);
        $reason = $request->validate(['refusal_reason' => ['nullable', 'string', 'max:255']])['refusal_reason'] ?? null;
        $this->workflow->refuse($order, $reason);

        return back()->with('status', __('Commande refusée. L\'annonceur a été remboursé.'));
    }

    public function publish(Request $request, Order $order): RedirectResponse
    {
        $this->authorizePublisher($request, $order);
        $url = $request->validate(['published_url' => ['required', 'url:http,https', 'max:255']])['published_url'];

        $result = $this->workflow->publish($order, $url);

        return $result->ok()
            ? back()->with('status', __('Publication vérifiée. L\'annonceur a été notifié.'))
            : back()->withInput()->withErrors(['published_url' => __('Vérification échouée : :detail', ['detail' => __('link.'.$result->status)])]);
    }

    public function validateOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBuyer($request, $order);
        $this->workflow->complete($order);

        return back()->with('status', __('Commande validée. Le lien sera surveillé pendant 12 mois.'));
    }

    public function dispute(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBuyer($request, $order);
        $reason = $request->validate(['dispute_reason' => ['required', 'string', 'max:2000']])['dispute_reason'];
        $this->workflow->dispute($order, $reason);

        return back()->with('status', __('Litige ouvert. L\'équipe Indexa va l\'examiner.'));
    }

    public function recheck(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeParty($request, $order);
        abort_unless($order->published_url, 404);
        $this->workflow->recheck($order);

        return back()->with('status', __('Lien vérifié.'));
    }

    private function authorizeParty(Request $request, Order $order): void
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || in_array($user->id, [$order->buyer_id, $order->publisher_id], true), 404);
    }

    private function authorizeBuyer(Request $request, Order $order): void
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
    }

    private function authorizePublisher(Request $request, Order $order): void
    {
        abort_unless($order->publisher_id === $request->user()->id, 404);
    }
}
