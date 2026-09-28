<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('app.admin.index');
        }

        $orders = $user->isPublisher() ? $user->publisherOrders() : $user->buyerOrders();

        return view('app.dashboard', [
            'balance' => $user->balance(),
            'openOrders' => (clone $orders)->whereIn('status', Order::OPEN_STATUSES)->count(),
            'completedOrders' => (clone $orders)->where('status', Order::COMPLETED)->count(),
            'toHandle' => $user->isPublisher()
                ? $user->publisherOrders()->whereIn('status', [Order::PENDING, Order::ACCEPTED])->with('site')->latest()->get()
                : $user->buyerOrders()->where('status', Order::PUBLISHED)->with('site')->latest()->get(),
            'sites' => $user->isPublisher() ? $user->sites()->latest()->get() : collect(),
        ]);
    }
}
