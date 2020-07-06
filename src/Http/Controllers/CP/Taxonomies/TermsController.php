<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Events\PublishBlueprintFound;
use Statamic\Facades\Asset;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Taxonomies\Term as TermResource;
use Statamic\Http\Resources\CP\Taxonomies\Terms;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class TermsController extends CpController
{
    use QueriesFilters;

    public function index(FilteredRequest $request, $taxonomy)
    {
        $this->authorize('view', $taxonomy);

        $query = $this->indexQuery($taxonomy);

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'blueprints' => $taxonomy->termBlueprints()->map->handle(),
        ]);

        $sortField = request('sort');
        $sortDirection = request('order', 'asc');

        if (! $sortField && ! request('search')) {
            $sortField = $taxonomy->sortField();
            $sortDirection = $taxonomy->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $terms = $query->paginate(request('perPage'));

        $terms->setCollection(
            $terms->getCollection()->map->in(Site::selected()->handle())
        );

        return (new Terms($terms))
            ->blueprint($taxonomy->termBlueprint())
            ->columnPreferenceKey("taxonomies.{$taxonomy->handle()}.columns")
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }

    protected function indexQuery($taxonomy)
    {
        $query = $taxonomy->queryTerms();

        if ($search = request('search')) {
            if ($taxonomy->hasSearchIndex()) {
                return $taxonomy->searchIndex()->ensureExists()->search($search);
            }

            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query;
    }

    public function edit(Request $request, $taxonomy, $term)
    {
        $this->authorize('view', $term);

        $term = $term->fromWorkingCopy();

        $blueprint = $term->blueprint();

        event(new PublishBlueprintFound($blueprint, 'term', $term));

        [$values, $meta] = $this->extractFromFields($term, $blueprint);

        if ($hasOrigin = $term->hasOrigin()) {
            [$originValues, $originMeta] = $this->extractFromFields($term->origin(), $blueprint);
        }

        $viewData = [
            'title' => $term->value('title'),
            'reference' => $term->reference(),
            'editing' => true,
            'actions' => [
                'save' => $term->updateUrl(),
                'publish' => $term->publishUrl(),
                'unpublish' => $term->unpublishUrl(),
                'revisions' => $term->revisionsUrl(),
                'restore' => $term->restoreRevisionUrl(),
                'createRevision' => $term->createRevisionUrl(),
            ],
            'values' => array_merge($values, ['id' => $term->id()]),
            'meta' => $meta,
            'taxonomy' => $this->taxonomyToArray($taxonomy),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::fromUser($request->user())->cant('edit', $term),
            'published' => $term->published(),
            'locale' => $term->locale(),
            'localizedFields' => $term->data()->keys()->all(),
            'isRoot' => $term->isRoot(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'permalink' => $term->absoluteUrl(),
            'localizations' => $taxonomy->sites()->map(function ($handle) use ($term) {
                $localized = $term->in($handle);

                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $term->locale(),
                    'exists' => true,
                    'root' => $localized->isRoot(),
                    'origin' => $localized->isRoot(),
                    'published' => $localized->published(),
                    'url' => $localized->editUrl(),
                    'livePreviewUrl' => $localized->livePreviewUrl(),
                ];
            })->all(),
            'hasWorkingCopy' => $term->hasWorkingCopy(),
            'preloadedAssets' => $this->extractAssetsFromValues($values),
            'revisionsEnabled' => $term->revisionsEnabled(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Term created'));
        }

        return view('statamic::terms.edit', array_merge($viewData, [
            'term' => $term,
        ]));
    }

    public function update(Request $request, $taxonomy, $term, $site)
    {
        $term = $term->in($site->handle());

        $this->authorize('update', $term);

        $term = $term->fromWorkingCopy();

        $fields = $term->blueprint()->fields()->addValues($request->except('id'));

        $fields->validate([
            'title' => 'required',
            'slug' => 'required|alpha_dash',
        ]);

        $values = $fields->process()->values()->except(['slug', 'date']);

        if ($term->hasOrigin()) {
            $term->data($values->only($request->input('_localized')));
        } else {
            $term->merge($values);
        }

        $term->slug($request->slug);

        if ($term->revisionsEnabled() && $term->published()) {
            $term
                ->makeWorkingCopy()
                ->user(User::fromUser($request->user()))
                ->save();
        } else {
            if (! $term->revisionsEnabled()) {
                $term->published($request->published);
            }

            $term
                ->set('updated_by', User::fromUser($request->user())->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        return new TermResource($term);
    }

    public function create(Request $request, $taxonomy, $site)
    {
        $this->authorize('create', [TermContract::class, $taxonomy]);

        $blueprint = $request->blueprint
            ? $taxonomy->ensureTermBlueprintFields(Blueprint::find($request->blueprint))
            : $taxonomy->termBlueprint();

        if (! $blueprint) {
            throw new \Exception('A valid blueprint is required.');
        }

        $fields = $blueprint
            ->fields()
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => null,
            'slug' => null,
            'published' => $taxonomy->defaultPublishState(),
        ]);

        $viewData = [
            'title' => __('Create Term'),
            'actions' => [
                'save' => cp_route('taxonomies.terms.store', [$taxonomy->handle(), $site->handle()]),
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'taxonomy' => $this->taxonomyToArray($taxonomy),
            'blueprint' => $blueprint->toPublishArray(),
            'published' => $taxonomy->defaultPublishState(),
            'localizations' => $taxonomy->sites()->map(function ($handle) use ($taxonomy, $site) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), $handle]),
                    'livePreviewUrl' => cp_route('taxonomies.terms.preview.create', [$taxonomy->handle(), $handle]),
                ];
            })->all(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::terms.create', $viewData);
    }

    public function store(Request $request, $taxonomy, $site)
    {
        $this->authorize('store', [TermContract::class, $taxonomy]);

        $blueprint = $taxonomy->ensureTermBlueprintFields(
            Blueprint::find($request->blueprint)
        );

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate([
            'title' => 'required',
            'slug' => 'required',
        ]);

        $values = $fields->process()->values()->except(['slug', 'blueprint']);

        $term = Term::make()
            ->taxonomy($taxonomy)
            ->blueprint($request->blueprint)
            ->in($site->handle());

        $term
            ->slug($request->slug)
            ->published($request->get('published')) // TODO
            ->data($values);

        if ($term->revisionsEnabled()) {
            $term->store([
                'message' => $request->message,
                'user' => User::fromUser($request->user()),
            ]);
        } else {
            $term
                ->set('updated_by', User::fromUser($request->user())->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        return ['data' => ['redirect' => $term->editUrl()]];
    }

    // TODO: Change to $taxonomy->toArray()
    protected function taxonomyToArray($taxonomy)
    {
        return [
            'title' => $taxonomy->title(),
            'url' => cp_route('taxonomies.show', $taxonomy->handle()),
        ];
    }

    protected function extractFromFields($term, $blueprint)
    {
        $fields = $blueprint
            ->fields()
            ->addValues($term->values()->all())
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => $term->value('title'),
            'slug' => $term->slug(),
        ]);

        return [$values->all(), $fields->meta()];
    }

    protected function extractAssetsFromValues($values)
    {
        return collect($values)
            ->filter(function ($value) {
                return is_string($value);
            })
            ->map(function ($value) {
                preg_match_all('/"asset::([^"]+)"/', $value, $matches);

                return str_replace('\/', '/', $matches[1]) ?? null;
            })
            ->flatten(2)
            ->unique()
            ->map(function ($id) {
                return Asset::find($id);
            })
            ->filter()
            ->values();
    }
}
