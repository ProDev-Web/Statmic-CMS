<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\API\Site;
use Statamic\API\Term;
use Statamic\API\Asset;
use Statamic\API\Entry;
use Statamic\CP\Column;
use Statamic\API\Action;
use Statamic\API\Blueprint;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\API\Preference;
use Statamic\Fields\Validation;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Events\Data\PublishBlueprintFound;
use Statamic\Http\Requests\FilteredSiteRequest;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class TermsController extends CpController
{
    public function index(FilteredSiteRequest $request, $taxonomy)
    {
        $query = $this->indexQuery($taxonomy);

        $this->filter($query, $request->filters);

        $sortField = request('sort');
        $sortDirection = request('order');

        if (!$sortField && !request('search')) {
            $sortField = $taxonomy->sortField();
            $sortDirection = $taxonomy->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $paginator = $query->paginate(request('perPage'));

        $paginator->supplement(function ($term) {
            return [
                'viewable' => me()->can('view', $term),
                'editable' => me()->can('edit', $term),
                'actions' => Action::for('terms', [], $term),
            ];
        })->preProcessForIndex();

        $columns = $taxonomy->termBlueprint()
            ->columns()
            ->setPreferred("taxonomies.{$taxonomy->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        return Resource::collection($paginator)->additional(['meta' => [
            'filters' => $request->filters,
            'sortColumn' => $sortField,
            'columns' => $columns,
        ]]);
    }

    protected function filter($query, $filters)
    {
        foreach ($filters as $handle => $values) {
            $class = app('statamic.scopes')->get($handle);
            $filter = app($class);
            $filter->apply($query, $values);
        }
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
                'revisions' => $term->revisionsUrl(),
                'restore' => $term->restoreRevisionUrl(),
                'createRevision' => $term->createRevisionUrl(),
            ],
            'values' => array_merge($values, ['id' => $term->id()]),
            'meta' => $meta,
            'taxonomy' => $this->taxonomyToArray($taxonomy),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => $request->user()->cant('edit', $term),
            'published' => $term->published(),
            'locale' => $term->locale(),
            'localizedFields' => array_keys($term->data()),
            'isRoot' => $term->isRoot(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? [],
            'originMeta' => $originMeta ?? [],
            'permalink' => $term->absoluteUrl(),
            'localizations' => $taxonomy->sites()->map(function ($handle) use ($term) {
                $localized = $term->in($handle);
                $exists = $localized !== null;
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $term->locale(),
                    'exists' => $exists,
                    'root' => $exists ? $localized->isRoot() : false,
                    'origin' => $exists ? $localized->id() === optional($term->origin())->id() : null,
                    'published' => $exists ? $localized->published() : false,
                    'url' => $exists ? $localized->editUrl() : null,
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
            'term' => $term
        ]));
    }

    public function update(Request $request, $taxonomy, $term, $site)
    {
        $term = $term->in($site);

        $this->authorize('update', $term);

        $term = $term->fromWorkingCopy();

        $fields = $term->blueprint()->fields()->addValues($request->except('id'))->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required|alpha_dash',
        ]);

        $request->validate($validation->rules());

        $values = array_except($fields->values(), ['slug', 'date']);

        if ($term->hasOrigin()) {
            $values = array_only($values, $request->input('_localized'));
        }

        $term
            ->merge($values)
            ->slug($request->slug);

        if ($term->revisionsEnabled() && $term->published()) {
            $term
                ->makeWorkingCopy()
                ->user($request->user())
                ->save();
        } else {
            if (! $term->revisionsEnabled()) {
                $term->published($request->published);
            }

            $term
                ->set('updated_by', $request->user()->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        return $term->toArray();
    }

    public function create(Request $request, $taxonomy, $site)
    {
        $this->authorize('create', [TermContract::class, $taxonomy]);

        $blueprint = $request->blueprint
            ? Blueprint::find($request->blueprint)
            : $taxonomy->termBlueprint();

        if (! $blueprint) {
            throw new \Exception('A valid blueprint is required.');
        }

        $fields = $blueprint
            ->fields()
            ->preProcess();

        $values = array_merge($fields->values(), [
            'title' => null,
            'slug' => null
        ]);

        $viewData = [
            'title' => __('Create Term'),
            'actions' => [
                'save' => cp_route('taxonomies.terms.store', [$taxonomy->handle(), $site->handle()])
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'taxonomy' => $this->taxonomyToArray($taxonomy),
            'blueprint' => $blueprint->toPublishArray(),
            'published' => $taxonomy->defaultStatus() === 'published',
            'localizations' => $taxonomy->sites()->map(function ($handle) use ($taxonomy, $site) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), $handle]),
                ];
            })->all()
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::terms.create', $viewData);
    }

    public function store(Request $request, $taxonomy, $site)
    {
        $this->authorize('store', [TermContract::class, $taxonomy]);

        $blueprint = $taxonomy->termBlueprint();

        $fields = $blueprint->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required',
        ]);

        $request->validate($validation->rules());

        $values = array_except($fields->values(), ['slug', 'blueprint']);

        $term = Term::make()
            ->taxonomy($taxonomy)
            ->locale($site->handle())
            ->published($request->get('published'))
            ->slug($request->slug)
            ->data($values);

        if ($term->revisionsEnabled()) {
            $term->store([
                'message' => $request->message,
                'user' => $request->user(),
            ]);
        } else {
            $term
                ->set('updated_by', $request->user()->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        return array_merge($term->toArray(), [
            'redirect' => $term->editUrl(),
        ]);
    }

    // TODO: Change to $taxonomy->toArray()
    protected function taxonomyToArray($taxonomy)
    {
        return [
            'title' => $taxonomy->title(),
            'url' => cp_route('taxonomies.show', $taxonomy->handle())
        ];
    }

    protected function extractFromFields($term, $blueprint)
    {
        $fields = $blueprint
            ->fields()
            ->addValues($term->values())
            ->preProcess();

        $values = array_merge($fields->values(), [
            'title' => $term->value('title'),
            'slug' => $term->slug()
        ]);

        return [$values, $fields->meta()];
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
