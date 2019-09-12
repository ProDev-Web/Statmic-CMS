<?php

namespace Statamic\Actions;

use Statamic\Facades;
use Statamic\Facades\AssetContainer;
use Statamic\Contracts\Assets\Asset;

class MoveAsset extends Action
{
    protected static $title = 'Move';

    public function visibleTo($key, $context)
    {
        return $key === 'asset-browser';
    }

    public function authorize($asset)
    {
        return user()->can('move', $asset);
    }

    public function run($assets, $values)
    {
        $assets->each->move($values['folder']);
    }

    protected function fieldItems()
    {
        $options = AssetContainer::find($this->context['container'])
            ->assetFolders()
            ->mapWithKeys(function ($folder) {
                return [$folder->path() => $folder->path()];
            })
            ->prepend('/', '/')
            ->all();

        return [
            'folder' => [
                'type' => 'select',
                'options' => $options,
                'validate' => 'required',
            ]
        ];
    }
}
