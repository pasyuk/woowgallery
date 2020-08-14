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
    mixins: woowgallery.mixins,
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
        let model1 = JSON.stringify(this.model),
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
        this.defaults = this.setDefaults(this.schema);
        this.model = $.extend({}, this.defaults, window.woowgallery_lightbox[lightbox]['model']);
      }
    },
    mounted: function() {
      // On init get gallery skin and set all the data
      this.premium = woowgallery.status && ('premium' === woowgallery.status || 'trial' === woowgallery.status);
      if (woowgallery.l10n.selected_lightbox || '' === woowgallery.l10n.selected_lightbox) {
        this.lightbox = woowgallery.l10n.selected_lightbox;
      }
      else {
        this.lightbox = woowgallery.l10n.default_lightbox;
      }
    },
    methods: {
      switchTab: function(tab_id) {
        this.activeTab = tab_id;
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
