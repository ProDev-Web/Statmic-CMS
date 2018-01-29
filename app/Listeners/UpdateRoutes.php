<?php

namespace Statamic\Listeners;

use Statamic\API\Config;
use Statamic\Events\Data\TaxonomyDeleted;
use Statamic\Events\Data\CollectionDeleted;

class UpdateRoutes
{
    /**
     * Register the listeners for the subscriber
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(CollectionDeleted::class, self::class.'@removeCollectionRoutes');
        $events->listen(TaxonomyDeleted::class, self::class.'@removeTaxonomyRoutes');
    }

    public function removeCollectionRoutes(CollectionDeleted $event)
    {
        $routes = collect(Config::get('statamic.routes.collections'))->except($event->collection)->all();

        Config::set('statamic.routes.collections', $routes);

        Config::save();
    }

    public function removeTaxonomyRoutes(TaxonomyDeleted $event)
    {
        $routes = collect(Config::get('statamic.routes.taxonomies'))->except($event->taxonomy)->all();

        Config::set('statamic.routes.taxonomies', $routes);

        Config::save();
    }
}
