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
        $this->app->instance('statamic.tags', collect());

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

        foreach ($tags as $tag) {
            $this->app['statamic.tags'][$tag::handle()] = $tag;
        }

        $this->registerExtensionsInAppFolder('Tags');
    }

    /**
     * Register modifiers.
     *
     * @return void
     */
    protected function registerModifiers()
    {
        $this->app->instance('statamic.modifiers', collect());

        $this->registerBundledModifiers();
        $this->registerExtensionsInAppFolder('Modifiers');
    }

    /**
     * Register bundled modifiers.
     *
     * @return void
     */
    protected function registerBundledModifiers()
    {
        $methods = array_diff(
            get_class_methods(BaseModifiers::class),
            get_class_methods(Modifier::class)
        );

        foreach ($methods as $method) {
            $this->app['statamic.modifiers'][$method] = "Statamic\\View\\BaseModifiers@{$method}";
        }

        foreach ($this->bundledModifierAliases as $alias => $actual) {
            $this->app['statamic.modifiers'][$alias] = "Statamic\\View\\BaseModifiers@{$actual}";
        }
    }

    /**
     * Register fieldtypes.
     *
     * @return void
     */
    protected function registerFieldtypes()
    {
        $this->app->instance('statamic.fieldtypes', collect());

        $this->registerBundledFieldtypes();
        $this->registerExtensionsInAppFolder('Fieldtypes');
    }

    /**
     * Register bundled fieldtypes.
     *
     * @return void
     */
    protected function registerBundledFieldtypes()
    {
        foreach ($this->bundledFieldtypes as $tag) {
            $studly = studly_case($tag);
            $this->app['statamic.fieldtypes'][$tag] = "Statamic\\Addons\\{$studly}\\{$studly}Fieldtype";
        }

        foreach ($this->bundledFieldtypeAliases as $alias => $actual) {
            $this->app['statamic.fieldtypes'][$alias] = "Statamic\\Addons\\{$actual}\\{$actual}Fieldtype";
        }

        foreach ($this->fieldtypes as $handle => $class) {
            $this->app['statamic.fieldtypes'][$class::handle()] = $class;
        }
    }

    /**
     * Register filters.
     *
     * @return void
     */
    protected function registerFilters()
    {
        $this->app->instance('statamic.filters', collect());

        $filters = [
            Filters\Site::class,
            Filters\UserRole::class,
            Filters\UserGroup::class,
        ];

        foreach ($filters as $filter) {
            $this->app['statamic.filters'][$filter::handle()] = $filter;
        }

        $this->registerExtensionsInAppFolder('Filters');
    }

    /**
     * Register actions.
     *
     * @return void
     */
    protected function registerActions()
    {
        $this->app->instance('statamic.actions', collect());

        $filters = [
            Actions\Delete::class,
            Actions\Publish::class,
            Actions\Unpublish::class,
            Actions\SendActivationEmail::class,
            Actions\MoveAsset::class,
        ];

        foreach ($filters as $filter) {
            $this->app['statamic.actions'][$filter::handle()] = $filter;
        }

        $this->registerExtensionsInAppFolder('Actions');
    }

    /**
     * Register widgets.
     *
     * @return void
     */
    protected function registerWidgets()
    {
        $this->app->instance('statamic.widgets', collect());

        $widgets = [
            \Statamic\Widgets\GettingStarted::class,
            \Statamic\Widgets\Collection::class,
            \Statamic\Widgets\Template::class,
            \Statamic\Widgets\Updater::class,
            \Statamic\Forms\Widget::class,
        ];

        foreach ($widgets as $widget) {
            $this->app['statamic.widgets'][$widget::handle()] = $widget;
        }

        $this->registerExtensionsInAppFolder('Widgets');
    }

    /**
     * Register extensions in a specific app folder.
     *
     * This prevents requiring users to manually bind their extensions.
     *
     * @param string $folder
     * @return void
     */
    protected function registerExtensionsInAppFolder($folder)
    {
        if (! $this->app['files']->exists($path = app_path($folder))) {
            return;
        }

        foreach ($this->app['files']->files($path) as $file) {
            $class = $file->getBasename('.php');
            $fqcn = $this->getAppNamespace() . "{$folder}\\{$class}";
            $handle = $fqcn::handle();
            $extensionType = strtolower($folder);
            $this->app["statamic.{$extensionType}"][$handle] = $fqcn;
        }
    }
}
