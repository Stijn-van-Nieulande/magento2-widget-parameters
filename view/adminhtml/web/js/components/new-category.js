define([
    'underscore',
    'Magento_Catalog/js/components/new-category'
], function (_, Category) {
    'use strict';

    function flattenCollection(array, separator, created) {
        let i = 0,
            length,
            childCollection;

        array = _.compact(array);
        length = array.length;
        created = created || [];

        for (i; i < length; i++) {
            created.push(array[i]);

            if (array[i].hasOwnProperty(separator)) {
                childCollection = array[i][separator];
                delete array[i][separator];
                flattenCollection.call(this, childCollection, separator, created);
            }
        }

        return created;
    }

    return Category.extend({
        /**
         * Set option to options array.
         *
         * @param {Object} option
         * @param {Array} options
         */
        setOption: function (option, options) {
            const parent = parseInt(option.parent);
            if (_.contains([0, 1], parent)) {
                options = options || this.cacheOptions.tree;
                options.push(option);

                const copyOptionsTree = JSON.parse(JSON.stringify(this.cacheOptions.tree));
                this.cacheOptions.plain = flattenCollection(copyOptionsTree, this.separator);
                this.options(this.cacheOptions.tree);
            } else {
                this._super(option, options);
            }
        },

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
            const isSelected = this.isSelected(data.value);

            if (this.lastSelectable && data.hasOwnProperty(this.separator)) {
                return this;
            }

            if (!this.multiple) {
                if (!isSelected) {
                    this.value(data.value);
                } else {
                    this.value(null);
                }
                this.listVisible(false);
            } else {
                if (!isSelected) { /*eslint no-lonely-if: 0*/
                    this.value.push(data.value);
                } else {
                    this.value(_.without(this.value(), data.value));
                }
            }

            return this;
        },
    });
});
