<?php

namespace Statamic\Widgets;

use Statamic\Extend\Widget;
use Statamic\API\Collection as CollectionAPI;

class Collection extends Widget
{
    public function html()
    {
        $collection = $this->config('collection');

        if (! CollectionAPI::handleExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        $collection = CollectionAPI::whereHandle($collection);

        if (! auth()->user()->can('view', $collection)) {
            return;
        }

        $entries = $collection
            ->queryEntries()
            // ->removeUnpublished() // TODO: Reimplement
            ->limit($this->config('limit', 5))
            ->get();

        $title = $this->config('title', $collection->title());
        $format = $this->config('date_format', config('statamic.cp.date_format'));
        $button = __('New :thing', ['thing' => $collection->entryBlueprint()->title()]);

        return view('statamic::widgets.collection', compact('collection', 'entries', 'title', 'format', 'button'));
    }
}
