<li class="{{ current_class($item->active()) }}">
    <a href="{{ $item->url() }}">
        <i>@svg($item->icon())</i><span>{{ __($item->name()) }}</span>
        <updates-badge class="ml-1" :initial-count="{{ Facades\Statamic\Updater\UpdatesOverview::count() }}"></updates-badge>
    </a>
</li>
