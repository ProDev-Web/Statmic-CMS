<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Composer\Composer;
use Facades\Statamic\Composer\CoreChangelog;
use Facades\Statamic\Composer\CoreUpdater;
use Illuminate\Http\Request;

class UpdaterController extends CpController
{
    const CORE = 'test/package';

    public function __construct()
    {
        // All temporary stuff to get this all hooked up with test/package instead of statamic/cms.
        $fakeCoreUpdater = new \Statamic\Composer\CoreUpdater;
        $fakeCoreUpdater->core = self::CORE;
        CoreUpdater::swap($fakeCoreUpdater);
        require(base_path('vendor/statamic/cms/tests/Fakes/Composer/Package/PackToTheFuture.php'));
    }

    public function index()
    {
        $this->access('updater');

        return view('statamic::updater.index', [
            'title' => 'Updates'
        ]);
    }

    public function changelog()
    {
        $this->access('updater');

        $currentVersion = Composer::installed()->get(self::CORE)->version;

        return [
            'changelog' => CoreChangelog::get($currentVersion),
            'currentVersion' => $currentVersion,
            'lastInstallLog' => Composer::lastCachedOutput(self::CORE),
        ];
    }

    public function update()
    {
        return CoreUpdater::update();
    }

    public function updateToLatest()
    {
        // Todo, fix composer output issue?
        // \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion(CoreUpdater::latestVersion()); // Temp!
        // return CoreUpdater::updateToLatest();

        \Illuminate\Support\Facades\Cache::forget('composer');
        \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion('2.10.5'); // Temp!

        return CoreUpdater::installExplicitVersion('2.10.5');
    }

    public function installExplicitVersion(Request $request)
    {
        \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion($request->version); // Temp!

        return CoreUpdater::installExplicitVersion($request->version);
    }
}
