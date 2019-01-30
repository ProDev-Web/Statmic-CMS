<?php

namespace Statamic\CP\Navigation;

use Statamic\API\Nav;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Forms\Form;
use Statamic\API\Structure as StructureAPI;
use Statamic\API\Collection as CollectionAPI;
use Statamic\Contracts\Data\Globals\GlobalSet;
use Statamic\Contracts\Data\Entries\Collection;
use Statamic\Contracts\Data\Structures\Structure;

class DefaultNav
{
    /**
     * Make default nav items.
     */
    public static function make()
    {
        (new static)
            ->makeContentSection()
            ->makeToolsSection()
            ->makeUsersSection()
            ->makeSiteSection();
    }

    /**
     * Make content section items.
     *
     * @return $this
     */
    protected function makeContentSection()
    {
        Nav::content('Collections')
            ->route('collections.index')
            ->icon('content-writing')
            ->can('index', Collection::class)
            ->children(CollectionAPI::all()->map(function ($collection) {
                return Nav::item($collection->title())
                          ->url($collection->showUrl())
                          ->can('view', $collection);
            }));

        Nav::content('Structure')
            ->route('structures.index')
            ->icon('hierarchy-files')
            ->can('index', Structure::class)
            ->children(StructureAPI::all()->map(function ($structure) {
                return Nav::item($structure->title())
                          ->url($structure->showUrl())
                          ->can('view', $structure);
            }));

        Nav::content('Taxonomies')
            ->route('')
            ->icon('tags');

        Nav::content('Assets')
            ->route('assets.index')
            ->icon('assets');

        Nav::content('Globals')
            ->route('globals.index')
            ->icon('earth')
            ->can('index', GlobalSet::class);

        return $this;
    }

    /**
     * Make tools section items.
     *
     * @return $this
     */
    protected function makeToolsSection()
    {
        Nav::tools('Forms')
            ->route('forms.index')
            ->icon('drawer-file')
            ->can('index', Form::class);

        Nav::tools('Updates')
            ->route('updater.index')
            ->icon('loading-bar')
            ->view('statamic::nav.updates')
            ->can('view updates');

        Nav::tools('Utilities')
            ->route('utilities.phpinfo')
            ->currentClass('utilities*')
            ->icon('settings-slider')
            ->children([
                Nav::item('PHP Info')->route('utilities.phpinfo'),
                Nav::item('Clear Cache')->route('utilities.clear-cache.index'),
                Nav::item('Search')->route('utilities.search'),
            ]);

        return $this;
    }

    /**
     * Make users section items.
     *
     * @return $this
     */
    protected function makeUsersSection()
    {
        Nav::users('Users')
            ->route('users.index')
            ->icon('users-box')
            ->can('index', User::class);

        Nav::users('Groups')
            ->route('user-groups.index')
            ->icon('users-multiple');

        Nav::users('Permissions')
            ->route('roles.index')
            ->icon('shield-key');

        return $this;
    }

    /**
     * Make site section items.
     *
     * @return $this
     */
    protected function makeSiteSection()
    {
        Nav::site('Addons')
            ->route('addons.index')
            ->icon('addons');

        Nav::site('Preferences')
            ->route('')
            ->icon('hammer-wrench');

        Nav::site('Fieldsets')
            ->route('fieldsets.index')
            ->icon('wireframe');

        Nav::site('Blueprints')
            ->route('blueprints.index')
            ->icon('blueprints');

        return $this;
    }
}
