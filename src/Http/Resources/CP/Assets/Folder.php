<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Facades\Action;

class Folder extends Resource
{
    protected $withChildFolders = false;

    public function withChildFolders()
    {
        $this->withChildFolders = true;

        return $this;
    }

    public function toArray($request)
    {
        return [
            $this->merge($this->resource->toArray()),

            'actions' => Action::for('asset-folders', ['container' => $this->container()->handle()], $this),

            $this->mergeWhen($this->withChildFolders, function () {
                return ['folders' => Folder::collection($this->assetFolders()->values())];
            })
        ];
    }
}
