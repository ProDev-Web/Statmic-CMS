<?php

namespace Statamic\Console\Commands;

use Exception;
use Statamic\API\Str;
use Facades\Statamic\Console\Processes\Composer;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\GeneratorCommand as IlluminateGeneratorCommand;

abstract class GeneratorCommand extends IlluminateGeneratorCommand
{
    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }

        $relativePath = $this->getRelativePath($this->getPath($this->qualifyClass($this->getNameInput())));

        $this->comment("Your {$this->type} class awaits at: {$relativePath}");
    }

    /**
     * Get the stub file for the generator.
     *
     * @param string|null $stub
     * @return string
     */
    protected function getStub($stub = null)
    {
        $stub = $stub ?? $this->stub;

        return __DIR__ . '/stubs/' . $stub;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return studly_case(parent::getNameInput());
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . str_plural($this->type);
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        $basePath = $this->laravel['path'];

        if ($addon = $this->argument('addon')) {
            $basePath = $this->getAddonPath($addon);
        }

        $path = $basePath.'/'.str_replace('\\', '/', $name).'.php';

        return $path;
    }

    /**
     * Get addon path.
     *
     * @param string $addon
     * @return string
     */
    protected function getAddonPath($addon)
    {
        $fallbackPath = $this->laravel['path'];

        // Attempt to get addon path.
        try {
            $path = Composer::installedPath($addon) . '/src';
        } catch (Exception $exception) {
            $path = $fallbackPath;
        }

        // Ensure we don't use addon path if within composer vendor files.
        if ($pathIsInVendor = str_contains($path, base_path('vendor'))) {
            $path = $fallbackPath;
        }

        // Output helpful errors to clarify why we're falling back to app path.
        if (! isset($this->shownAddonPathError) && $pathIsInVendor) {
            $this->error('It not a good practice to modify vendor files, falling back to default path.');
            $this->shownAddonPathError = true;
        } elseif (! isset($this->shownAddonPathError) && $path == $fallbackPath) {
            $this->error('Could not find path for specified addon, falling back to default path.');
            $this->shownAddonPathError = true;
        }

        return $path;
    }

    /**
     * Get path relative to the project if possible, otherwise return absolute path.
     *
     * @param string $path
     * @return string
     */
    protected function getRelativePath($path)
    {
        return str_replace(base_path().'/', '', $path);
    }

    /**
     * Get appropriate JS path for generating vue files, etc.
     *
     * @param string $file
     * @return string
     */
    protected function getJsPath($file)
    {
        $basePath = $this->laravel['path'];

        // If addon argument was specified, attempt to get addon as base path.
        if ($addon = $this->argument('addon')) {
            $basePath = $this->getAddonPath($addon);
        }

        // If base path is user's app and resources/assets/js exists from an older laravel installation, use it.
        // It's possible the user started with a <=5.6 app and shifted to 5.7+, but kept old structure,
        // So we will check actual structure, rather than laravel version.
        if ($basePath == $this->laravel['path'] && $this->files->exists(resource_path('assets/js'))) {
            $basePath = resource_path('assets/js');
        }

        // If the base path is user's app and resource/assets/js doesn't exist, use standard laravel js path.
        elseif ($basePath == $this->laravel['path']) {
            $basePath = resource_path('js');
        }

        // Otherwise, specify addon base path.
        else {
            $basePath = $basePath . '/resources/js';
        }

        return $basePath . Str::ensureLeft($file, '/');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['addon', InputArgument::OPTIONAL, 'The name of your addon'],
        ]);
    }
}
