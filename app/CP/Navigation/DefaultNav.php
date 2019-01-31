<?php

namespace Statamic\CP\Navigation;

use Statamic\API\Nav;
use Statamic\API\Form as FormAPI;
use Statamic\API\Role as RoleAPI;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Forms\Form;
use Statamic\API\GlobalSet as GlobalSetAPI;
use Statamic\API\Structure as StructureAPI;
use Statamic\API\UserGroup as UserGroupAPI;
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
            ->can('index', GlobalSet::class)
            ->children(GlobalSetAPI::all()->map(function ($globalSet) {
                return Nav::item($globalSet->title())
                          ->url($globalSet->editUrl())
                          ->can('view', $globalSet);
            }));

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
            ->can('index', Form::class)
            ->children(FormAPI::all()->map(function ($form) {
                return Nav::item($form->title())
                          ->url($form->editUrl())
                          ->can('view', $form);
            }));

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
            ->icon('users-multiple')
            // ->can() // TODO: Permission to manage groups?
            ->children(UserGroupAPI::all()->map(function ($userGroup) {
                return Nav::item($userGroup->title())
                          ->url($userGroup->editUrl());
            }));

        Nav::users('Permissions')
            ->route('roles.index')
            ->icon('shield-key')
            // ->can() // TODO: Permission to manage permissions?
            ->children(RoleAPI::all()->map(function ($role) {
                return Nav::item($role->title())
                          ->url($role->editUrl());
            }));

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
