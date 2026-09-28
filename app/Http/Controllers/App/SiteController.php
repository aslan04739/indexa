<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SiteVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        return view('app.sites.index', ['sites' => $request->user()->sites()->latest()->get()]);
    }

    public function create(): View
    {
        return view('app.sites.form', ['site' => new Site(['link_attribute' => 'sponsored', 'turnaround_days' => 5])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['domain'] = Site::domainFromUrl($data['url']);

        $request->validate(['url' => [function ($attr, $value, $fail) use ($data) {
            if (Site::where('domain', $data['domain'])->exists()) {
                $fail(__('Ce site est déjà inscrit sur Indexa.'));
            }
        }]]);

        $site = $request->user()->sites()->create($data);

        return redirect()->route('app.sites.show', $site);
    }

    public function show(Request $request, Site $site): View
    {
        $this->authorizeOwner($request, $site);

        return view('app.sites.show', ['site' => $site]);
    }

    public function edit(Request $request, Site $site): View
    {
        $this->authorizeOwner($request, $site);

        return view('app.sites.form', ['site' => $site]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorizeOwner($request, $site);
        $data = $this->validated($request, editing: true);
        $site->update($data);

        return redirect()->route('app.sites.show', $site)->with('status', __('Site mis à jour.'));
    }

    public function verify(Request $request, Site $site, SiteVerifier $verifier): RedirectResponse
    {
        $this->authorizeOwner($request, $site);

        return $verifier->verify($site)
            ? back()->with('status', __('Propriété vérifiée. Votre site est en attente de validation par l\'équipe Indexa.'))
            : back()->withErrors(['verify' => __('La balise de vérification est introuvable sur la page d\'accueil. Ajoutez-la dans la balise <head> puis réessayez.')]);
    }

    private function validated(Request $request, bool $editing = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'language' => ['required', Rule::in(array_keys(config('indexa.locales')))],
            'category' => ['required', Rule::in(config('marketplace.categories'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_dzd' => ['required', 'integer', 'min:1000', 'max:10000000'],
            'link_attribute' => ['required', Rule::in(Site::LINK_ATTRIBUTES)],
            'turnaround_days' => ['required', 'integer', 'min:1', 'max:30'],
            'monthly_traffic' => ['nullable', 'integer', 'min:0'],
        ];

        // The URL (and so the verified domain) cannot change after creation.
        if (! $editing) {
            $rules['url'] = ['required', 'url:http,https', 'max:255'];
        }

        return $request->validate($rules);
    }

    private function authorizeOwner(Request $request, Site $site): void
    {
        abort_unless($site->user_id === $request->user()->id, 404);
    }
}
