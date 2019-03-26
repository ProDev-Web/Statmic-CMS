<!doctype html>
{{-- TODO: Set lang with current translation --}}
<html lang="en">
<head>
    @include('statamic::partials.head')
</head>

<body>
    <div id="statamic" :style="{ marginRight: pane ? `24rem` : null }">

      <session-expiry
          email="{{ my()->email() }}"
          :warn-at="60"
          :lifetime="{{ config('session.lifetime') * 60 }}"
      ></session-expiry>

      @include('statamic::partials.global-header')
      @include('statamic::partials.alerts')

      <div id="main" class="@yield('content-class')" :class="{'nav-closed': ! computedNavOpen}">
            @include('statamic::partials.nav-main')

            <div class="workspace">
                  <div class="page-wrapper">
                        @yield('content')
                  </div>
            </div>

            <portal to="modals" v-if="showLoginModal">
                <login-modal
                      email="{{ \Statamic\API\User::getCurrent()->email() }}"
                      @closed="showLoginModal = false"
                ></login-modal>
            </portal>

            <keyboard-shortcuts-modal></keyboard-shortcuts-modal>

            <tooltip :pointer="true"></tooltip>

            <vue-toast ref="toast"></vue-toast>

            <portal-target name="live-preview"></portal-target>

            <stacks v-if="stacks.length"></stacks>

            <portal-target
                  v-for="(modal, i) in modals"
                  :key="`modal-${modal}-${i}`"
                  :name="`modal-${i}`"
            ></portal-target>

            <portal-target name="pane" :slim="true"></portal-target>

            <portal-target name="outside"></portal-target>
      </div>

      @include('statamic::partials.nav-mobile')

  </div>

@include('statamic::partials.scripts')
@yield('scripts')

</body>
</html>
