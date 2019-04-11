<div class="card p-0 shadow-hover-lg rounded-lg">
    <div class="flex justify-between items-center p-2">
        <h2>{{ $title }}</h2>
        <a href="{{ $collection->createEntryUrl() }}" class="text-blue hover:text-blue-dark text-sm">{{ $button }}</a>
    </div>
    <collection-widget
        collection="{{ $collection->handle() }}"
        initial-sort-column="title"
        initial-sort-direction="asc"
        :initial-per-page="{{ $limit }}"
        :use-cancel-token="false"
    ></collection-widget>
</div>
