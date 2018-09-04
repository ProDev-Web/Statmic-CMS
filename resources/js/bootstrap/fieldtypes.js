import Vue from "vue";

Vue.component('select-input', require('../components/inputs/Select.vue'));
Vue.component('text-input', require('../components/inputs/Text.vue'));
Vue.component('textarea-input', require('../components/inputs/Textarea.vue'));

Vue.component('text-fieldtype', require('../components/fieldtypes/Text.vue'));
Vue.component('textarea-fieldtype', require('../components/fieldtypes/Textarea.vue'));
Vue.component('slug-fieldtype', require('../components/fieldtypes/Slug.vue'));

// Fieldtypes
import AssetsFieldtype from '../components/fieldtypes/assets/AssetsFieldtype.vue'
import AssetsFolderFieldtype from '../components/fieldtypes/AssetsFolderFieldtype.vue'
import AssetContainerFieldtype from '../components/fieldtypes/AssetContainerFieldtype.vue'
import BardFieldtype from '../components/fieldtypes/bard/BardFieldtype.vue'
import CollectionFieldtype from '../components/fieldtypes/CollectionFieldtype.vue'
import CollectionsFieldtype from '../components/fieldtypes/CollectionsFieldtype.vue'
import GridFieldtype from '../components/fieldtypes/GridFieldtype.vue'
import FieldsetFieldtype from '../components/fieldtypes/FieldsetFieldtype.vue'
import FormFieldtype from '../components/fieldtypes/FormFieldtype.vue'
import HiddenFieldtype from '../components/fieldtypes/HiddenFieldtype.vue'
import IntegerFieldtype from '../components/fieldtypes/IntegerFieldtype.vue'
import LocaleSettingsFieldtype from '../components/fieldtypes/LocaleSettingsFieldtype.vue'
import PagesFieldtype from '../components/fieldtypes/PagesFieldtype.vue'
import RedactorSettingsFieldtype from '../components/fieldtypes/redactor/RedactorSettingsFieldtype.vue'
import RelateFieldtype from '../components/fieldtypes/relate/RelateFieldtype.vue'
import ReplicatorFieldtype from '../components/fieldtypes/replicator/ReplicatorFieldtype.vue'
import RevealerFieldtype from '../components/fieldtypes/RevealerFieldtype.vue'
import RoutesFieldtype from '../components/fieldtypes/RoutesFieldtype.vue'
import SectionFieldtype from '../components/fieldtypes/SectionFieldtype.vue'
import StatusFieldtype from '../components/fieldtypes/StatusFieldtype.vue'
import SuggestFieldtype from '../components/fieldtypes/SuggestFieldtype.vue'
import TableFieldtype from '../components/fieldtypes/TableFieldtype.vue'
import TaxonomyFieldtype from '../components/fieldtypes/TaxonomyFieldtype.vue'
import TemplateFieldtype from '../components/fieldtypes/TemplateFieldtype.vue'
// import TextFieldtype from '../components/fieldtypes/TextFieldtype.vue'
// import TextareaFieldtype from '../components/fieldtypes/TextareaFieldtype.vue'
import ThemeFieldtype from '../components/fieldtypes/ThemeFieldtype.vue'
import UserGroupsFieldtype from '../components/fieldtypes/UserGroupsFieldtype.vue'
import UserRolesFieldtype from '../components/fieldtypes/UserRolesFieldtype.vue'
import VideoFieldtype from '../components/fieldtypes/VideoFieldtype.vue'
import UsersFieldtype from '../components/fieldtypes/UsersFieldtype.vue'

Vue.component('array-fieldtype', require('../components/fieldtypes/ArrayFieldtype.vue'));
Vue.component('assets-fieldtype', AssetsFieldtype);
Vue.component('asset_container-fieldtype', AssetContainerFieldtype);
Vue.component('asset_folder-fieldtype', AssetsFolderFieldtype);
Vue.component('bard-fieldtype', BardFieldtype);
Vue.component('checkboxes-fieldtype', require('../components/fieldtypes/CheckboxesFieldtype.vue'));
Vue.component('collection-fieldtype', CollectionFieldtype);
Vue.component('collections-fieldtype', CollectionsFieldtype);
Vue.component('date-fieldtype', require('../components/fieldtypes/DateFieldtype.vue'));
Vue.component('fieldset-fieldtype', FieldsetFieldtype);
Vue.component('form-fieldtype', FormFieldtype);
Vue.component('grid-fieldtype', GridFieldtype);
Vue.component('hidden-fieldtype', HiddenFieldtype);
Vue.component('integer-fieldtype', IntegerFieldtype);
Vue.component('list-fieldtype', require('../components/fieldtypes/ListFieldtype.vue'));
Vue.component('locale_settings-fieldtype', LocaleSettingsFieldtype);
Vue.component('markdown-fieldtype', require('../components/fieldtypes/MarkdownFieldtype.vue'));
Vue.component('pages-fieldtype', PagesFieldtype);
Vue.component('radio-fieldtype', require('../components/fieldtypes/RadioFieldtype.vue'));
Vue.component('redactor-fieldtype', require('../components/fieldtypes/redactor/RedactorFieldtype.vue'));
Vue.component('redactor_settings-fieldtype', RedactorSettingsFieldtype);
Vue.component('relate-fieldtype', RelateFieldtype);
Vue.component('replicator-fieldtype', ReplicatorFieldtype);
Vue.component('revealer-fieldtype', RevealerFieldtype);
Vue.component('routes-fieldtype', RoutesFieldtype);
Vue.component('section-fieldtype', SectionFieldtype);
Vue.component('select-fieldtype', require('../components/fieldtypes/SelectFieldtype.vue'));
Vue.component('status-fieldtype', StatusFieldtype);
Vue.component('suggest-fieldtype', SuggestFieldtype);
Vue.component('table-fieldtype', TableFieldtype);
Vue.component('tags-fieldtype', require('../components/fieldtypes/TagsFieldtype.vue'));
Vue.component('taxonomy-fieldtype', TaxonomyFieldtype);
Vue.component('template-fieldtype', TemplateFieldtype);
// Vue.component('text-fieldtype', TextFieldtype);
// Vue.component('textarea-fieldtype', TextareaFieldtype);
Vue.component('theme-fieldtype', ThemeFieldtype);
Vue.component('time-fieldtype', require('../components/fieldtypes/TimeFieldtype.vue'));
Vue.component('toggle-fieldtype', require('../components/fieldtypes/ToggleFieldtype.vue'));
Vue.component('users-fieldtype', UsersFieldtype);
Vue.component('user_groups-fieldtype', UserGroupsFieldtype);
Vue.component('user_roles-fieldtype', UserRolesFieldtype);
Vue.component('video-fieldtype', VideoFieldtype);
Vue.component('yaml-fieldtype', require('../components/fieldtypes/YamlFieldtype.vue'));
