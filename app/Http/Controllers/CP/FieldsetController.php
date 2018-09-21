<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API;
use Illuminate\Http\Request;
use Statamic\Fields\Fieldset;

class FieldsetController extends CpController
{
    public function index()
    {
        $this->authorize('index', Fieldset::class, 'You are not authorized to access fieldsets.');

        $fieldsets = API\Fieldset::all()->map(function ($fieldset) {
            return [
                'id' => $fieldset->handle(),
                'handle' => $fieldset->handle(),
                'title' => $fieldset->title(),
                'fields' => $fieldset->fields()->count(),
                'edit_url' => $fieldset->editUrl(),
            ];
        })->values();

        return view('statamic::fieldsets.index', compact('fieldsets'));
    }

    public function edit($fieldset)
    {
        return view('statamic::fieldsets.edit', [
            'fieldset' => API\Fieldset::find($fieldset)
        ]);
    }

    public function update(Request $request, $fieldset)
    {
        $fieldset = API\Fieldset::find($fieldset);

        $this->authorize('edit', $fieldset);

        $fields = collect($request->fields)->mapWithKeys(function ($field) {
            return [array_pull($field, 'handle') => array_except($field, '_id')];
        })->all();

        $fieldset->setContents([
            'title' => $request->title,
            'fields' => $fields
        ])->save();

        return response('', 204);
    }
}
