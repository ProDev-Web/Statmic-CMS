<?php

namespace Statamic\Filesystem;

use Statamic\Support\Str;
use Statamic\Facades\Path;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;

class FilesystemAdapter extends AbstractAdapter
{
    protected $root;
    protected $filesystem;

    public function __construct(Filesystem $filesystem, $root)
    {
        $this->filesystem = $filesystem;
        $this->setRootDirectory($root);
    }

    public function setRootDirectory($directory)
    {
        $this->root = rtrim($directory, '/\\');

        return $this;
    }

    public function normalizePath($path)
    {
        // If given an absolute path, just tidy it (to adjust the slashes) and return it.
        // Except for a single slash, because that means "the root of the configured
        // filesystem", and not "the root of this entire computer".
        if ($path !== '/' && Path::isAbsolute($path)) {
            return Path::tidy($path);
        }

        $path = Path::tidy($this->root . '/' . $path);

        return $path;
    }

    public function isWithinRoot($path)
    {
        $path = $this->normalizePath($path);

        return Str::startsWith($path, Path::tidy($this->root));
    }

    protected function relativePath($path)
    {
        $root = Path::tidy($this->root);

        $path = Path::tidy(Str::removeLeft($path, $root));

        return ltrim($path, '/');
    }

    public function isDirectory($path)
    {
        return $this->filesystem->isDirectory($this->normalizePath($path));
    }

    public function getFiles($path, $recursive = false)
    {
        $method = $recursive ? 'allFiles' : 'files';

        if (! $this->exists($path)) {
            return $this->collection();
        }

        $files = $this->filesystem->$method($this->normalizePath($path), true);

        $inRoot = $this->isWithinRoot($path);

        return $this->collection($files)->map(function ($file) use ($inRoot) {
            $path = $file->getPathname();
            return $inRoot ? $this->relativePath($path) : $this->normalizePath($path);
        });
    }

    public function getFolders($path, $recursive = false)
    {
        $finder = Finder::create()
            ->in($this->normalizePath($path))
            ->depth($recursive ? '>=0' : 0)
            ->directories();

        return collect($finder)->map(function ($file) {
            return $this->relativePath($file->getPathname());
        })->values();
    }

    public function copyDirectory($src, $dest, $overwrite = false)
    {
        // todo: implement the overwrite argument

        return $this->filesystem->copyDirectory($this->normalizePath($src), $this->normalizePath($dest));
    }

    public function moveDirectory($src, $dest, $overwrite = false)
    {
        return $this->filesystem->moveDirectory($this->normalizePath($src), $this->normalizePath($dest), $overwrite);
    }
}
