<?php

namespace Statamic\Composer;

use Statamic\Jobs\RunComposer;

class Composer extends Process
{
    public $memoryLimit;

    /**
     * Instantiate composer process.
     *
     * @param mixed $basePath
     */
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);

        // Set this process to eleven.
        $this->toEleven();

        // Set memory limit for child process to eleven.
        $this->memoryLimit = config('statamic.system.php_memory_limit');
    }

    /**
     * List installed packages (including dev dependencies).
     *
     * @return \Illuminate\Support\Collection
     */
    public function installed()
    {
        return collect(json_decode($this->runComposerCommand('show', '--format=json'))->installed)
            ->keyBy('name')
            ->map(function ($package) {
                $package->version = $this->normalizeVersion($package->version);
                return $package;
            });
    }

    /**
     * Require a package.
     *
     * @param string $package
     * @param string|null $version
     */
    public function require(string $package, string $version = null)
    {
        $version
            ? $this->queueComposerCommand('require', $package, $version)
            : $this->queueComposerCommand('require', $package);
    }

    /**
     * Remove a package.
     *
     * @param string $package
     */
    public function remove(string $package)
    {
        $this->queueComposerCommand('remove', $package);
    }

    /**
     * Update a package.
     *
     * @param string $package
     */
    public function update(string $package)
    {
        $this->queueComposerCommand('update', $package);
    }

    /**
     * Run composer command.
     *
     * @param mixed $parts
     * @return mixed
     */
    private function runComposerCommand(...$parts)
    {
        return $this->run($this->prepareProcessArguments($parts));
    }

    /**
     * Queue composer command.
     *
     * @param mixed $parts
     */
    private function queueComposerCommand(...$parts)
    {
        dispatch(new RunComposer($this->prepareProcessArguments($parts)));
    }

    /**
     * Propare process arguments.
     *
     * @param array $parts
     * @return array
     */
    private function prepareProcessArguments($parts)
    {
        return array_merge([
            $this->phpBinary(),
            "-d memory_limit={$this->memoryLimit}",
            'vendor/bin/composer'
        ], $parts);
    }

    /**
     * Sometimes composer returns versions with a 'v', sometimes it doesn't.
     *
     * @param string $version
     * @return string
     */
    private function normalizeVersion(string $version)
    {
        return str_replace('v', '', $version);
    }
}
