<?php

namespace Statamic\Data\Structures;

use Statamic\API\Entry as EntryAPI;
use Statamic\Data\Content\UrlBuilder;
use Statamic\Contracts\Data\Entries\Entry;

class Page implements Entry
{
    protected $entry;
    protected $route;
    protected $parentUri;
    protected $children;

    public function setEntry($entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function entry(): ?Entry
    {
        if (is_string($this->entry)) {
            $this->entry = $this->actualEntry();
        }

        return $this->entry;
    }

    protected function actualEntry(): ?Entry
    {
        return EntryAPI::find($this->entry);
    }

    public function parentUri(): ?string
    {
        return $this->parentUri;
    }

    public function setParentUri(string $uri): self
    {
        $this->parentUri = $uri;

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

    public function uri()
    {
        $this->entry()->set('parent_uri', $this->parentUri);

        return app(UrlBuilder::class)
            ->content($this->entry())
            ->build($this->route);
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function pages()
    {
        return (new Pages)
            ->setTree($this->children ?? [])
            ->setParentUri($this->uri())
            ->setRoute($this->route);
    }

    public function flattenedPages()
    {
        return $this->pages()->flattenedPages();
    }

    // TODO: tests for these

    public function toArray()
    {
        return $this->entry()->toArray();
    }

    public function editUrl()
    {
        return $this->entry()->editUrl();
    }

    public function fieldset($fieldset = null)
    {
        return $this->entry()->fieldset($fieldset);
    }

    public function in($locale)
    {
        return new \Statamic\Data\LocalizedData($locale, $this);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->entry(), $method], $args);
    }
}
