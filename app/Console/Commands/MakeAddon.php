<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Input\InputArgument;

class MakeAddon extends GeneratorCommand
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:addon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new addon';

    /**
     * The name of the addon.
     *
     * @var string
     */
    protected $addonName;

    /**
     * The path to where the addon will be generated.
     *
     * @var string
     */
    protected $addonPath;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->addonName = $this->getNameInput();

        $this->generateComposerJson();
        $this->generateServiceProvider();
        $this->addRepositoryPath();
        $this->composerRequireAddon();

        // TODO: handle flags for additional scaffolding

        $relativePath = $this->getRelativePath($this->addonPath());

        $this->info('Addon created successfully.');
        $this->comment("Your addon files await at: {$relativePath}");
    }

    /**
     * Generate composer.json.
     */
    protected function generateComposerJson()
    {
        $json = $this->files->get($this->getStub('addon/composer.json.stub'));

        $json = str_replace('DummyNamespace', str_replace('\\', '\\\\', $this->addonNamespace()), $json);
        $json = str_replace('dummy-slug', $this->addonSlug(), $json);
        $json = str_replace('DummyTitle', $this->addonTitle(), $json);

        $this->files->put($this->addonPath('composer.json'), $json);
    }

    /**
     * Generate service provider.
     */
    protected function generateServiceProvider()
    {
        $provider = $this->files->get($this->getStub('addon/provider.php.stub'));

        $provider = str_replace('DummyNamespace', $this->addonNamespace(), $provider);

        $this->files->put($this->addonPath('src/ServiceProvider.php'), $provider);
    }

    /**
     * Add repository path to app's composer.json file.
     */
    protected function addRepositoryPath()
    {
        $decoded = json_decode($this->files->get(base_path('composer.json')), true);

        $decoded['repositories'][] = [
            'type' => 'path',
            'url' => 'addons/' . $this->addonSlug(),
        ];

        $json = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $this->files->put(base_path('composer.json'), $json);
    }

    /**
     * Composer require addon.
     */
    protected function composerRequireAddon()
    {
        // TODO: run composer require
    }

    /**
     * Build absolute path for an addon or addon file, and ensure folder structure exists.
     *
     * @param string|null $file
     * @return string
     */
    protected function addonPath($file = null)
    {
        $path = config('statamic.system.addons_path') . '/' . $this->addonSlug();

        if ($file) {
            $path .= "/{$file}";
        }

        $this->makeDirectory($path);

        return $path;
    }

    /**
     * Build addon namespace.
     *
     * @return string
     */
    protected function addonNamespace()
    {
        return "Local\\{$this->addonName}";
    }

    /**
     * Get addon slug.
     *
     * @return string
     */
    protected function addonSlug()
    {
        return str_slug(snake_case($this->addonName));
    }

    /**
     * Get addon title.
     *
     * @return string
     */
    protected function addonTitle()
    {
        return str_replace('-', ' ', title_case($this->addonSlug()));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the addon'],
        ];
    }
}
