@if ($sites->isEmpty())
    <p class="text-muted">{{ __('Aucun site ne correspond à ces critères.') }}</p>
@else
    <div class="overflow-x-auto rounded border border-line bg-white">
        <table class="w-full text-sm">
            <thead class="bg-paper text-xs uppercase text-muted">
                <tr>
                    <th class="p-3 text-start">{{ __('Site') }}</th>
                    <th class="p-3 text-start">{{ __('Langue') }}</th>
                    <th class="p-3 text-start">{{ __('Thématique') }}</th>
                    <th class="p-3 text-start">{{ __('Trafic mensuel') }}</th>
                    <th class="p-3 text-start">DR</th>
                    <th class="p-3 text-start">{{ __('Type de lien') }}</th>
                    <th class="p-3 text-start">{{ __('Délai') }}</th>
                    <th class="p-3 text-start">{{ __('Prix') }}</th>
                    @if ($showDomain)<th class="p-3"></th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($sites as $site)
                    <tr class="border-t border-line">
                        <td class="p-3">
                            @if ($showDomain)
                                <span class="font-semibold">{{ $site->domain }}</span><br><span class="text-muted">{{ $site->name }}</span>
                            @else
                                {{-- Domains are shown to registered buyers only, so deals stay on the platform. --}}
                                <span class="font-semibold">{{ __('category.'.$site->category) }} #{{ $site->id }}</span>
                            @endif
                        </td>
                        <td class="p-3">{{ strtoupper($site->language) }}</td>
                        <td class="p-3">{{ __('category.'.$site->category) }}</td>
                        <td class="p-3 tabular-nums">{{ $site->monthly_traffic !== null ? num($site->monthly_traffic) : '–' }}</td>
                        <td class="p-3 tabular-nums">{{ $site->domain_rating ?? '–' }}</td>
                        <td class="p-3">{{ $site->link_attribute }}</td>
                        <td class="p-3">{{ trans_choice(':count jour|:count jours', $site->turnaround_days) }}</td>
                        <td class="p-3 font-semibold tabular-nums">{{ dzd($site->buyerPrice()) }}</td>
                        @if ($showDomain)
                            <td class="p-3"><a class="text-brand underline" href="{{ route('app.orders.create', $site) }}">{{ __('Commander') }}</a></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
