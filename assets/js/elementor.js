(function($) {
  window.initWoowGallerySkin = function(id, skin) {
    const {WoowGallery} = window;
    if (WoowGallery.skins && WoowGallery.skins[skin]) {
      let galleriesManager = WoowGallery.skins[skin];
      if (galleriesManager.searchAndInit) {
        galleriesManager.searchAndInit(id);
      }
    }
    else {
      $.get(
        WoowGallery.ajaxurl,
        {
          action: 'woowgallery_skin_assets',
          skin: skin
        }
      ).done(function(response) {
        if (response && response.success) {
          if (!WoowGallery.skins) {
            WoowGallery.skins = {};
          }
          WoowGallery.skins[skin] = true;

          const assets = $(response.data);
          assets.filter('link').each(function(el) {
            if (!$('link[href="' + this.href + '"]').length) {
              $('head').append(this);
            }
          });
          assets.filter('script').each(function(el) {
            if (!$('script[src="' + this.src + '"]').length) {
              $('head').append(this);
            }
          });
        }

        $('#' + id).closest('.elementor-widget-wp-widget-woowgallery').removeClass('elementor-widget-empty').find('.elementor-widget-empty-icon').remove();
      });
    }
  };

})(jQuery);
