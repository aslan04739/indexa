<?php

namespace App\Support;

use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** Shared by the public catalog and the buyer catalog: plain GET parameters, no JavaScript. */
class CatalogFilters
{
    public function __construct(
        public ?string $language = null,
        public ?string $category = null,
        public ?string $attribute = null,
        public ?int $maxPrice = null,
        public string $sort = 'traffic',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $in = fn (string $key, array $allowed) => in_array($request->query($key), $allowed, true) ? $request->query($key) : null;

        return new self(
            language: $in('language', array_keys(config('indexa.locales'))),
            category: $in('category', config('marketplace.categories')),
            attribute: $in('attribute', Site::LINK_ATTRIBUTES),
            maxPrice: $request->integer('max_price') ?: null,
            sort: $in('sort', ['traffic', 'price', 'rating']) ?? 'traffic',
        );
    }

    public function active(): bool
    {
        return $this->language || $this->category || $this->attribute || $this->maxPrice || $this->sort !== 'traffic';
    }

    public function apply(): Builder
    {
        $query = Site::approved()
            ->when($this->language, fn ($q) => $q->where('language', $this->language))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->attribute, fn ($q) => $q->where('link_attribute', $this->attribute))
            ->when($this->maxPrice, fn ($q) => $q->where('price_dzd', '<=', $this->maxPrice));

        $sorted = match ($this->sort) {
            'price' => $query->orderBy('price_dzd'),
            'rating' => $query->orderByDesc('domain_rating'),
            default => $query->orderByDesc('monthly_traffic'),
        };

        return $sorted->orderBy('id');
    }
}
