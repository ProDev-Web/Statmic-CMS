<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Git Integration
    |--------------------------------------------------------------------------
    |
    | Whether Statamic's git integration should be enabled. This feature
    | assumes that git is already installed and accessible by your
    | PHP process' server user. For more info, see the docs at:
    |
    | https://statamic.dev/git-integration
    |
    */

    'enabled' => env('STATAMIC_GIT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Automatically Run
    |--------------------------------------------------------------------------
    |
    | By default, git will automatically commit when `Saved` or `Deleted`
    | events are fired. If you prefer users to manually trigger git
    | commits using the `Git` utility GUI, set this to `false`.
    |
    */

    'automatic' => env('STATAMIC_GIT_AUTOMATIC', true),

    /*
    |--------------------------------------------------------------------------
    | Git User
    |--------------------------------------------------------------------------
    |
    | The git user that will be used when committing changes to your repo.
    | By default, the currently authenticated user name and email will
    | be used, otherwise it will fall back to the below user info.
    |
    */

    'use_authenticated' => true,

    'user' => [
        'name' => env('STATAMIC_GIT_USER_NAME', 'Spock'),
        'email' => env('STATAMIC_GIT_USER_EMAIL', 'spock@example.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracked Paths
    |--------------------------------------------------------------------------
    |
    | Define the tracked paths to be considered when staging changes. Default
    | stache and file locations are already set up for you, but feel free
    | to modify these paths to suit your storage config. Referencing
    | absolute paths to external repos is also completely valid.
    |
    */

    'paths' => [
        base_path('content'),
        base_path('resources/blueprints'),
        base_path('resources/fieldsets'),
        base_path('resources/forms'),
        base_path('resources/users'),
        base_path('users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    | Define a list commands to be run when Statamic is ready to `git add`
    | and `git commit` your changes. These commands will be run once
    | per repo, attempting to consolidate commits where possible.
    |
    */

    'commands' => [
        'git add {{ paths }}',
        'git -c "user.name={{ name }}" -c "user.email={{ email }}" commit -m "{{ message }}"',
    ],

    /*
    |--------------------------------------------------------------------------
    | Push
    |--------------------------------------------------------------------------
    |
    | Determine whether `git push` should be run after the commands above
    | have finished.  This is disabled by default, but can be enabled
    | globally, or per environment using the provided variable.
    |
    */

    'push' => env('STATAMIC_GIT_PUSH', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored Events
    |--------------------------------------------------------------------------
    |
    | Statamic will listen on all `Saved` and `Deleted` events, as well
    | as any events registered by installed addons. If you wish to
    | ignore any specific events, you may reference them here.
    |
    */

    'ignored_events' => [
        // \Statamic\Events\Data\UserSaved::class,
    ],

];
