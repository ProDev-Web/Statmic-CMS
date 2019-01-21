<?php

namespace Statamic\API\Endpoint;

use Statamic\API\Page;
use Statamic\API\Term;
use Statamic\API\Entry;
use Statamic\API\Helper;
use Statamic\API\GlobalSet;
use Statamic\API\Collection;
use Statamic\API\PageFolder;
use Statamic\Data\Services\PageStructureService;
use Statamic\Contracts\Data\Repositories\ContentRepository;

class Content
{
    /**
     * Get all content
     *
     * @return \Statamic\Data\Content\ContentCollection
     */
    public function all()
    {
        return $this->repo()->all();
    }

    /**
     * Get content by ID
     *
     * @param string $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->repo()->find($id);
    }

    /**
     * Get the raw Content object for a URI
     *
     * @param string      $uri       The URI to look for.
     * @return \Statamic\Contracts\Data\Content\Content
     */
    public function whereUri($uri, $site = null)
    {
        // TODO: The old version of this method accepted an array of URIs.
        // Either support that in here, or make a separate method.
        return $this->repo()->findByUri($uri, $site);
    }

    /**
     * Check if content exists by ID
     *
     * @param string $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->repo()->exists($id);
    }

    /**
     * Check if content exists by URI
     *
     * @param string $uri
     * @return bool
     */
    public function uriExists($uri)
    {
        return $this->repo()->uriExists($uri);
    }

    /**
     * Get the content tree
     *
     * @param string       $uri
     * @param int          $depth
     * @param bool         $entries
     * @param bool         $drafts
     * @param bool         $exclude
     * @param string|null  $locale
     * @return array
     */
    public function tree(
        $uri = null,
        $depth = null,
        $entries = null,
        $drafts = null,
        $exclude = null,
        $locale = null
    ) {
        return app(PageStructureService::class)->tree($uri, $depth, $entries, $drafts, $exclude, $locale);
    }

    protected function repo()
    {
        return app(ContentRepository::class);
    }
}
