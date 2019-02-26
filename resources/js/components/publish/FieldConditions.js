const OPERATORS = ['==', '!=', '===', '!==', '>', '>=', '<', '<=', 'is', 'equals', 'not', 'includes', 'contains'];

class FieldConditionsValidator {
    constructor(field, values, store, storeName) {
        this.field = field;
        this.values = values;
        this.store = store;
        this.storeName = storeName;
        this.showOnPass = true;
    }

    passesConditions() {
        let conditions = this.getConditions();

        if (conditions === undefined) {
            return true;
        } else if (_.isString(conditions)) {
            return this.passesCustomLogicFunction(conditions);
        }

        let failedConditions = _.chain(conditions)
            .map((condition, field) => this.normalizeCondition(field, condition))
            .reject(condition => this.passesCondition(condition))
            .value();

        return this.showOnPass ? _.isEmpty(failedConditions) : ! _.isEmpty(failedConditions);
    }

    getConditions() {
        var conditions;

        if (conditions = this.field.if || this.field.show_when) {
            return conditions;
        }

        this.showOnPass = false;

        return this.field.unless || this.field.hide_when;
    }

    normalizeCondition(field, condition) {
        return {
            'lhs': this.normalizeConditionLhs(field),
            'operator': this.normalizeConditionOperator(condition),
            'rhs': this.normalizeConditionRhs(condition)
        };
    }

    normalizeConditionLhs(field) {
        let lhs = data_get(this.values, field, undefined);

        if (_.isString(lhs)) {
            lhs = JSON.stringify(lhs.trim());
        }

        return lhs;
    }

    normalizeConditionOperator(condition) {
        let operator = '==';

        OPERATORS.forEach(value => condition.toString().startsWith(value + ' ') ? operator = value : false);

        this.stringifyRhs = true;

        switch (operator) {
            case 'is':
            case 'equals':
                operator = '==';
                break;
            case 'not':
                operator = '!=';
                break;
            case 'includes':
            case 'contains':
                operator = 'includes';
                this.stringifyRhs = false;
                break;
        }

        return operator;
    }

    normalizeConditionRhs(condition) {
        let rhs = condition;

        OPERATORS.forEach(value => rhs = rhs.toString().replace(new RegExp(`^${value} `), ''));

        switch (rhs) {
            case 'null':
            case 'empty':
                rhs = null;
                break;
            case 'true':
                rhs = true;
                break;
            case 'false':
                rhs = false;
                break;
        }

        if (_.isString(rhs) && this.stringifyRhs) {
            rhs = JSON.stringify(rhs.trim());
        }

        return rhs;
    }

    passesCondition(condition) {
        if (condition.operator === 'includes') {
            return _.isObject(condition.lhs)
                ? condition.lhs.includes(condition.rhs)
                : condition.lhs.toString().includes(condition.rhs);
        }

        return eval(`${condition.lhs} ${condition.operator} ${condition.rhs}`);
    }

    passesCustomLogicFunction(functionName) {
        let customFunction = data_get(Statamic, 'conditions.' + functionName);

        let extra = {
            store: this.store,
            storeName: this.storeName,
            storeValues: this.store.state.publish[this.storeName].values
        }

        return this.showOnPass ? customFunction(this.values, extra) : ! customFunction(this.values, extra);
    }
}

// Export select methods for use as Vue mixin.
export default {
    methods: {
        showField(field) {
            let validator = new FieldConditionsValidator(field, this.values, this.$store, this.storeName);

            return validator.passesConditions();
        }
    }
}
