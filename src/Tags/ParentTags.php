<?php

namespace Statamic\Tags;

use Statamic\Facades\URL;
use Statamic\Facades\Parse;
use Statamic\Facades\Entry;
use Statamic\Support\Arr;
use Statamic\Tags\Tags;
use Stringy\StaticStringy as Stringy;

class ParentTags extends Tags
{
    protected static $handle = 'parent';

    /**
     * The {{ parent:[field] }} tag
     *
     * Gets a specified field value from the parent.
     *
     * @param  $method
     * @param  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $var_name = Stringy::removeLeft($this->tag, 'parent:');

        $data = Arr::get($this->getParent(), $var_name);

        if ($this->isPair) {
            $this->content = '{{'.$var_name.'}}' . $this->content . '{{/'.$var_name.'}}';

            return $this->parse([$var_name => $data]);
        }

        return $data;
    }

    /**
     * The {{ parent }} tag
     *
     * On its own, it simply returns the URL of the parent for a single tag,
     * or makes all the parent values available within it for a tag pair.
     *
     * @return string
     */
    public function index()
    {
        if ($this->isPair) {
            return $this->parse($this->getParent());
        }

        return $this->getParentUrl();
    }

    /**
     * Get the parent url
     *
     * @return string
     */
    private function getParentUrl()
    {
        $parent = $this->getParent();

        return array_get($parent, 'url');
    }

    /**
     * Get the parent data
     *
     * @return string
     */
    private function getParent()
    {
        $crumbs = [];

        $segments = explode('/', URL::getCurrent());
        $segment_count = count($segments);
        $segments[0] = '/';

        // Remove the current URL.
        // That's the whole point here.
        array_pop($segments);

        // Create crumbs from segments
        $segment_urls = [];
        for ($i = 1; $i <= $segment_count; $i++) {
            $segment_urls[] = URL::tidy(join('/', $segments));
            array_pop($segments);
        }

        $segments = collect($segment_urls);

        // Find the parent by stripping away URL segments
        foreach ($segment_urls as $segment_url) {
            if ($content = Entry::findByUri($segment_url)) {
                return $content->toArray();
            }
        }

        return null;
    }
}