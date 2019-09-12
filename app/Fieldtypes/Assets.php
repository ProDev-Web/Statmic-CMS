<?php

namespace Statamic\Fieldtypes;

use Statamic\Support\Arr;
use Statamic\Facades\Asset;
use Statamic\Facades\Helper;
use Statamic\Fields\Fieldtype;
use Statamic\Facades\AssetContainer;
use Statamic\Assets\AssetCollection;

class Assets extends Fieldtype
{
    protected $categories = ['media', 'relationship'];
    protected $defaultValue = [];

    protected $configFields = [
        'container' => [
            'type' => 'asset_container',
            'max_items' => 1,
            'instructions' => 'The asset container to work with.',
            'width' => 50
        ],
        'folder' => [
            'type' => 'asset_folder',
            'max_items' => 1,
            'instructions' => 'The folder to begin browsing in.',
            'width' => 50
        ],
        'restrict' => [
            'type' => 'toggle',
            'instructions' => 'Prevent users from navigating to other folders.',
            'width' => 50
        ],
        'max_files' => [
            'type' => 'integer',
            'instructions' => 'The maximum number of selectable assets.',
            'width' => 50
        ],
        'allow_uploads' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Allow new files to be uploaded?',
            'width' => 50
        ],
        'mode' => [
            'type' => 'select',
            'default' => 'grid',
            'options' => [
                'grid' => 'Grid',
                'list' => 'List',
            ],
            'instructions' => 'Default layout interface.'
        ],
    ];

    public function canHaveDefault()
    {
        return false;
    }

    public function preProcess($values)
    {
        if (is_null($values)) {
            return [];
        }

        return collect($values)->map(function ($value) {
            return $this->valueToId($value);
        })->filter()->values()->all();
    }

    protected function valueToId($value)
    {
        if (str_contains($value, '::')) {
            return $value;
        }

        return optional($this->container()->asset($value))->id();
    }

    public function process($data)
    {
        $max_files = (int) $this->config('max_files');

        $values = collect($data)->map(function ($id) {
            return Asset::find($id)->path();
        });

        return $this->config('max_files') === 1 ? $values->first() : $values->all();
    }

    public function preload()
    {
        $data = $this->getItemData($this->field->value() ?? $this->defaultValue);

        return compact('data');
    }

    public function getItemData($items)
    {
        $assets = new AssetCollection;

        foreach ($items as $url) {
            if (! $asset = Asset::find($url)) {
                continue;
            }

            if ($asset->isImage()) {
                $asset->setSupplement('thumbnail', $asset->thumbnailUrl('small'));
                $asset->setSupplement('toenail', $asset->thumbnailUrl('large'));
            }

            $assets->put($url, $asset);
        }

        return $assets->values();
    }

    public function augment($value)
    {
        $assets = collect($value)->map(function ($path) {
            return $this->container()->asset($path);
        })->filter()->values();

        return $this->config('max_files') === 1 ? $assets->first() : $assets;
    }

    protected function container()
    {
        return AssetContainer::find($this->config('container'));
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($max = $this->config('max_files')) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }

    public function preProcessIndex($data)
    {
        if (! $assets = $this->augment($data)) {
            return [];
        }

        if ($this->config('max_files') === 1) {
            $assets = collect([$assets]);
        }

        return $assets->map(function ($asset) {
            $arr = [
                'id' => $asset->id(),
                'is_image' => $isImage = $asset->isImage(),
                'url' => $asset->url(),
            ];

            if ($isImage) {
                $arr['thumbnail'] = cp_route('assets.thumbnails.show', [
                    'asset' => base64_encode($asset->id()),
                    'size' => 'thumbnail',
                ]);
            }

            return $arr;
        });
    }
}
