<div class="global-header">
    <div class="lg:w-56 pl-1 md:pl-3 h-full flex items-center">
        <button class="nav-toggle ml-sm flex-no-shrink" @click="toggleNav">@svg('burger')</button>
        <a href="{{ route('statamic.cp.index') }}" class="flex items-end">
            <div v-tooltip="version" class="hidden md:block flex-no-shrink">
                @svg('statamic-wordmark')
            </div>
        </a>
    </div>

    <div class="sm:px-4 w-full flex-1 mx-auto" :class="wrapperClass">
        <global-search endpoint="{{ cp_route('search') }}" placeholder="{{ __('Search...') }}">
        </global-search>
    </div>

    <div class="lg:absolute pin-t pin-r head-link h-full md:pr-3 flex items-center">

        @if (Statamic\Facades\Site::hasMultiple())
            <site-selector>
                <template slot="icon">@svg('sites')</template>
            </site-selector>
        @endif

        <favorite-creator class="hidden md:block"></favorite-creator>

        @if (config('telescope.enabled'))
            <a class="hidden md:block h-6 w-6 p-sm text-grey ml-2 hover:text-grey-80" href="/{{ config('telescope.path') }}" target="_blank" v-tooltip="'Laravel Telescope'">
                @svg('telescope')
            </a>
        @endif
        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <a class="hidden md:block h-6 w-6 ml-2 p-sm text-grey hover:text-grey-80" v-tooltip="__('Useful Links')">
                    @svg('book-open')
                </a>
            </template>

            <dropdown-item external-link="https://statamic.dev" class="flex items-center">
                <span>{{__('Documentation')}}</span>
                <i class="w-3 block ml-1">@svg('external-link')</i>
            </dropdown-item>

            <dropdown-item external-link="https://statamic.com/forum" class="flex items-center">
                <span>{{__('Support')}}</span>
                <i class="w-3 block ml-1">@svg('external-link')</i>
            </dropdown-item>

            <dropdown-item @click="$events.$emit('keyboard-shortcuts.open')" class="flex items-center">
                <span>{{__('Keyboard Shortcuts')}}</span>
            </dropdown-item>
        </dropdown-list>

        <a class="hidden md:block h-6 w-6 p-sm text-grey ml-2 hover:text-grey-80" href="{{ route('statamic.site') }}" target="_blank" v-tooltip="'{{ __('View Site') }}'">
            @svg('browser-com')
        </a>
        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <a class="dropdown-toggle items-center ml-2 h-full hide flex">
                    @if ($user->avatar())
                        <div class="icon-header-avatar"><img src="{{ $user->avatar() }}" /></div>
                    @else
                        <div class="icon-header-avatar icon-user-initials">{{ $user->initials() }}</div>
                    @endif
                </a>
            </template>

            <div class="px-1">
                <div class="text-base mb-px">{{ $user->email() }}</div>
                @if ($user->isSuper())
                    <div class="text-2xs mt-px text-grey-60">{{ __('Super Admin') }}</div>
                @endif
            </div>
            <div class="divider"></div>

            <dropdown-item :text="__('Profile')" redirect="{{ route('statamic.cp.account') }}"></dropdown-item>
            <dropdown-item :text="__('Log out')" redirect="{{ route('statamic.cp.logout') }}"></dropdown-item>
        </dropdown-list>
    </div>
</div>
