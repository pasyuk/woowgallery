/**
 * Set WoowGallery global object
 */
window.wp = window.wp || {};
window.WoowGalleryAdmin = window.WoowGalleryAdmin || {l10n: {}};
window.WoowGalleryAdmin.mixins = window.WoowGalleryAdmin.mixins || [];
window.WoowGalleryAdmin.Gallery = window.WoowGalleryAdmin.Gallery || {};
window.WoowGalleryAdmin.Skin = window.WoowGalleryAdmin.Skin || {};

window.WoowGalleryAdmin.Hook = {
  hooks: {},

  register: function(hookName, action, callback, context) {
    let names = hookName.split(' '), curName;
    for (let i = 0; i < names.length; i++) {
      curName = names[i];
      if (!this.exists(curName)) {
        this.hooks[curName] = {};
      }
      this.hooks[curName][action] = {callback: callback, context: context};
    }
  },

  call: function(hookName, args, context) {
    if ('undefined' !== typeof (this.hooks[hookName])) {
      for (let key in this.hooks[hookName]) {
        // skip loop if the property is from prototype
        if (!this.hooks[hookName].hasOwnProperty(key)) {
          continue;
        }

        let action = this.hooks[hookName][key];
        if (!context) {
          context = action.context;
        }
        if (false === action.callback.apply(context, args)) {
          break;
        }
      }
    }
  },

  exists: function(hookName) {
    return 'undefined' !== typeof (this.hooks[hookName]);
  },

  remove: function(hookName, action) {
    if (this.exists(hookName)) {
      delete this.hooks[hookName][action];
    }
  }
};

