<?php

namespace Statamic\Extend;

abstract class Filter
{
    /**
     * Provides access to methods for retrieving parameters
     */
    use HasParameters;

    /**
     * @var \Illuminate\Support\Collection
     * @deprecated since 2.6  Use the collection passed into the filter method instead.
     */
    protected $collection;

    /**
     * @var array
     */
    protected $context;

    public function setProperties($properties)
    {
        $this->collection = $properties['collection'];
        $this->context = $properties['context'];
        $this->parameters = $properties['parameters'];
    }
}
