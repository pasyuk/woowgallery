/**
 * WoowGallery Skins Settings
 */

/**
 * You'll need to use CodeKit or similar, as this file is a placeholder to combine
 * the following JS files into __FILE__.min.js:
 */

// @codekit-prepend "settings-vue-fields.js";

(function($) {
  let $skin_config = document.getElementById('woowgallery-skin-config');
  if (!$skin_config || !window.woowgallery_skin) {
    return;
  }

  let woowgallery = window.WoowGalleryAdmin;

  Vue.use(Toasted);

  let tick;
  let config = new Vue({
    el: $skin_config,
    components: woowgallery.vueFields,
    data: {
      options: {
        fieldNameTemplate: '_woowgallery_skin[{name}]'
      },
      premium: false,
      // chosen skin (slug)
      skin: '',
      default_skin: '',
      // chosen skin preset
      preset: 'default',
      presets: ['default'],
      skin_info: '',
      // skin settings
      model: {},
      // skin default settings
      defaults: {},
      // skin schema
      schema: {},
      activeTab: '',
      new_preset: false,
      new_preset_name: '',
      activity: false
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
          model2 = JSON.stringify($.extend({}, this.defaults, window.woowgallery_skin[this.skin]['model'][this.preset]));
        return model1 !== model2;
      }
    },
    watch: {
      skin: function(skin) {
        this.schema = $.extend({}, window.woowgallery_skin[skin]['schema']);
        this.skin_info = $.extend({}, window.woowgallery_skin[skin]['info']);
        this.activeTab = _.keys(this.schema)[0];
        this.defaults = setDefaults(this.schema);

        this.updatePresets(skin);
        if (-1 === this.presets.indexOf(this.preset)) {
          this.preset = 'default';
        }

        this.model = $.extend({}, this.defaults, window.woowgallery_skin[skin]['model'][this.preset]);

        this.fakeActivity(400);

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
      },
      preset: function(preset) {
        this.model = $.extend({}, this.defaults, window.woowgallery_skin[this.skin]['model'][preset]);
        this.fakeActivity(400);
      },
      new_preset_name: function(new_preset_name) {
        this.new_preset_name = new_preset_name.replace(/[&\/\\#,+()\[\]~%.'":;?<>{}^=|`]/g, '');
      }
    },
    mounted: function() {
      // On init get gallery skin and set all the data
      this.premium = !!woowgallery.premium;
      this.default_skin = woowgallery.l10n.default_skin;

      // let selected_skin = woowgallery.l10n.selected_skin || woowgallery.l10n.default_skin;
      let selected_skin = woowgallery.l10n.selected_skin;
      if (selected_skin) {
        let skin = selected_skin.split(': ');
        this.skin = skin[0];
        if (skin[1]) {
          this.preset = skin[1];
        }
        else {
          this.preset = 'default';
        }
      }
    },
    methods: {
      fakeActivity: function(time) {
        this.activity = true;
        if (tick) {
          clearTimeout(tick);
        }
        tick = setTimeout(() => {
          config.activity = false;
          tick = null;
        }, time);
      },
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

      loadPreset: function(event) {
        this.fakeActivity(120);
        this.model = $.extend({}, this.defaults, window.woowgallery_skin[this.skin]['model'][event.target.value]);
        event.target.value = '_custom';
      },

      updatePresets: function(skin) {
        this.presets = Object.keys(window.woowgallery_skin[skin]['model']);
        this.new_preset = false;
        this.new_preset_name = '';
      },

      // save skin data via AJAX
      saveSkinSettings: function() {
        this.activity = true;

        let skin = this.skin,
          preset = this.new_preset ? this.new_preset_name : this.preset,
          model = this.model,
          defaults = this.defaults,
          data = {
            action: 'woowgallery_save_skin_data',
            _nonce_woowgallery_skin_settings_save: woowgallery.l10n._nonce_woowgallery_skin_settings_save,
            skin: skin,
            preset: preset,
            data: JSON.stringify(model)
          };
        if ('default' === preset && this.isSettingsDefault) {
          data.default_reset = true;
        }

        if (!preset) {
          this.$toasted.error(woowgallery.l10n.fill_preset_name, {duration: 2000});
          this.activity = false;
          $('#woowskin_preset').focus();
          return;
        }

        // Post updated preset data.
        $.post(
          ajaxurl,
          data,
          (response) => {
            // Response should be a JSON success with the message
            if (response && response.success) {
              window.woowgallery_skin[skin]['model'][preset] = $.extend({}, defaults, model);
              this.updatePresets(skin);
              this.skin = skin;
              this.preset = preset;
              // Display some success message
              this.$toasted.success(response.data, {duration: 2000});

              // this.updateSkinsListSetting();
            }
            else if (response && response.data) {
              // Display some error here
              this.$toasted.error(response.data, {duration: 2000});
            }
            else {
              this.$toasted.error(':(', {duration: 2000});
            }
          },
          'json'
        ).always(() => {
          this.activity = false;
        });

      },

      // delete skin preset
      deletePreset: function() {
        this.activity = true;

        let skin = this.skin,
          preset = this.preset,
          data = {
            action: 'woowgallery_delete_skin_preset',
            _nonce_woowgallery_skin_settings_save: woowgallery.l10n._nonce_woowgallery_skin_settings_save,
            skin: skin,
            preset: preset
          };

        // let default_skin = this.default_skin.split(': '),
        //     is_default_preset = default_skin[1] === preset || 'default' === preset;
        let is_default_preset = 'default' === preset;
        if (is_default_preset) {
          this.$toasted.error(woowgallery.l10n.delete_default_preset_error, {duration: 2000});
          this.activity = false;
          return;
        }

        // Post updated gallery data.
        $.post(
          ajaxurl,
          data,
          (response) => {
            // Response should be a JSON success with the message
            if (response && response.success) {
              delete window.woowgallery_skin[skin]['model'][preset];
              this.updatePresets(skin);
              this.skin = skin;
              this.preset = 'default';
              // Display some success message
              this.$toasted.success(response.data, {duration: 2000});

              // this.updateSkinsListSetting();
            }
            else if (response && response.data) {
              // Display some error here
              this.$toasted.error(response.data, {duration: 2000});
            }
            else {
              this.$toasted.error(':(', {duration: 2000});
            }
          },
          'json'
        ).always(() => {
          this.activity = false;
        });

      },

      // reset skin settings changes
      resetSkinSettingsChanges: function() {
        this.model = $.extend({}, this.defaults, window.woowgallery_skin[this.skin]['model'][this.preset]);
      },

      // reset skin settings to default
      resetSkinSettings: function() {
        this.model = $.extend({}, this.defaults);
      },

      // // reset skin settings to default
      // updateSkinsListSetting: function() {
      //   // WoowBox Settings page.
      //   let default_skin = $('select#woowgallery-default-skin');
      //   if (default_skin.length) {
      //
      //     let options = '';
      //     $.each(window.woowgallery_skin, (skin, data) => {
      //       options += '<option value="' + skin + '">' + data.info.name + '</option>';
      //       $.each(data.model, (presetName, presetData) => {
      //         if ('default' === presetName) {
      //           return;
      //         }
      //         options += '<option value="' + skin + ': ' + presetName + '">' + data.info.name + ': ' + presetName + '</option>';
      //       });
      //     });
      //
      //     default_skin.find('option').not('[value=""]').remove();
      //     default_skin.append(options);
      //     default_skin.val(this.default_skin);
      //   }
      // }

    }
  });

  woowgallery.Skin = config;

})(jQuery);
