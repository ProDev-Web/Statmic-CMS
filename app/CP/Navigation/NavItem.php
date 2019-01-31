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
    protected $currentClass;
    protected $icon;
    protected $children;
    protected $view;
    protected $authorization;

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

        if (! $this->currentClass) {
            $this->currentClass = str_replace(url('cp').'/', '', $this->url) . '*';
        }

        return $this;
    }

    /**
     * Get or set current class.
     *
     * @param string|null $pattern
     * @return mixed
     */
    public function currentClass($pattern = null)
    {
        if (is_null($pattern)) {
            return $this->currentClass;
        }

        $this->currentClass = $pattern;

        return $this;
    }

    /**
     * Get or set url by cp route name.
     *
     * @param array|string $name
     * @param mixed $params
     * @return mixed
     */
    public function route($name, $params = [])
    {
        return $this->url(cp_route($name, $params));
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

        if (is_callable($items)) {
            $this->children = $items;
            return $this;
        }

        $this->children = collect($items)
            ->map(function ($value, $key) {
                return $value instanceof NavItem
                    ? $value
                    : Nav::item($key)->url($value);
            })
            ->values();

        if ($this->children->isEmpty()) {
            $this->children = null;
        }

        return $this;
    }

    /**
     * Get or set custom view.
     *
     * @param string|null $view
     * @return mixed
     */
    public function view($view = null)
    {
        if (is_null($view)) {
            return $this->view;
        }

        $this->view = $view;

        return $this;
    }

    /**
     * Get or set authorization.
     *
     * @param string|null $ability
     * @param array $arguments
     * @return mixed
     */
    public function authorization($ability = null, $arguments = [])
    {
        if (is_null($ability)) {
            return $this->authorization;
        }

        $this->authorization = (object) [
            'ability' => $ability,
            'arguments' => $arguments,
        ];

        return $this;
    }

    /**
     * Get or set authorization (an alias for consistency with Laravel's can() method).
     *
     * @param string|null $ability
     * @param array $arguments
     * @return mixed
     */
    public function can($ability = null, $arguments = [])
    {
        return $this->authorization($ability, $arguments);
    }
}
