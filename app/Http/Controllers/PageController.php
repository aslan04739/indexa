<?php

namespace App\Http\Controllers;

use App\Support\CatalogFilters;
use App\Support\Localized;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $page): View
    {
        $data = ['page' => $page, 'noindex' => false, 'canonical' => Localized::url($page)];

        if ($page === 'catalog') {
            $filters = CatalogFilters::fromRequest($request);
            $sites = $filters->apply()->paginate(30)->withQueryString();

            // Filter combinations are for people, not for the index: noindex them, keep links followed.
            $data['noindex'] = $filters->active();
            if (! $filters->active() && $sites->currentPage() > 1) {
                $data['canonical'] .= '?page='.$sites->currentPage();
            }

            $data += ['filters' => $filters, 'sites' => $sites];
        }

        return view("pages.$page", $data);
    }
}
