@extends('statamic::layout')
@section('title', __('Licensing'))

@section('content')

    @if ($requestError)

        <div class="no-results md:pt-8 max-w-2xl mx-auto">
            <div class="flex flex-wrap items-center">
                <div class="w-full md:w-1/2">
                    <h1 class="mb-4">Licensing</h1>
                    <p class="text-grey-70 leading-normal mb-4 text-lg antialiased">
                        There was an issue communicating with statamic.com. Please try again later.
                    </p>
                    <a href="{{ cp_route('licensing.refresh') }}" class="btn-primary btn-lg">{{ __('Try again') }}</a>
                </div>
                <div class="hidden md:block w-1/2 pl-6">
                    @svg('empty/navigation')
                </div>
            </div>
        </div>

    @else

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Licensing') }}</h1>
        </div>

        <h6 class="mt-4">Site</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span class="little-dot {{ $site->valid() ? 'bg-green' : 'bg-red' }} mr-1"></span>
                        {{ $site->key() ?? __('No license key') }}
                    </td>
                    <td class="relative">
                        {{ $site->domain()['url'] ?? '' }}
                        @if ($site->hasMultipleDomains())
                            <span class="text-2xs">(and {{ $site->additionalDomainCount() }} others)</span>
                        @endif
                    </td>
                    <td class="text-right text-red">{{ $site->invalidReason() }}</td>
                </tr>
            </table>
        </div>

        <h6 class="mt-4">Core</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span class="little-dot {{ $statamic->valid() ? 'bg-green' : 'bg-red' }} mr-1"></span>
                        Statamic @if ($statamic->pro())<span class="text-pink">Pro</span>@else Free @endif
                    </td>
                    <td>{{ $statamic->version() }}</td>
                    <td class="text-right text-red">{{ $statamic->invalidReason() }}</td>
                </tr>
            </table>
        </div>

        <h6 class="mt-4">Addons</h6>
        @empty($addons)
        <p class="text-sm text-grey mt-1">No addons installed</p>
        @else
        <div class="card p-0 mt-1">
            <table class="data-table">
                @foreach ($addons as $addon)
                    <tr>
                        <td class="w-64 font-bold mr-1">
                            <span class="little-dot {{ $addon->valid() ? 'bg-green' : 'bg-red' }} mr-1"></span>
                            {{ $addon->name() }}
                        </td>
                        <td>{{ $addon->version() }}</td>
                        <td class="text-right text-red">{{ $addon->invalidReason() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        @endempty

        @if (!empty($unlistedAddons))
        <h6 class="mt-4">Unlisted Addons</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                @foreach ($unlistedAddons as $addon)
                    <tr>
                        <td class="w-64 font-bold mr-1">
                            <span class="little-dot bg-green mr-1"></span>
                            {{ $addon->name() }}
                        </td>
                        <td>{{ $addon->version() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        @endif

        <div class="mt-5 py-2 border-t flex items-center">
            <a href="{{ $site->url() }}" target="_blank" class="btn btn-primary mr-2">{{ __('Edit Site') }}</a>
            <a href="{{ cp_route('licensing.refresh') }}" class="btn">{{ __('Refresh') }}</a>
            <p class="ml-2 text-2xs text-grey">Data from statamic.com is synced once per hour. Refresh to see any changes you've made.</p>
        </div>

    @endif

@stop
