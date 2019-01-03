<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Here you may define any template or controller based routes. Each route
    | may contain wildcards, and can point to either a template name or an
    | array containing all the data to be passed in to the template.
    |
    | More info: https://docs.statamic.com/routing
    |
    */

    'routes' => [
        // '/login' => 'auth/login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vanity Routes
    |--------------------------------------------------------------------------
    |
    | A Vanity URL is a dummy, easy to remember URL that redirects you to a
    | permanent URL. For example, a http://example.com/promo URL that may
    | redirect you to http://example.com/blog/2016/09/this-months-promo
    |
    */

    'vanity' => [
        // '/promo' => '/blog/2017/09/this-months-promo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permanent Redirects
    |--------------------------------------------------------------------------
    |
    | While it's recommended to add permanent redirects (301s) on the server
    | for performence, you may also define them here for your convenience.
    |
    */

    'redirects' => [
        // '/here' => '/there',
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Route Prefix
    |--------------------------------------------------------------------------
    |
    | Some extensions may provide routes that go through the frontend of your
    | website. These URLs begin with the following prefix. We've chosen an
    | unobtrusive default but you are free to select whatever you want.
    |
    */

    'action' => '!',

];
