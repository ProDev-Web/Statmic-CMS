const mix = require('laravel-mix');
const webpack = require('webpack');
const tailwindcss = require('tailwindcss');
const src = 'resources';
const dest = 'resources/dist';

mix.sass(`${src}/sass/cp.scss`, `${dest}/css`).options({
    processCssUrls: false,
    postCss: [
        tailwindcss('./tailwind.js'),
        require('autoprefixer')
    ],
});

mix.js(`${src}/js/bootstrap.js`, `${dest}/js`);
mix.js(`${src}/js/app.js`, `${dest}/js`);
mix.extract([
    'autosize',
    'axios',
    'baremetrics-calendar',
    'codemirror',
    'cookies-js',
    'dmuploader',
    'jquery',
    'jquery-ui',
    'luminous-lightbox',
    'marked',
    'marked-plaintext',
    'medium-editor',
    'medium-editor-autohr',
    'moment',
    'mousetrap',
    'nested-sortable',
    'redactor',
    'selectize',
    'sweetalert',
    '@shopify/draggable',
    'tippy.js',
    'transliterations',
    'underscore',
    'vue',
    'vue-clickaway',
    'vue-typeahead'
]);

mix.copyDirectory(`${src}/img`, `${dest}/img`);
mix.copyDirectory(`${src}/svg`, `${dest}/svg`);
mix.copyDirectory(`${src}/audio`, `${dest}/audio`);
mix.copyDirectory(`${src}/fonts`, `${dest}/fonts`);

// mix.browserSync({
//     proxy: process.env.BROWSERSYNC_PROXY || 'talonsbeard.test',
//     startPath: '/cp',
//     open: false,
//     files: [
//         `${src}/views/**/*.blade.php`,
//         `${dest}/js/*.js`,
//         `${dest}/css/*.css`,
//         `app/site/addons/**/*`
//     ]
// });

mix.sourceMaps();

mix.options({ extractVueStyles: true });

mix.webpackConfig({
    devtool: 'source-map',
    plugins: [
        // Vendor files (eg. twitter bootstrap) reference globals
        new webpack.ProvidePlugin({ $: "jquery", jQuery: "jquery" }),

        // Our files reference globals
        new webpack.ProvidePlugin({ Vue: "vue" }),
        new webpack.ProvidePlugin({ _: "underscore" })
    ]
})
