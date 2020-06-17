<?php

namespace Statamic\Providers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Statamic\Actions;
use Statamic\Actions\Action;
use Statamic\Extend\Manifest;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes;
use Statamic\Modifiers\CoreModifiers;
use Statamic\Modifiers\Modifier;
use Statamic\Query\Scopes;
use Statamic\Query\Scopes\Scope;
use Statamic\Support\Str;
use Statamic\Tags;
use Statamic\Widgets;
use Statamic\Widgets\Widget;

class ExtensionServiceProvider extends ServiceProvider
{
    protected $actions = [
        Actions\Delete::class,
        Actions\Publish::class,
        Actions\Unpublish::class,
        Actions\SendPasswordReset::class,
        Actions\MoveAsset::class,
        Actions\RenameAsset::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\Arr::class,
        Fieldtypes\AssetContainer::class,
        Fieldtypes\AssetFolder::class,
        Fieldtypes\Assets\Assets::class,
        Fieldtypes\Bard::class,
        Fieldtypes\Bard\Buttons::class,
        Fieldtypes\ButtonGroup::class,
        Fieldtypes\Blueprints::class,
        Fieldtypes\Checkboxes::class,
        Fieldtypes\Code::class,
        Fieldtypes\CollectionRoutes::class,
        Fieldtypes\Collections::class,
        Fieldtypes\Color::class,
        Fieldtypes\Date::class,
        Fieldtypes\Entries::class,
        Fieldtypes\GlobalSetSites::class,
        Fieldtypes\Grid::class,
        Fieldtypes\Hidden::class,
        Fieldtypes\Integer::class,
        Fieldtypes\Link::class,
        Fieldtypes\Lists::class,
        Fieldtypes\Markdown::class,
        Fieldtypes\NestedFields::class,
        Fieldtypes\Radio::class,
        Fieldtypes\Range::class,
        Fieldtypes\Replicator::class,
        Fieldtypes\Revealer::class,
        Fieldtypes\Section::class,
        Fieldtypes\Select::class,
        Fieldtypes\Sets::class,
        Fieldtypes\Sites::class,
        Fieldtypes\Structures::class,
        Fieldtypes\Slug::class,
        Fieldtypes\Table::class,
        Fieldtypes\Tags::class,
        Fieldtypes\Taxonomy::class,
        Fieldtypes\Taxonomies::class,
        Fieldtypes\Template::class,
        Fieldtypes\Text::class,
        Fieldtypes\Textarea::class,
        Fieldtypes\Time::class,
        Fieldtypes\Toggle::class,
        Fieldtypes\UserGroups::class,
        Fieldtypes\UserRoles::class,
        Fieldtypes\Users::class,
        Fieldtypes\Video::class,
        Fieldtypes\Yaml::class,
        \Statamic\Forms\Fieldtype::class,
    ];

    protected $modifierAliases = [
        '+' => 'add',
        '-' => 'subtract',
        '*' => 'multiply',
        '/' => 'divide',
        '%' => 'mod',
        '^' => 'exponent',
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
        'tz' => 'timezone',
        'inFuture' => 'isFuture',
        'inPast' => 'isPast',
        'as' => 'alias',
    ];

    protected $scopes = [
        Scopes\Filters\Fields::class,
        Scopes\Filters\Blueprint::class,
        Scopes\Filters\Status::class,
        Scopes\Filters\Site::class,
        Scopes\Filters\UserRole::class,
        Scopes\Filters\UserGroup::class,
        Scopes\Filters\Collection::class,
    ];

    protected $tags = [
        Tags\Asset::class,
        Tags\Assets::class,
        Tags\Cache::class,
        Tags\Can::class,
        Tags\Collection\Collection::class,
        Tags\Dd::class,
        Tags\Dump::class,
        Tags\GetContent::class,
        Tags\GetFiles::class,
        Tags\Glide::class,
        Tags\In::class,
        Tags\Increment::class,
        Tags\Is::class,
        Tags\Iterate::class,
        Tags\Link::class,
        Tags\Locales::class,
        Tags\Markdown::class,
        Tags\Member::class,
        Tags\Mix::class,
        Tags\Nav::class,
        Tags\NotFound::class,
        Tags\Obfuscate::class,
        Tags\ParentTags::class,
        Tags\Partial::class,
        Tags\Path::class,
        Tags\Query::class,
        Tags\Range::class,
        Tags\Redirect::class,
        Tags\Relate::class,
        Tags\Rotate::class,
        Tags\Route::class,
        Tags\Scope::class,
        Tags\Set::class,
        Tags\Section::class,
        Tags\Session::class,
        Tags\Structure::class,
        Tags\Svg::class,
        Tags\Taxonomy\Taxonomy::class,
        Tags\Theme::class,
        Tags\Trans::class,
        Tags\TransChoice::class,
        Tags\Users::class,
        Tags\Widont::class,
        Tags\Yields::class,
        \Statamic\Forms\Tags::class,
        \Statamic\Auth\UserTags::class,
        \Statamic\Auth\Protect\Tags::class,
        \Statamic\OAuth\Tags::class,
        \Statamic\Search\Tags::class,
    ];

    protected $widgets = [
        Widgets\Collection::class,
        Widgets\GettingStarted::class,
        Widgets\Header::class,
        Widgets\Template::class,
        Widgets\Updater::class,
        \Statamic\Forms\Widget::class,
    ];

    public function register()
    {
        $this->registerExtensions();
        $this->registerAddonManifest();
    }

    protected function registerAddonManifest()
    {
        $this->app->instance(Manifest::class, new Manifest(
            new Filesystem,
            $this->app->basePath(),
            $this->app->bootstrapPath().'/cache/addons.php'
        ));
    }

    protected function registerExtensions()
    {
        $this->app->instance('statamic.extensions', collect());

        $types = [
            'actions' => [
                'class' => Action::class,
                'directory' => 'Actions',
                'extensions' => $this->actions,
            ],
            'fieldtypes' => [
                'class' => Fieldtype::class,
                'directory' => 'Fieldtypes',
                'extensions' => $this->fieldtypes,
            ],
            'modifiers' => [
                'class' => Modifier::class,
                'directory' => 'Modifiers',
            ],
            'scopes' => [
                'class' => Scope::class,
                'directory' => 'Scopes',
                'extensions' => $this->scopes,
            ],
            'tags' => [
                'class' => Tags\Tags::class,
                'directory' => 'Tags',
                'extensions' => $this->tags,
            ],
            'widgets' => [
                'class' => Widget::class,
                'directory' => 'Widgets',
                'extensions' => $this->widgets,
            ],
        ];

        foreach ($types as $key => $type) {
            $this->registerBindingAlias($key, $type['class']);
            $this->registerCoreExtensions($type['extensions'] ?? []);
            $this->registerAppExtensions($type['directory'], $type['class']);
        }

        $this->registerCoreModifiers();
    }

    protected function registerBindingAlias($key, $class)
    {
        return $this->app->bind('statamic.'.$key, function ($app) use ($class) {
            return $app['statamic.extensions'][$class];
        });
    }

    protected function registerCoreExtensions($extensions)
    {
        foreach ($extensions as $extension) {
            $extension::register();
        }
    }

    protected function registerCoreModifiers()
    {
        $methods = array_diff(
            get_class_methods(CoreModifiers::class),
            get_class_methods(Modifier::class)
        );

        $modifiers = collect();

        foreach ($methods as $method) {
            $modifiers[Str::snake($method)] = "Statamic\\Modifiers\\CoreModifiers@{$method}";
        }

        foreach ($this->modifierAliases as $alias => $actual) {
            $modifiers[$alias] = "Statamic\\Modifiers\\CoreModifiers@{$actual}";
        }

        $this->app['statamic.extensions'][Modifier::class] = collect()
            ->merge($this->app['statamic.extensions'][Modifier::class] ?? [])
            ->merge($modifiers);
    }

    protected function registerAppExtensions($folder, $requiredClass)
    {
        if (! $this->app['files']->exists($path = app_path($folder))) {
            return;
        }

        foreach ($this->app['files']->files($path) as $file) {
            $class = $file->getBasename('.php');
            $fqcn = $this->app->getNamespace()."{$folder}\\{$class}";
            if (is_subclass_of($fqcn, $requiredClass)) {
                $fqcn::register();
            }
        }
    }
}
