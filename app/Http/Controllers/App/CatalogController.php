<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Support\CatalogFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = CatalogFilters::fromRequest($request);

        return view('app.catalog', [
            'filters' => $filters,
            'sites' => $filters->apply()->paginate(30)->withQueryString(),
        ]);
    }
}
