@if ($orders->isEmpty())
    <p class="text-muted">{{ __('Aucune commande.') }}</p>
@else
    <div class="overflow-x-auto rounded border border-line bg-white">
        <table class="w-full text-sm">
            <thead class="bg-paper text-start text-xs uppercase text-muted">
                <tr><th class="p-3 text-start">#</th><th class="p-3 text-start">{{ __('Site') }}</th><th class="p-3 text-start">{{ __('Ancre') }}</th><th class="p-3 text-start">{{ __('Prix') }}</th><th class="p-3 text-start">{{ __('Statut') }}</th><th class="p-3 text-start">{{ __('Échéance') }}</th></tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr class="border-t border-line">
                        <td class="p-3"><a class="text-brand underline" href="{{ route('app.orders.show', $order) }}">{{ $order->id }}</a></td>
                        <td class="p-3">{{ $order->site->domain }}</td>
                        <td class="p-3">{{ $order->anchor_text }}</td>
                        <td class="p-3 tabular-nums">{{ dzd(auth()->user()->isPublisher() ? $order->publisher_price : $order->buyer_price) }}</td>
                        <td class="p-3"><x-status :status="$order->status" /></td>
                        <td class="p-3 tabular-nums">{{ $order->deadline_at?->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
