{{-- Plain GET form: filtering works without JavaScript. --}}
<form method="GET" action="{{ $action }}" class="grid gap-3 rounded border border-line bg-white p-4 sm:grid-cols-3 lg:grid-cols-6 lg:items-end">
    <x-select name="language" :label="__('Langue')" :value="$filters->language" :placeholder="__('Toutes')" :options="collect(config('indexa.locales'))->map(fn ($l) => $l['name'])->all()" />
    <x-select name="category" :label="__('Thématique')" :value="$filters->category" :placeholder="__('Toutes')" :options="collect(config('marketplace.categories'))->mapWithKeys(fn ($c) => [$c => __('category.'.$c)])->all()" />
    <x-select name="attribute" :label="__('Type de lien')" :value="$filters->attribute" :placeholder="__('Tous')" :options="collect(App\Models\Site::LINK_ATTRIBUTES)->mapWithKeys(fn ($a) => [$a => $a])->all()" />
    <x-field name="max_price" type="number" :label="__('Prix max (DZD)')" :value="$filters->maxPrice" min="0" step="500" />
    <x-select name="sort" :label="__('Trier par')" :value="$filters->sort" :options="['traffic' => __('Trafic'), 'price' => __('Prix'), 'rating' => __('Autorité')]" />
    <x-button>{{ __('Filtrer') }}</x-button>
</form>
