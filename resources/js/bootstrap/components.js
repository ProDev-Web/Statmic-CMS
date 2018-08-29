import Vue from 'vue'

// Core
Vue.component('asset-manager', require('../components/assets/AssetManager.vue'));
Vue.component('asset-browser', require('../components/assets/Browser/Browser.vue'));
Vue.component('asset-container-form', require('../components/assets/Container/EditForm.vue'));
Vue.component('asset-container-wizard', require('../components/assets/Container/Wizard/Wizard.vue'));

// Publish
Vue.component('publish-container', require('../components/publish/Container.vue'));
Vue.component('publish-fields', require('../components/publish/Fields.vue'));
Vue.component('publish-sections', require('../components/publish/Sections.vue'));

// Data List
Vue.component('data-list', require('../components/data-list/DataList.vue'));
Vue.component('data-list-search', require('../components/data-list/DataListSearch.vue'));
Vue.component('data-list-bulk-actions', require('../components/data-list/DataListBulkActions.vue'));
Vue.component('data-list-column-picker', require('../components/data-list/DataListColumnPicker.vue'));
Vue.component('data-list-toggle-all', require('../components/data-list/DataListToggleAll.vue'));
Vue.component('data-table', require('../components/data-list/DataTable.vue'));

// Resource Type Lists
Vue.component('entry-list', require('../components/data-list/EntryList.vue'));
Vue.component('collection-list', require('../components/data-list/CollectionList.vue'));

// Reusable
Vue.component('svg-icon', require('../components/SvgIcon.vue'));
Vue.component('file-icon', require('../components/FileIcon.vue'));
Vue.component('dropdown-list', require('../components/DropdownList'));

// Recursive from page tree
Vue.component('branch', require('../components/page-tree/Branch.vue'));
Vue.component('branches', require('../components/page-tree/Branches.vue'));

// Recursive from fieldset builder
// Vue.component('fields-builder', require('../components/fieldset-builder/fields-builder'));
// Vue.component('fieldset-fields', require('../components/fieldset-builder/Sections/Fields.vue'));
// Vue.component('fieldset-field', require('../components/fieldset-builder/Sections/Field.vue'));
Vue.component('fieldtype-selector', require('../components/fieldset-builder/FieldtypeSelector.vue'));

Vue.component('modal', require('../components/Modal.vue'));
Vue.component('modal-dialog', require('../components/ModalDialog.vue'));
Vue.component('pagination', require('../components/pagination/Pagination.vue'));
