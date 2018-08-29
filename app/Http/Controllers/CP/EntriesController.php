<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\CP\Publish\ProcessesFields;

class EntriesController extends CpController
{
    use ProcessesFields;

    public function index($collection)
    {
        // TODO: Bring over the rest of the logic.
        return Entry::whereCollection($collection)->toArray();
    }

    public function edit(Request $request, $collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        $this->authorize('view', $entry);

        $fieldset = $entry->fieldset();
        // event(new PublishFieldsetFound($fieldset, 'entry', $entry)); // TODO

        $data = array_merge($this->addBlankFields($fieldset, $entry->processedData()), [
            'slug' => $entry->slug()
        ]);

        return view('statamic::entries.edit', [
            'entry' => $entry,
            'data' => $data,
            'readOnly' => $request->user()->cant('edit', $entry)
        ]);
    }

    public function update(Request $request, $collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        $fieldsetFields = $entry->fieldset()->inlinedFields();
        $fields = array_keys($fieldsetFields);
        $extra = ['slug'];
        $validatable = array_merge($fields, $extra);

        $fieldsetValidationRules = collect($fieldsetFields)->map(function ($field) {
            return array_get($field, 'validate', '');
        });

        $rules = $fieldsetValidationRules->merge([
            'title' => 'required',
            'slug' => 'required',
        ]);

        $data = $request->validate($rules->all());

        foreach (array_only($data, array_keys($fieldsetFields)) as $key => $value) {
            $entry->set($key, $value);
        }
        $entry->set('title', $data['title']);
        $entry->slug($data['slug']);
        $entry->save();

        return ['success' => true];
    }

    public function create()
    {
        return view('statamic::entries.create');
    }

    public function store()
    {

    }

    public function destroy($slug)
    {

    }
}
