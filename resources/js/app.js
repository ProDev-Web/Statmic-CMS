import Vue from 'vue';
import Notifications from './mixins/Notifications.js';
import axios from 'axios';
import PortalVue from "portal-vue";
import VModal from "vue-js-modal";
import Vuex from 'vuex';
import StatamicStore from './store';
import Popover  from 'vue-js-popover'


axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('#csrf-token').getAttribute('value');

Vue.prototype.axios = axios;
Vue.prototype.$mousetrap = require('mousetrap');
Vue.prototype.$eventHub = new Vue(); // Global event bus

Vue.config.productionTip = false

Vue.use(Popover, { tooltip: true })
Vue.use(PortalVue)
Vue.use(VModal)
Vue.use(Vuex);

// Vue.http.interceptors.push({
//     response: function (response) {
//         if (response.status === 401) {
//             this.$root.showLoginModal = true;
//         }
//
//         return response;
//     }
// });

require('./components/NotificationBus');

var vm = new Vue({
    el: '#statamic',

    store: new Vuex.Store({
        modules: {
            statamic: StatamicStore,
            publish: {
                namespaced: true
            }
        }
    }),

    components: {
        GlobalSearch: require('./components/GlobalSearch.vue'),
        PageTree: require('./components/page-tree/PageTree.vue'),
        Login: require('./components/login/login'),
        LoginModal: require('./components/login/LoginModal.vue'),
        EntryPublishForm: require('./components/publish/forms/EntryPublishForm.vue'),
        FormsetBuilder: require('./components/formset-builder/formset-builder'),
        FieldsetBuilder: require('./components/fieldset-builder/Builder.vue'),
        Installer: require('./components/installer/installer'),
        Updater: require('./components/updater'),
        Importer: require('./components/importer/importer'),
        Roles: require('./components/roles/roles'),
    },

    mixins: [ Notifications ],

    data: {
        version: Statamic.version,
        showLoginModal: false,
    },

    mounted() {
        this.bindWindowResizeListener();
    },

    methods: {

        bindWindowResizeListener() {
            window.addEventListener('resize', () => {
                this.$store.commit('statamic/windowWidth', document.documentElement.clientWidth);
            });
            window.dispatchEvent(new Event('resize'));
        }

    }

});

// TODO: Drag events
// TODO: Live Preview
