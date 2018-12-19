<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Form;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\Forms\Presenters\UploadedFilePresenter;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

class FormSubmissionsController extends CpController
{
    public function index($form)
    {
        $form = Form::get($form);

        $this->authorize('view', $form);

        $columns = collect($form->columns())->map(function ($val, $column) {
            return ['label' => $column, 'field' => $column, 'translation' => $val];
        })->values()->reverse()->push([
            'label' => 'datestring',
            'field' => 'datestamp'
        ])->reverse()->values();

        $submissions = $form->submissions()->map(function ($submission) use ($form) {
            $this->sanitizeSubmission($submission);

            return array_merge($submission->toArray(), [
                'datestring' => $submission->date()->format($form->dateFormat()),
                'datestamp' => $submission->date()->timestamp,
                'url' => cp_route('forms.submissions.show', [$form->name(), $submission->id()]),
                'deleteable' => me()->can('delete', $submission),
            ]);
        });

        // Set the default/fallback sort order
        $sort = 'datestamp';
        $sortOrder = 'asc';

        // Custom sorting will override anything predefined.
        if ($customSort = $this->request->sort) {
            $sort = $customSort;
        }
        if ($customOrder = $this->request->order) {
            $sortOrder = $customOrder;
        }

        // Perform the sort!
        if ($customSort !== 'datestamp' || $sortOrder !== 'desc') {
            $submissions = $submissions->sortBy($sort, null, $sortOrder === 'desc');
        }

        // Set up the paginator, since we don't want to display all the entries.
        $totalSubmissionCount = $submissions->count();
        $perPage = Config::get('statamic.cp.pagination_size');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $submissions = $submissions->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($submissions, $totalSubmissionCount, $perPage, $currentPage);

        return [
            'data' => $submissions->values(),
            'meta' => [
                'columns' => $columns,
                'sortColumn' => $sort,
            ]
            // 'pagination' => [
            //     'totalItems' => $totalSubmissionCount,
            //     'itemsPerPage' => $perPage,
            //     'totalPages'    => $paginator->lastPage(),
            //     'currentPage'   => $paginator->currentPage(),
            //     'prevPage'      => $paginator->previousPageUrl(),
            //     'nextPage'      => $paginator->nextPageUrl(),
            //     'segments'      => array_get($paginator->renderArray(), 'segments')
            // ]
        ];
    }

    private function sanitizeSubmission($submission)
    {
        collect($submission->data())->each(function ($value, $field) use ($submission) {
            $sanitized = ($submission->formset()->isUploadableField($field))
                ? UploadedFilePresenter::render($submission, $field)
                : $this->sanitizeField($value, $submission);

            $submission->set($field, $sanitized);
        });
    }

    private function sanitizeField($value, $submission)
    {
        $is_arr = is_array($value);

        $values = Helper::ensureArray($value);

        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif (! $submission->formset()->get('sanitize', true)) {
                $value = sanitize($value);
            }
        }

        return ($is_arr) ? $values : $values[0];
    }

    public function destroy($form, $id)
    {
        $submission = Form::get($form)->submission($id);

        $this->authorize('delete', $submission);

        $submission->delete();

        return response('', 204);
    }

    public function show($form, $submission)
    {
        $form = Form::get($form);

        if (! $submission = $form->submission($submission)) {
            return $this->pageNotFound();
        }

        $this->authorize('view', $submission);

        $this->sanitizeSubmission($submission);

        return view('statamic::forms.submission', compact('form', 'submission'));
    }
}
