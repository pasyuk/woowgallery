/**
 * WoowGallery Skins Settings
 */

/**
 * You'll need to use CodeKit or similar, as this file is a placeholder to combine
 * the following JS files into __FILE__.min.js:
 */

// @codekit-prepend "settings-vue-fields.js";
// @codekit-prepend "settings-lightbox.js";

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
    mixins: woowgallery.mixins,
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
        this.defaults = this.setDefaults(this.schema);

        this.updatePresets(skin);
        if (-1 === this.presets.indexOf(this.preset)) {
          this.preset = 'default';
        }

        this.model = $.extend({}, this.defaults, window.woowgallery_skin[skin]['model'][this.preset]);

        this.fakeActivity(400);
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
      this.premium = woowgallery.status && ('premium' === woowgallery.status || 'trial' === woowgallery.status);
      this.default_skin = woowgallery.l10n.default_skin;

      let selected_skin;
      if (woowgallery.l10n.selected_skin || '' === woowgallery.l10n.selected_skin) {
        selected_skin = woowgallery.l10n.selected_skin;
      }
      else {
        selected_skin = woowgallery.l10n.default_skin;
      }
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

              this.updateSkinsListSetting();
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

              this.updateSkinsListSetting();
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

      // reset skin settings to default
      updateSkinsListSetting: function() {
        // WoowGallery Settings page.
        let skins_list = $('select.skins-presets-list');
        if (skins_list.length) {
          let options = '';
          $.each(window.woowgallery_skin, (skin, data) => {
            options += '<option value="' + skin + '">' + data.info.name + '</option>';
            $.each(data.model, (presetName, presetData) => {
              if ('default' === presetName) {
                return;
              }
              options += '<option value="' + skin + ': ' + presetName + '">' + data.info.name + ': ' + presetName + '</option>';
            });
          });

          skins_list.each((index, el) => {
            let select = $(el);
            let value = select.val();
            select.find('option').not('[value=""]').remove();
            select.append(options);
            if (select.find('[value="' + value + '"]').length) {
              select.val(value);
            }
            else {
              select.val(this.default_skin);
            }
          });
        }
      }

    }
  });

  woowgallery.Skin = config;

})(jQuery);
