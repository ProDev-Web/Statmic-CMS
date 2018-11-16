class Fieldset {

    constructor(fieldset) {
        this.fieldset = fieldset;
        this.name = fieldset.name;
        this.sections = fieldset.sections;
        this.metaFields = [];
    }

    getFieldset() {
        let fieldset = this.fieldset;
        fieldset.sections = this.sections;
        return fieldset;
    }

    /**
     * By default, the slug field won't be shown.
     * This lets you specify whether or not it should be shown.
     */
    showSlug(show) {
        if (show) this.metaFields.push('slug');
        return this;
    }

    /**
     * By default, the date field won't be shown.
     * This lets you specify whether or not it should be shown.
     */
    showDate(show) {
        if (show) this.metaFields.push('date');
        return this;
    }

    /**
     * Place a title field at the beginning of the first section
     * if it hasn't been explicitly placed somewhere else.
     */
    prependTitle() {
        if (! this.fieldsInSections().includes('title')) {
            this.firstSectionFields().unshift({
                handle: 'title',
                type: 'text',
                instructions: null,
                width: 100
            });
        }

        return this;
    }

    /**
     * Prepend any required meta fields to the start of the sidebar.
     */
    prependMeta() {
        this.ensureSidebar();

        let fields = this.fieldsInSections();

        _.each(this.metaFields, field => {
            if (!fields.includes(field)) {
                this.pushSidebarField({ handle: field, type: field });
            }
        });

        this.removeEmptySidebar();

        return this;
    }

    /**
     * Push a field into the sidebar
     */
    pushSidebarField(config) {
        let sidebar = this.sidebarSectionFields();

        let field = Object.assign({
            width: 100,
            localizable: true
        }, config || {});

        sidebar.unshift(field);
    }

    /**
     * Customizing the sidebar is not a requirement, but we expect one to
     * exist. If it's not already defined, we'll create a blank one here.
     */
    ensureSidebar() {
        const sidebar = _.find(this.sections, { handle: 'sidebar' });

        if (! sidebar) {
            this.sections.push({ handle: 'sidebar', display: __('Meta'), fields: [] });
        }
    }

    /**
     * It's possible that all the fields that would normally be in the
     * sidebar have been placed in other sections, resulting in an
     * empty sidebar. If it's empty, we'll just get rid of it.
     */
    removeEmptySidebar() {
        if (this.sidebarSectionFields().length > 0) return;

        this.sections = _.reject(this.sections, section => section.handle == 'sidebar');
    }

    /**
     * Get the handles of fields that have been placed into a section.
     */
    fieldsInSections() {
        return _.chain(this.sections).map(section => section.fields).flatten().pluck('handle').value();
    }

    /**
     * Get the fields that are in the first section.
     */
    firstSectionFields() {
        return this.sections[0].fields;
    }

    /**
     * Get the fields that are in the sidebar.
     */
    sidebarSectionFields() {
        return _.find(this.sections, { handle: 'sidebar' }).fields;
    }

    /**
     * Get all the fields from all the sections.
     */
    fields() {
        return _.chain(this.sections).pluck('fields').flatten().value();
    }

}

export default Fieldset;
