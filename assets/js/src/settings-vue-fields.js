/**
 * Vue Settings fields
 */

(function($) {

  let abstractField,
    components = {};

  abstractField = {
    props: [
      'skin',
      'schema',
      'id',
      'disabled',
      'options'
    ],
    data: function() {
      return {};
    },

    computed: {
      value: {
        get: function() {
          let val = null;
          if (!this.skin) {
            return;
          }
          if (typeof this.$root.model !== 'undefined' && this.id) {
            val = this.$root.model[this.id];
          }
          if (val === null) {
            val = this.schema.default;
          }
          return this.formatValueToField(val);
        },

        set: function(newValue) {
          if (!this.skin) {
            return;
          }
          newValue = this.formatValueToModel(newValue);
          if (this.id) {
            this.$root.model[this.id] = newValue;
            // this.$emit('model-updated', newValue, this.id);
          }
        }
      }
    },

    methods: {
      getFieldName: function() {
        let nameTemplate = this.options && this.options.fieldNameTemplate ? this.options.fieldNameTemplate : '{name}',
          name;
        if (this.schema.attr && this.schema.attr.name) {
          name = this.schema.attr.name;
        }
        name = name || this.schema.name || this.id;
        name = nameTemplate.replace('{name}', name);

        return name;
      },

      getFieldType: function(isInput) {
        let type;
        if (this.schema.attr && this.schema.attr.type) {
          type = this.schema.attr.type;
        }
        type = type || this.schema.type || 'text';

        if (isInput && 'color' === type) {
          return 'text';
        }
        return type;
      },

      getFieldID: function() {
        let prefix = this.options && this.options.fieldIdPrefix ? this.options.fieldIdPrefix : '',
          id;
        if (this.schema.attr && this.schema.attr.id) {
          id = this.schema.attr.id;
        }
        id = id || this.schema.id || this.id;

        return prefix + id;
      },

      getFieldClasses: function() {
        let classes = ['field-' + this.id];
        if (this.schema.attr && this.schema.attr.class) {
          if (_.isArray(this.schema.attr.class)) {
            _.union(classes, this.schema.attr.class);
          }
          else {
            classes.push(this.schema.attr.class);
          }
        }
        return classes;
      },

      getFieldAttributes: function() {
        let attrs = {},
          fieldType = this.getFieldType();
        if (fieldType && 'text' !== fieldType) {
          attrs['data-type'] = fieldType;
        }
        if (this.schema.attr) {
          attrs = $.extend(attrs, this.schema.attr);
        }
        if (this.schema.props) {
          let i = 0,
            length = this.schema.props.length,
            key;
          for (; i < length; i++) {
            key = this.schema.props[i];
            attrs[key] = true;
          }
        }
        return attrs;
      },

      formatValueToField: function(value) {
        return value;
      },

      formatValueToModel: function(value) {
        return value;
      }
    }
  };

  components['field-input'] = Vue.component('field-input',
    {
      mixins: [abstractField],
      template:
        `<div class="wrapper">
    <input class="form-control"
           :class="getFieldClasses()"
           :id="getFieldID()"
           :type="getFieldType(true)"
           :name="getFieldName()"
           v-model="value"
           v-bind="getFieldAttributes()"
    >
    <span class="helper" v-if="getFieldType() === 'color' || getFieldType() === 'range'">{{ value }}</span>
</div>`,
      data: function() {
        return {
          picker: null
        };
      },
      watch: {
        disabled: function(val) {
          if ('color' === this.getFieldType() && $.fn.spectrum) {
            if (val) {
              this.picker.spectrum('disable');
            }
            else {
              this.picker.spectrum('enable');
            }
          }
        }
      },
      mounted: function() {
        this.$nextTick(function() {
          if ('color' === this.getFieldType()) {
            if ($.fn.spectrum) {
              this.spectrumInit();
            }
            else {
              console.warn('Spectrum color library is missing.');
            }
          }
        });
      },
      updated: function() {
        this.$nextTick(function() {
          if ('color' === this.getFieldType() && $.fn.spectrum && this.picker) {
            this.picker.spectrum('set', this.value);
          }
        });
      },
      beforeDestroy: function() {
        if (this.picker) {
          this.picker.spectrum('destroy');
        }
      },
      methods: {
        spectrumInit: function() {
          let vm = this;
          if (this.picker) {
            this.picker.spectrum('destroy');
          }
          this.picker = $('input[data-type="color"]', this.$el).spectrum(
            _.defaults(
              this.schema.options || {},
              {
                color: this.value,
                showInput: true,
                showAlpha: true,
                disabled: this.disabled,
                allowEmpty: false,
                preferredFormat: 'rgb',
                change: function(color) {
                  vm.value = color ? color.toString() : null;
                }
              }
            )
          );
        },
        formatValueToModel: function(value) {
          if (value !== null) {
            let type = this.getFieldType();
            switch (type) {
              case 'number':
                return Number(value);
              case 'range':
                return Number(value);
            }
          }

          return value;
        }
      }
    });

  components['field-checkbox'] = Vue.component('field-checkbox',
    {
      mixins: [abstractField],
      template:
        `<div class="wrapper">
    <span class="wg-toggle" :class="{'is-checked': !!value}">
        <input type="hidden" :name="getFieldName()" value="0" />
        <input type="checkbox" :id="getFieldID()" :name="getFieldName()" :class="getFieldClasses()" v-bind="getFieldAttributes()" value="1" v-model="value" true-value="1" false-value="0">
        <span class="wg-toggle__track"></span>
        <span class="wg-toggle__thumb"></span>
    </span>
</div>`,
      methods: {
        formatValueToField: function(value) {
          return Number(value);
        },

        formatValueToModel: function(value) {
          if (value !== null) {
            return Number(value);
          }

          return value;
        }
      }
    });

  components['field-select'] = Vue.component('field-select',
    {
      mixins: [abstractField],
      template:
        `<div class="wrapper">
    <select class="form-control"
           v-model="value"
           :class="getFieldClasses()"
           :id="getFieldID()"
           :name="getFieldName()"
           v-bind="getFieldAttributes()"
    >
         <option v-for="item in items" :value="getItemValue(item)" :disabled="isItemDisabled(item)">{{ getItemName(item) }}</option>
    </select>
</div>`,
      data: function() {
        return {
          items: this.schema.options
        };
      },
      methods: {
        getItemValue: function(item) {
          if (_.isObject(item)) {
            if (typeof item['value'] !== 'undefined') {
              return item.value;
            }
            else {
              throw '`value` is not defined';
            }
          }
          else {
            return item;
          }
        },
        getItemName: function(item) {
          if (_.isObject(item)) {
            if (typeof item['name'] !== 'undefined') {
              return item.name;
            }
            else {
              throw '`name` is not defined';
            }
          }
          else {
            return item;
          }
        },
        isItemDisabled: function(item) {
          if (_.isObject(item)) {
            if (typeof item['premium'] !== 'undefined') {
              return item.premium && !config.premium;
            }
            else {
              return false;
            }
          }
          else {
            return false;
          }
        }
      }
    });

  window.WoowGalleryAdmin.vueFields = components;

})(jQuery);
