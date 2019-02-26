import Vue from 'vue';
import Vuex from 'vuex';
import FieldConditions from '../components/publish/FieldConditions.js';
require('../bootstrap/globals');
global._ = require('underscore');
Vue.use(Vuex);

const Store = new Vuex.Store({
    state: {publish: {base: {values: {}}}},
    mutations: {
        setValues(state, values) {
            this.state.publish.base.values = values;
        }
    }
});

const Fields = new Vue({
    mixins: [FieldConditions],
    store: Store
});

let showFieldIf = function (conditions=null) {
    return Fields.showField(conditions ? {'if': conditions} : {});
};

afterEach(() => {
    Store.commit('setValues', {});
});

test('it shows field by default', () => {
    expect(showFieldIf()).toBe(true);
});

test('it shows or hides field based on shorthand equals conditions', () => {
    Store.commit('setValues', {first_name: 'Jesse'});

    expect(showFieldIf({first_name: 'Jesse'})).toBe(true);
    expect(showFieldIf({first_name: 'Jack'})).toBe(false);
});

test('it can use comparison operators in conditions', () => {
    Store.commit('setValues', {age: 13});

    expect(showFieldIf({age: '== 13'})).toBe(true);
    expect(showFieldIf({age: '!= 5'})).toBe(true);
    expect(showFieldIf({age: '=== 13'})).toBe(false); // Fails because we don't cast their condition to int
    expect(showFieldIf({age: '!== 13'})).toBe(true);

    expect(showFieldIf({age: '> 5'})).toBe(true);
    expect(showFieldIf({age: '> 13'})).toBe(false);
    expect(showFieldIf({age: '> 20'})).toBe(false);
    expect(showFieldIf({age: '>= 13'})).toBe(true);

    expect(showFieldIf({age: '< 5'})).toBe(false);
    expect(showFieldIf({age: '< 13'})).toBe(false);
    expect(showFieldIf({age: '< 20'})).toBe(true);
    expect(showFieldIf({age: '<= 13'})).toBe(true);

    expect(showFieldIf({age: 'is 13'})).toBe(true);
    expect(showFieldIf({age: 'equals 13'})).toBe(true);
    expect(showFieldIf({age: 'not 13'})).toBe(false);
});

test('it can use includes or contains operators in conditions', () => {
    Store.commit('setValues', {
        cancellation_reasons: [
            'found another service',
            'other'
        ],
        example_string: 'The quick brown fox jumps over the lazy dog',
        age: 13,
    });

    expect(showFieldIf({cancellation_reasons: 'includes other'})).toBe(true);
    expect(showFieldIf({cancellation_reasons: 'contains other'})).toBe(true);
    expect(showFieldIf({cancellation_reasons: 'includes slow service'})).toBe(false);
    expect(showFieldIf({cancellation_reasons: 'contains slow service'})).toBe(false);

    expect(showFieldIf({example_string: 'includes fox jumps'})).toBe(true);
    expect(showFieldIf({example_string: 'contains fox jumps'})).toBe(true);
    expect(showFieldIf({example_string: 'includes dog jumps'})).toBe(false);
    expect(showFieldIf({example_string: 'contains dog jumps'})).toBe(false);

    expect(showFieldIf({age: 'includes 13'})).toBe(true);
    expect(showFieldIf({age: 'contains 13'})).toBe(true);
    expect(showFieldIf({age: 'includes fox'})).toBe(false);
    expect(showFieldIf({age: 'contains fox'})).toBe(false);
});

test('it handles null, empty, true, and false in condition as literal', () => {
    Fields.setValues({
        last_name: 'HasselHoff',
        likes_food: true,
        likes_animals: false,
        not_real_boolean: 'false'
    });

    expect(showFieldIf({first_name: '=== null'})).toBe(true);
    expect(showFieldIf({first_name: '=== empty'})).toBe(true);
    expect(showFieldIf({last_name: '!== null'})).toBe(true);
    expect(showFieldIf({last_name: '!== empty'})).toBe(true);
    expect(showFieldIf({likes_food: '=== true'})).toBe(true);
    expect(showFieldIf({likes_animals: '=== false'})).toBe(true);
    expect(showFieldIf({not_real_boolean: '=== false'})).toBe(false);
});

test('it can use operators with multi-word values', () => {
    Store.commit('setValues', {ace_ventura_says: 'Allllllrighty then!'});

    expect(showFieldIf({ace_ventura_says: 'Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: '== Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: 'is Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: 'not I am your father'})).toBe(true);
});

test('it only shows when multiple conditions are met', () => {
    Store.commit('setValues', {
        first_name: 'San',
        last_name: 'Holo',
        age: 22
    });

    expect(showFieldIf({first_name: 'is San', last_name: 'is Holo', age: '!= 20'})).toBe(true);
    expect(showFieldIf({first_name: 'is San', last_name: 'is Holo', age: '> 40'})).toBe(false);
});

test('it shows or hides with parent key variants', () => {
    Store.commit('setValues', {
        first_name: 'Rincess',
        last_name: 'Pleia'
    });

    expect(Fields.showField({if: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({if: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);

    expect(Fields.showField({show_when: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({show_when: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);

    expect(Fields.showField({unless: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(false);
    expect(Fields.showField({unless: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(true);

    expect(Fields.showField({hide_when: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(false);
    expect(Fields.showField({hide_when: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(true);
});

test('it can run conditions on nested data', () => {
    Store.commit('setValues', {
        user: {
            address: {
                country: 'Canada'
            }
        }
    });

    expect(showFieldIf({'user.address.country': 'Canada'})).toBe(true);
    expect(showFieldIf({'user.address.country': 'Australia'})).toBe(false);
});

// TODO: Implement wildcards using asterisks...
// test('it can run conditions on nested data using wildcards', () => {
//     Store.commit('setValues', {
//         related_posts: [
//             {title: 'Learning Laravel', slug: 'learning-laravel'},
//             {title: 'Learning Vue', slug: 'learning-vue'},
//         ]
//     });

//     expect(showFieldIf({'related_posts.*.title': 'Learning Vue'})).toBe(true);
//     expect(showFieldIf({'related_posts.*.title': 'Learning Vim'})).toBe(false);
// });

