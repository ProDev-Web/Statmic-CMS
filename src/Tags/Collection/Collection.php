<?php

namespace Statamic\Tags\Collection;

use Statamic\Facades\URL;
use Statamic\Facades\Entry;
use Statamic\Tags\Tags;
use Statamic\Tags\OutputsItems;
use Statamic\Entries\EntryCollection;

class Collection extends Tags
{
    use OutputsItems;

    protected $defaultAsKey = 'entries';

    /**
     * {{ collection:* }} ... {{ /collection:* }}
     */
    public function __call($method, $args)
    {
        $this->parameters['from'] = $this->method;

        return $this->index();
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}
     */
    public function index()
    {
        return $this->output(
            $this->entries()->get()
        );
    }

    /**
     * {{ collection:count from="" }} ... {{ /collection:count }}
     */
    public function count()
    {
        return $this->entries()->count();
    }

    /**
     * {{ collection:next }} ... {{ /collection:next }}
     */
    public function next()
    {
        $this->parameters['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->next($this->currentEntry())
        );
    }

    /**
     * {{ collection:previous }} ... {{ /collection:previous }}
     */
    public function previous()
    {
        $this->parameters['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->previous($this->currentEntry())
        );
    }

    protected function entries()
    {
        return new Entries($this->parameters);
    }

    protected function currentEntry()
    {
        return Entry::find($this->get('current', $this->context->get('id')));
    }
}
