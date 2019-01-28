<?php

namespace Statamic\CP\Navigation;

use Exception;
use Statamic\API\Nav;
use Statamic\API\Str;

class NavItem
{
    protected $name;
    protected $section;
    protected $route;
    protected $url;
    protected $icon;
    protected $children;
    // private $badge; // TODO

    /**
     * Get or set name.
     *
     * @param string|null $name
     * @return mixed
     */
    public function name($name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Get or set section name.
     *
     * @param string|null $section
     * @return mixed
     */
    public function section($section = null)
    {
        if (is_null($section)) {
            return $this->section;
        }

        $this->section = $section;

        return $this;
    }

    /**
     * Get or set URL.
     *
     * @param string|null $url
     * @return mixed
     */
    public function url($url = null)
    {
        if (is_null($url)) {
            return $this->url;
        }

        $this->url = $url;

        return $this;
    }

    /**
     * Get or set url by route name.
     *
     * @param array|string $name
     * @param mixed $parameters
     * @param bool $absolute
     * @return mixed
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        return $this->url(route($name, $parameters, $absolute));
    }

    /**
     * Get or set icon.
     *
     * @param string|null $icon
     * @return mixed
     */
    public function icon($icon = null)
    {
        if (is_null($icon)) {
            return $this->icon;
        }

        $this->icon = $icon;

        return $this;
    }

    /**
     * Get or set child nav items.
     *
     * @param array|null $items
     * @return mixed
     */
    public function children($items = null)
    {
        if (is_null($items)) {
            return $this->children;
        }

        $this->children = collect($items)
            ->map(function ($value, $key) {
                return $value instanceof NavItem
                    ? $value
                    : Nav::item($key)->url($value);
            });

        return $this;
    }

    // public function badge($badge = null)
    // {
    //     if (is_null($badge)) {
    //         return $this->badge;
    //     }

    //     $this->badge = $badge;

    //     return $this;
    // }

    // public function add($key, $item = null)
    // {
    //     return $this->children->add($key, $item);
    // }

    // public function has($key)
    // {
    //     return $this->children->has($key);
    // }

    // public function get($key)
    // {
    //     return $this->children->get($key);
    // }

    // public function remove($key)
    // {
    //     return $this->children->remove($key);
    // }
}
