<?php

namespace Statamic\Tags;

use Statamic\API\URL;
use Statamic\API\Entry;
use Statamic\API\Helper;

class GetContent extends Collection
{
    /**
     * The {{ get_content:[foo] }} tag
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        $from = array_get_colon($this->context, $this->method);

        return $this->index($from);
    }

    /**
     * The {{ get_content }} tag
     *
     * @param string|null $locations  Optional requested location(s) to retrieve content from.
     * @return string
     */
    public function index($locations = null)
    {
        if (! $locale = $this->get('locale')) {
            $locale = site_locale();
        }

        if (! $locations) {
            $locations = $this->get(['from', 'id']);
        }

        $this->collection = collect_content(
            Helper::explodeOptions($locations)
        )->map(function ($from) use ($locale) {
            return ($content = $this->getContent($from, $locale)) ? $content->in($locale) : null;
        })->filter();

        $this->filter();

        return $this->output();
    }

    /**
     * Get content from somewhere
     *
     * @param string $from  Either an ID or URI
     * @param string $locale  Locale to get the content from
     * @return \Statamic\Contracts\Data\Content\Content
     */
    protected function getContent($from, $locale)
    {
        // If a secondary locale is specified, get the default URI
        // since that's how they are referenced internally.
        if ($locale !== default_locale()) {
            $from = URL::unlocalize($from, $locale);
        }

        if ($entry = Entry::find($from)) {
            return $entry;
        }

        return Entry::whereUri($from);
    }

    protected function getSortOrder()
    {
        return $this->get('sort');
    }
}
