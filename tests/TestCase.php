<?php

namespace Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $shouldFakeVersion = true;

    protected function setUp()
    {
        parent::setUp();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventSavingStacheItemsToDisk::class])) {
            $this->preventSavingStacheItemsToDisk();
        }

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->andReturn('3.0.0-testing');
        }
    }

    protected function getPackageProviders($app)
    {
        return ['Statamic\Providers\StatamicServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return ['Statamic' => 'Statamic\Statamic'];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'routes', 'static_caching',
            'sites', 'stache', 'system', 'theming', 'users'
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../config/{$config}.php"));
        }
    }

    protected function getEnvironmentSetUp($app)
    {
        // We changed the default sites setup but the tests assume defaults like the following.
        $app['config']->set('statamic.sites', [
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://localhost/',]
            ]
        ]);

        $app['config']->set('statamic.stache.stores.taxonomies.directory', __DIR__.'/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.collections.directory', __DIR__.'/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', __DIR__.'/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.structures.directory', __DIR__.'/__fixtures__/content/structures');
        $app['config']->set('statamic.stache.stores.globals.directory', __DIR__.'/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__.'/__fixtures__/content/assets');
        $app['config']->set('statamic.stache.stores.users.directory', __DIR__.'/__fixtures__/users');
    }

    protected function assertEveryItem($items, $callback)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $passes = 0;

        foreach ($items as $item) {
            if ($callback($item)) {
                $passes++;
            }
        }

        $this->assertEquals(count($items), $passes, 'Failed asserting that every item passes.');
    }

    protected function assertEveryItemIsInstanceOf($class, $items)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $matches = 0;

        foreach ($items as $item) {
            if ($item instanceof $class) {
                $matches++;
            }
        }

        $this->assertEquals(count($items), $matches, 'Failed asserting that every item is an instance of ' . $class);
    }

    protected function assertFileEqualsString($filename, $expected)
    {
        $this->assertFileExists($filename);

        $this->assertEquals($expected, file_get_contents($filename));
    }
}
