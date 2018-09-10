<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Str;
use Illuminate\Http\Request;
use Statamic\API\AssetContainer;

class AssetBrowserController extends CpController
{
    public function index()
    {
        $containers = AssetContainer::all();

        // TODO: Filter out unauthorized containers
        // TODO: Handle no authorized containers

        return redirect()
            ->cpRoute('assets.browse.show', $containers->first()->handle());
    }

    public function show($container, $path = '/')
    {
        // TODO: Handle invalid $container in url
        // TODO: Auth

        $container = AssetContainer::find($container);

        return view('statamic::assets.browse', [
            'container' => $this->toContainerArray($container),
            'folder' => $path
        ]);
    }

    public function folder(Request $request, $container, $path = '/')
    {
        // TODO: Handle invalid $container in url
        // TODO: Auth

        $container = AssetContainer::find($container);

        // Grab all the assets from the container.
        $assets = $container->assets($path);
        $assets = $this->supplementAssetsForDisplay($assets);

        // Get data about the subfolders in the requested asset folder.
        $folders = [];
        foreach ($container->assetFolders($path) as $f) {
            $folders[] = [
                'path' => $f->path(),
                'title' => $f->title()
            ];
        }

        return [
            'container' => $this->toContainerArray($container),
            'assets' => $assets->toArray(),
            'folders' => $folders,
            'folder' => $container->assetFolder($path)->toArray()
        ];
    }

    private function supplementAssetsForDisplay($assets)
    {
        foreach ($assets as &$asset) {
            // Add thumbnails to all image assets.
            if ($asset->isImage()) {
                $asset->set('thumbnail', $this->thumbnail($asset, 'small'));
                $asset->set('toenail', $this->thumbnail($asset, 'large'));
            }

            // Set some values for better listing formatting.
            $asset->set('size_formatted', Str::fileSizeForHumans($asset->size(), 0));
            $asset->set('last_modified_formatted', $asset->lastModified()->format(config('statamic.cp.date_format')));
        }

        return $assets;
    }

    private function thumbnail($asset, $preset = null)
    {
        return cp_route('assets.thumbnails.show', [
            'asset' => base64_encode($asset->id()),
            'size' => $preset
        ]);
    }

    private function toContainerArray($container)
    {
        return [
            'id' => $container->id(),
            'title' => $container->title(),
            'edit_url' => $container->editUrl()
        ];
    }
}
