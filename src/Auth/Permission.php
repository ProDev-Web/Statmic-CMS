<?php

namespace Statamic\Auth;

use Statamic\Support\Traits\FluentlyGetsAndSets;

class Permission
{
    use FluentlyGetsAndSets;

    protected $value;
    protected $placeholder;
    protected $placeholderLabel;
    protected $replacement;
    protected $callback;
    protected $children;
    protected $label;
    protected $description;
    protected $group;

    public function value(string $value = null)
    {
        return $this->fluentlyGetOrSet('value')->args(func_get_args());
    }

    public function originalLabel()
    {
        return $this->label;
    }

    public function label(string $label = null)
    {
        if (func_num_args() > 0) {
            $this->label = $label;

            return $this;
        }

        return __($this->label ?? $this->value, [$this->placeholder => $this->placeholderLabel]);
    }

    public function placeholder(string $placeholder = null)
    {
        return $this->fluentlyGetOrSet('placeholder')->args(func_get_args());
    }

    public function placeholderLabel(string $label = null)
    {
        return $this->fluentlyGetOrSet('placeholderLabel')->args(func_get_args());
    }

    public function replacements(string $placeholder, callable $callback)
    {
        $this->placeholder = $placeholder;
        $this->callback = $callback;

        return $this;
    }

    public function replacement(string $replacement = null)
    {
        return $this->fluentlyGetOrSet('replacement')->args(func_get_args());
    }

    public function callback()
    {
        return $this->callback;
    }

    public function permissions()
    {
        if (! $this->callback) {
            return collect([$this]);
        }

        // The callback should return an array where the keys are the replacements for the
        // permission values, and the values are the strings to be replaced inside the
        // labels. eg. ['blog' => 'Blog', 'downloads' => 'Downloadable Products']
        $items = call_user_func($this->callback);

        return collect($items)->map(function ($replacement) {
            $value = str_replace('{'.$this->placeholder.'}', $replacement['value'], $this->value());
            $label = $this->label ?? str_replace('{'.$this->placeholder.'}', ':'.$this->placeholder, $this->value());

            $replaced = (new self)
                ->value($value)
                ->label($label)
                ->replacement($replacement['value'])
                ->placeholder($this->placeholder)
                ->placeholderLabel($replacement['label'])
                ->group($this->group());

            if ($this->children()) {
                $replaced->children($this->children()->all());
            };

            return $replaced;
        })->values();
    }

    public function children(array $children = null)
    {
        return $this
            ->fluentlyGetOrSet('children')
            ->getter(function ($children) {
                return $children ?? collect();
            })
            ->setter(function ($children) {
                return collect($children)->map->group($this->group);
            })
            ->args(func_get_args());
    }

    public function toTree()
    {
        return $this->permissions()->map(function ($permission) {
            $children = $permission->children();

            if ($permission->placeholder()) {
                $children = $children->map(function ($child) use ($permission) {
                    $value = str_replace('{'.$this->placeholder.'}', $permission->replacement(), $child->value());
                    $label = $child->originalLabel() ?? str_replace('{'.$this->placeholder.'}', ':'.$this->placeholder, $child->value());

                    $replaced = (new self)
                        ->value($value)
                        ->label($label)
                        ->replacement($permission->replacement())
                        ->placeholder($permission->placeholder())
                        ->placeholderLabel($permission->placeholderLabel())
                        ->group($permission->group());

                    if ($children = $child->children()) {
                        $replaced->children($children->all());
                    }

                    return $replaced;
                });
            }

            return [
                'value' => $permission->value(),
                'label' => $permission->label(),
                'description' => $permission->description(),
                'group' => $permission->group(),
                'children' => $children->flatMap->toTree()->all()
            ];
        })->all();
    }

    public function group(string $group = null)
    {
        return $this->fluentlyGetOrSet('group')->args(func_get_args());
    }

    public function description()
    {
        return $this->fluentlyGetOrSet('description')->args(func_get_args());
    }
}
