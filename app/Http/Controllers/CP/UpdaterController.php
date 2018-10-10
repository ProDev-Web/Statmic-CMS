<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Composer\Composer;
use Facades\Statamic\Composer\CoreChangelog;
use Facades\Statamic\Composer\CoreUpdater;
use Illuminate\Http\Request;
use Statamic\Statamic;

class UpdaterController extends CpController
{
    public function __construct()
    {
        // Temporarily using PackToTheFuture to fake version tags until we get this hooked up to statamic/cms.
        require(base_path('vendor/statamic/cms/tests/Fakes/Composer/Package/PackToTheFuture.php'));
    }

    public function index()
    {
        $this->access('updater');

        return view('statamic::updater.index', [
            'title' => 'Updates'
        ]);
    }

    public function count()
    {
        $this->access('updater');

        $count = 0;

        if (Statamic::version() != CoreUpdater::latestVersion()) {
            $count++;
        }

        // Todo: Increment for each addon that has updates as well.

        return $count;
    }

    public function changelog()
    {
        $this->access('updater');

        $currentVersion = Composer::installed()->get(Statamic::CORE_REPO)->version;

        return [
            'changelog' => CoreChangelog::get($currentVersion),
            'currentVersion' => $currentVersion,
            'lastInstallLog' => Composer::lastCachedOutput(Statamic::CORE_REPO),
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
