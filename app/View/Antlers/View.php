<?php

namespace Statamic\View\Antlers;

use Statamic\Events\ViewRendered;
use Facades\Statamic\View\Cascade;

class View
{
    protected $data = [];
    protected $layout;
    protected $template;
    protected $cascadeContent;

    public static function make($template = null)
    {
        $view = new static;
        $view->template($template);

        return $view;
    }

    public function data($data = null)
    {
        if (! $data) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function layout($layout = null)
    {
        if (count(func_get_args()) === 0) {
            return $this->layout;
        }

        $this->layout = $layout;

        return $this;
    }

    public function template($template = null)
    {
        if (! $template) {
            return $this->template;
        }

        $this->template = $template;

        return $this;
    }

    public function render()
    {
        $cascade = array_merge($this->data, $this->cascade());

        $contents = view($this->template, $cascade)->render();

        if ($this->layout) {
            $contents = view($this->layout, array_merge($cascade, [
                'template_content' => $contents
            ]))->render();
        }

        $contents = app('antlers.view.parser')->injectNoparse($contents);

        ViewRendered::dispatch($this);

        return $contents;
    }

    protected function cascade()
    {
        return Cascade::instance()
            ->withContent($this->cascadeContent)
            ->hydrate()
            ->toArray();
    }

    public function cascadeContent($content)
    {
        $this->cascadeContent = $content;

        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }
}
