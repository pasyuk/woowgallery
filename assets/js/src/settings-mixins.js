/**
 * Settings mixins
 */
(function($, _) {

  let woowgallery = window.WoowGalleryAdmin;

  woowgallery.mixins.push({
    methods: {
      setDefaults: function(obj, def_obj) {
        const setDefaults = (obj, def_obj) => {
          def_obj = def_obj || {};
          $.each(obj, function(key, val) {
            if (typeof val !== 'object') {
              return;
            }
            if (typeof val['default'] !== 'undefined') {
              def_obj[key] = val['default'];
            }
            else {
              setDefaults(val, def_obj);
            }
          });
          return def_obj;
        };

        return setDefaults(obj, def_obj);
      },

      // Get style classes of field
      getFieldRowClasses: function(field) {
        let baseClasses = {
          disabled: this.fieldDisabled(field),
          readonly: this.fieldReadonly(field),
          required: this.fieldRequired(field),
          'premium-field': this.fieldPremium(field)
        };

        if (_.isArray(field.styleClasses)) {
          _.each(field.styleClasses, (c) => {
            baseClasses[c] = true;
          });
        }
        else if (_.isString(field.styleClasses)) {
          baseClasses[field.styleClasses] = true;
        }

        baseClasses['field-' + field.tag] = true;

        return baseClasses;
      },

      // Get style classes of field
      getFieldRowStyles: function(field) {
        let styles = {};
        if (_.isObject(field.styles)) {
          styles = field.styles;
        }

        return styles;
      },

      // Should field type have a label?
      fieldTypeHasLabel: function(field) {
        let relevantType = field.type || field.tag;
        if (field.attr && field.attr.type) {
          relevantType = field.attr.type;
        }
        switch (relevantType) {
          case 'button':
          case 'submit':
          case 'reset':
            return false;
          default:
            return true;
        }
      },

      // Get disabled attr of field
      fieldDisabled: function(field) {
        if (!field.prop || !field.prop.disabled) {
          return false;
        }

        return field.prop.disabled;
      },

      // Get required prop of field
      fieldRequired: function(field) {
        if (!field.prop || !field.prop.required) {
          return false;
        }

        return field.prop.required;
      },

      // Get premium prop of field
      fieldPremium: function(field) {
        return !!field.premium;
      },

      // Get visible prop of field
      fieldVisible: function(field) {
        if (!field.visible) {
          return true;
        }

        let filter,
          visible;
        try {
          filter = compileExpression(field.visible);
          visible = filter(this.model);
        } catch (e) {
          visible = true;
        }

        return visible;
      },

      // Get readonly prop of field
      fieldReadonly: function(field) {
        if (!field.prop || !field.prop.readonly) {
          return false;
        }

        return field.prop.readonly;
      },

      // Get current hint.
      fieldHint: function(field) {
        return field.hint;
      },

      // Get type of field 'field-xxx'. It'll be the name of HTML element
      getFieldTagType: function(field) {
        return 'field-' + field.tag;
      }

    }
  });

})(jQuery, _);
