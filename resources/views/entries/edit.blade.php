@extends('statamic::layout')

@section('content')

    <entry-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        collection-title="{{ $collection['title'] }}"
        collection-url="{{ $collection['url'] }}"
        initial-title="{{ $entry->get('title') }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-localizations="{{ json_encode($localizations) }}"
        initial-site="{{ $locale }}"
        :amp="{{ bool_str($entry->ampable()) }}"
    ></entry-publish-form>

@endsection
