<?php

namespace Statamic\Data\Structures;

use Statamic\API\URL;
use Statamic\API\Site;
use Statamic\API\Entry as EntryAPI;
use Statamic\Data\Content\UrlBuilder;
use Statamic\Contracts\Data\Entries\Entry;
use Illuminate\Contracts\Support\Responsable;

class Page implements Entry, Responsable
{
    protected $tree;
    protected $reference;
    protected $entry;
    protected $route;
    protected $parent;
    protected $children;
    protected $isRoot = false;
    protected $url;
    protected $title;
    protected $depth;
    protected static $uris = [];

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function url()
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->reference) {
            return URL::makeRelative($this->absoluteUrl());
        }
    }

    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    public function depth()
    {
        return $this->depth;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function title()
    {
        if ($this->title) {
            return $this->title;
        }

        return optional($this->entry())->value('title');
    }

    public function setEntry($reference): self
    {
        if ($reference === null) {
            return $this;
        }

        if (! is_string($reference)) {
            $this->entry = $reference;
            $reference = $reference->id();
        }

        $this->reference = $reference;

        return $this;
    }

    public function entry(): ?Entry
    {
        if (!$this->reference && !$this->entry) {
            return null;
        }

        return $this->entry = $this->entry ?? EntryAPI::find($this->reference);
    }

    public function reference()
    {
        return $this->reference;
    }

    public function parent(): ?Page
    {
        return $this->parent;
    }

    public function setParent(?Page $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function route(): ?string
    {
        return $this->route;
    }

    public function slug()
    {
        return optional($this->entry())->slug();
    }

    public function uri()
    {
        if (! $this->reference) {
            return;
        }

        if ($cached = static::$uris[$this->reference] ?? null) {
            return $cached;
        }

        if (! $this->structure()->collection()) {
            return static::$uris[$this->reference] = $this->entry()->uri();
        }

        return static::$uris[$this->reference] = app(UrlBuilder::class)
            ->content($this)
            ->merge([
                'parent_uri' => $this->parent && !$this->parent->isRoot() ? $this->parent->uri() : '',
                'slug' => $this->isRoot() ? '' : $this->slug(),
                'depth' => $this->depth,
                'is_root' => $this->isRoot(),
            ])
            ->build($this->route);
    }

    public function absoluteUrl()
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->reference) {
            return vsprintf('%s/%s', [
                rtrim($this->site()->absoluteUrl(), '/'),
                ltrim($this->uri(), '/')
            ]);
        }
    }

    public function isRoot()
    {
        return $this->isRoot;
    }

    public function setTree($tree)
    {
        $this->tree = $tree;

        return $this;
    }

    public function setRoot(bool $isRoot)
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function pages()
    {
        $pages = (new Pages)
            ->setTree($this->tree)
            ->setPages($this->children ?? [])
            ->setParent($this)
            ->setDepth($this->depth + 1)
            ->prependParent(false);

        if ($this->route) {
            $pages->setRoute($this->route);
        }

        return $pages;
    }

    public function flattenedPages()
    {
        return $this->pages()->flattenedPages();
    }

    // TODO: tests for these

    public function toArray()
    {
        $array = $this->reference ? $this->entry()->toArray() : [];

        return array_merge($array, [
            'title' => $this->title(),
            'url' => $this->url(),
            'uri' => $this->uri(),
            'permalink' => $this->absoluteUrl(),
        ]);
    }

    public function editUrl()
    {
        return optional($this->entry())->editUrl();
    }

    public function id()
    {
        return optional($this->entry())->id();
    }

    public function in($site)
    {
        if ($this->reference) {
            if (! $entry = $this->entry()->in($site)) {
                return null;
            }

            return $this->setEntry($entry->id());
        }

        return $this;
    }

    public function site()
    {
        if ($this->reference) {
            return $this->entry()->site();
        }

        return Site::current(); // TODO: Get it from the tree instead.
    }

    public function toResponse($request)
    {
        if ($this->reference) {
            return $this->entry()->toResponse($request);
        }

        throw new \LogicException('A page without a reference to an entry cannot be rendered.');
    }

    public function structure()
    {
        return $this->tree->structure();
    }

    public function routeData()
    {
        return $this->entry()->routeData();
    }
}
