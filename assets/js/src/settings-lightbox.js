/**
 * WoowGallery Lightbox Settings
 */

(function($) {
  let $lightbox_config = document.getElementById('woowgallery-lightbox-config');
  if (!$lightbox_config || !window.woowgallery_lightbox) {
    return;
  }

  let woowgallery = window.WoowGalleryAdmin;

  let config = new Vue({
    el: $lightbox_config,
    components: woowgallery.vueFields,
    data: {
      options: {
        fieldNameTemplate: '_woowgallery_lightbox[{name}]'
      },
      premium: false,
      lightbox: '',
      // lightbox settings
      model: {},
      // lightbox default settings
      defaults: {},
      // lightbox schema
      schema: {},
      activeTab: ''
    },
    computed: {
      isSettingsDefault: function() {
        let clean_model = _.pick(this.model, (val, key, obj) => {
          return this.defaults.hasOwnProperty(key);
        });

        return (JSON.stringify(clean_model) === JSON.stringify(this.defaults));
      },
      isSettingsChanged: function() {
        let activity = this.activity,
          model1 = JSON.stringify(this.model),
          model2 = JSON.stringify($.extend({}, this.defaults, window.woowgallery_lightbox[this.lightbox]['model']));
        return model1 !== model2;
      }
    },
    watch: {
      lightbox: function(lightbox) {
        if (!lightbox) {
          this.schema = {};
          this.activeTab = '';
          this.defaults = {};
          this.model = {};
          return;
        }
        this.schema = $.extend({}, window.woowgallery_lightbox[lightbox]['schema']);
        this.activeTab = _.keys(this.schema)[0];
        this.defaults = setDefaults(this.schema);
        this.model = $.extend({}, this.defaults, window.woowgallery_lightbox[lightbox]['model']);

        function setDefaults(obj, def_obj) {
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
        }
      }
    },
    mounted: function() {
      // On init get gallery skin and set all the data
      this.premium = woowgallery.status && ('premium' === woowgallery.status || 'trial' === woowgallery.status);
      this.lightbox = woowgallery.l10n.selected_lightbox || woowgallery.l10n.default_lightbox;
    },
    methods: {
      switchTab: function(tab_id) {
        this.activeTab = tab_id;
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
      },

      // reset settings changes
      resetLightboxSettingsChanges: function() {
        this.model = $.extend({}, this.defaults, window.woowgallery_lightbox[this.skin]['model']);
      },

      // reset skin settings to default
      resetLightboxSettings: function() {
        this.model = $.extend({}, this.defaults);
      }
    }
  });

  woowgallery.Lightbox = config;

})(jQuery);
