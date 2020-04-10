<?php

namespace Statamic\Actions;

use Statamic\Support\Str;
use Statamic\Fields\Fields;
use Statamic\Extend\HasTitle;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasFields;
use Statamic\Extend\RegistersItself;
use Illuminate\Contracts\Support\Arrayable;

abstract class Action implements Arrayable
{
    use HasHandle, HasTitle, HasFields, RegistersItself;

    protected static $binding = 'actions';

    protected $confirm = true;
    protected $dangerous = false;
    protected $fields = [];
    protected $context = [];

    public function filter($item)
    {
        return true;
    }

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    protected function fieldItems()
    {
        return $this->fields;
    }

    public function authorize($user, $item)
    {
        return true;
    }

    public function redirect()
    {
        return false;
    }

    public function buttonText()
    {
        /** @translation */
        return 'Run action|Run action on :count items';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to run this action?|Are you sure you want to run this action on :count items?';
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'confirm' => $this->confirm,
            'buttonText' => $this->buttonText(),
            'confirmationText' => $this->confirmationText(),
            'dangerous' => $this->dangerous,
            'fields' => $this->fields()->toPublishArray(),
            'meta' => $this->fields()->meta(),
            'context' => $this->context
        ];
    }
}
