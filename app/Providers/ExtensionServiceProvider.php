<?php

namespace Statamic\Providers;

use Statamic\Tags;
use Statamic\Actions;
use Statamic\Filters;
use Statamic\DataStore;
use Statamic\Extend\Modifier;
use Statamic\Fields\Fieldtypes;
use Statamic\View\BaseModifiers;
use Statamic\Extensions\FileStore;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Statamic\Extend\Management\Manifest;
use Illuminate\Console\DetectsApplicationNamespace;

class ExtensionServiceProvider extends ServiceProvider
{
    use DetectsApplicationNamespace;

    /**
     * Fieldtypes bundled with Statamic.
     *
     * @var array
     */
    protected $bundledFieldtypes = [
        'arr', 'asset_container', 'asset_folder', 'bard', 'checkboxes', 'collection',
        'date', 'fieldset', 'hidden', 'integer', 'lists', 'locale_settings', 'markdown',
        'pages', 'partial', 'radio', 'redactor', 'redactor_settings', 'relate', 'replicator', 'replicator_sets',
        'theme', 'time', 'title', 'toggle', 'user_groups', 'user_roles', 'video', 'yaml',
        'revealer', 'section', 'select', 'slug', 'suggest', 'table', 'tags', 'taxonomy', 'template', 'text', 'textarea',
    ];

    /**
     * Aliases for fieldtypes bundled with Statamic.
     *
     * @var array
     */
    protected $bundledFieldtypeAliases = [
        'array' => 'Arr',
        'list' => 'lists'
    ];

    /**
     * Aliases for modifiers bundled with Statamic.
     *
     * @var array
     */
    protected $bundledModifierAliases = [
        '+' => 'add',
        '-' => 'subtract',
        '*' => 'multiply',
        '/' => 'divide',
        '%' => 'mod',
        '^' => 'exponent',
        'dd' => 'dump',
        'ago' => 'relative',
        'until' => 'relative',
        'since' => 'relative',
        'specialchars' => 'sanitize',
        'htmlspecialchars' => 'sanitize',
        'striptags' => 'stripTags',
        'join' => 'joinplode',
        'implode' => 'joinplode',
        'list' => 'joinplode',
        'piped' => 'optionList',
        'json' => 'toJson',
        'email' => 'obfuscateEmail',
        'l10n' => 'formatLocalized',
        'lowercase' => 'lower',
        '85' => 'slackEasterEgg',
        'tz' => 'timezone',
        'in_future' => 'isFuture',
        'inPast' => 'isPast',
        'in_past' => 'isPast',
        'as' => 'scopeAs',
    ];

    /**
     * Widgets bundled with Statamic.
     *
     * @var array
     */
    protected $bundledWidgets = [
        'getting-started', 'collection', 'template', 'updater', 'form'
    ];

    protected $fieldtypes = [
        Fieldtypes\Assets::class,
        Fieldtypes\Blueprints::class,
        Fieldtypes\Checkboxes::class,
        Fieldtypes\Code::class,
        Fieldtypes\Collections::class,
        Fieldtypes\Date::class,
        \Statamic\Forms\Fieldtype::class,
        Fieldtypes\Grid::class,
        Fieldtypes\Markdown::class,
        Fieldtypes\NestedFields::class,
        Fieldtypes\Relationship::class,
        Fieldtypes\Radio::class,
        Fieldtypes\Template::class,
        Fieldtypes\Time::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance(Manifest::class, new Manifest(
            new Filesystem,
            $this->app->basePath(),
            $this->app->bootstrapPath().'/cache/addons.php'
        ));

        $this->registerTags();
        $this->registerModifiers();
        $this->registerFieldtypes();
        $this->registerFilters();
        $this->registerActions();
        $this->registerWidgets();
    }

    /**
     * Register tags.
     *
     * @return void
     */
    protected function registerTags()
    {
        $parent = 'statamic.tags';

        $tags = [
            Tags\Asset::class, Tags\Assets::class, Tags\Cache::class, Tags\Can::class, Tags\Collection::class,
            Tags\Dump::class, Tags\Entries::class, Tags\Env::class, Tags\GetContent::class, Tags\GetFiles::class,
            Tags\GetValue::class, Tags\Glide::class, Tags\In::class, Tags\Is::class, Tags\Link::class,
            Tags\Locales::class, Tags\Markdown::class, Tags\Member::class, Tags\Mix::class, Tags\Nav::class,
            Tags\NotFound::class, Tags\OAuth::class, Tags\Obfuscate::class, Tags\Pages::class, Tags\ParentTags::class,
            Tags\Partial::class, Tags\Path::class, Tags\Redirect::class, Tags\Relate::class, Tags\Rotate::class,
            Tags\Routes::class, Tags\Section::class, Tags\Taxonomy::class, Tags\Theme::class, Tags\Trans::class,
            Tags\TransChoice::class, Tags\Users::class, Tags\Widont::class, Tags\Yields::class,
            \Statamic\Forms\Tags::class, \Statamic\Auth\UserTags::class, \Statamic\Auth\Protect\Tags::class,
            \Statamic\Search\Tags::class
        ];

        $this->registerParent($parent);

        foreach ($tags as $tag) {
            $this->registerExtension($tag, $parent);
            $this->registerAliases($tag, $parent);
        }

        $this->registerExtensionsInAppFolder('Tags', $parent);
    }

    /**
     * Register modifiers.
     *
     * @return void
     */
    protected function registerModifiers()
    {
        $parent = 'statamic.modifiers';

        $this->registerParent($parent);
        $this->registerBundledModifiers($parent);
        $this->registerExtensionsInAppFolder('Modifiers', $parent);
    }

    /**
     * Register bundled modifiers.
     *
     * @param string $parent
     * @return void
     */
    protected function registerBundledModifiers($parent)
    {
        $methods = array_diff(
            get_class_methods(BaseModifiers::class),
            get_class_methods(Modifier::class)
        );

        foreach ($methods as $method) {
            $this->app[$parent][$method] = "Statamic\\View\\BaseModifiers@{$method}";
        }

        foreach ($this->bundledModifierAliases as $alias => $actual) {
            $this->app[$parent][$alias] = "Statamic\\View\\BaseModifiers@{$actual}";
        }
    }

    /**
     * Register fieldtypes.
     *
     * @return void
     */
    protected function registerFieldtypes()
    {
        $parent = 'statamic.fieldtypes';

        $this->registerParent($parent);
        $this->registerBundledFieldtypes($parent);
        $this->registerExtensionsInAppFolder('Fieldtypes', $parent);
    }

    /**
     * Register bundled fieldtypes.
     *
     * @param string $parent
     * @return void
     */
    protected function registerBundledFieldtypes($parent)
    {
        foreach ($this->bundledFieldtypes as $tag) {
            $studly = studly_case($tag);
            $this->app[$parent][$tag] = "Statamic\\Addons\\{$studly}\\{$studly}Fieldtype";
        }

        foreach ($this->bundledFieldtypeAliases as $alias => $actual) {
            $this->app[$parent][$alias] = "Statamic\\Addons\\{$actual}\\{$actual}Fieldtype";
        }

        foreach ($this->fieldtypes as $handle => $class) {
            $this->app[$parent][$class::handle()] = $class;
        }
    }

    /**
     * Register filters.
     *
     * @return void
     */
    protected function registerFilters()
    {
        $parent = 'statamic.filters';

        $filters = [
            Filters\Site::class,
            Filters\UserRole::class,
            Filters\UserGroup::class,
        ];

        $this->registerParent($parent);

        foreach ($filters as $filter) {
            $this->registerExtension($filter, $parent);
        }

        $this->registerExtensionsInAppFolder('Filters', $parent);
    }

    /**
     * Register actions.
     *
     * @return void
     */
    protected function registerActions()
    {
        $parent = 'statamic.actions';

        $actions = [
            Actions\Delete::class,
            Actions\Publish::class,
            Actions\Unpublish::class,
            Actions\SendActivationEmail::class,
            Actions\MoveAsset::class,
        ];

        $this->registerParent($parent);

        foreach ($actions as $action) {
            $this->registerExtension($action, $parent);
        }

        $this->registerExtensionsInAppFolder('Actions', $parent);
    }

    /**
     * Register widgets.
     *
     * @return void
     */
    protected function registerWidgets()
    {
        $parent = 'statamic.widgets';

        $widgets = [
            \Statamic\Widgets\GettingStarted::class,
            \Statamic\Widgets\Collection::class,
            \Statamic\Widgets\Template::class,
            \Statamic\Widgets\Updater::class,
            \Statamic\Forms\Widget::class,
        ];

        $this->registerParent($parent);

        foreach ($widgets as $widget) {
            $this->registerExtension($widget, $parent);
        }

        $this->registerExtensionsInAppFolder('Widgets', $parent);
    }

    /**
     * Register parent.
     *
     * @param string $parent
     * @return void
     */
    protected function registerParent($parent)
    {
        $this->app->instance($parent, collect());
    }

    /**
     * Register extension.
     *
     * @param string $extension
     * @param string $parent
     * @return void
     */
    protected function registerExtension($extension, $parent)
    {
        $this->app[$parent][$extension::handle()] = $extension;
    }

    /**
     * Register aliases.
     *
     * @param string $extension
     * @param string $parent
     * @return void
     */
    protected function registerAliases($extension, $parent)
    {
        foreach ($extension::aliases() as $alias) {
            $this->app[$parent][$alias] = $extension;
        }
    }

    /**
     * Register extensions in a specific app folder.
     *
     * This prevents requiring users to manually bind their extensions.
     *
     * @param string $folder
     * @param string $parent
     * @return void
     */
    protected function registerExtensionsInAppFolder($folder, $parent)
    {
        if (! $this->app['files']->exists($path = app_path($folder))) {
            return;
        }

        foreach ($this->app['files']->files($path) as $file) {
            $class = $file->getBasename('.php');
            $fqcn = $this->getAppNamespace() . "{$folder}\\{$class}";
            $handle = $fqcn::handle();
            $this->app[$parent][$handle] = $fqcn;
        }
    }
}
