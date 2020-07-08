window.initWoowGallerySkin = function(id, skin, lightbox) {
  const {WoowGallery} = window;

  if (WoowGallery.skins && WoowGallery.skins[skin]) {
    let galleriesManager = WoowGallery.skins[skin];
    if (galleriesManager.searchAndInit) {
      galleriesManager.searchAndInit(id);
    }
    else {
      setTimeout(() => {
        initWoowGallerySkin(id, skin, lightbox);
      }, 500);
      return;
    }
  }
  else {
    if (!WoowGallery.skins) {
      WoowGallery.skins = {};
    }
    WoowGallery.skins[skin] = true;

    if (lightbox) {
      if (!WoowGallery.lightboxes) {
        WoowGallery.lightboxes = {};
      }
      WoowGallery.lightboxes[lightbox] = true;
    }

    jQuery.get(
      WoowGallery.ajaxurl,
      {
        action: 'woowgallery_skin_assets',
        skin: skin,
        lightbox: lightbox
      }
    ).done(function(response) {
      if (response && response.success) {
        const assets = jQuery(response.data);
        assets.filter('link').each(function(el) {
          if (!jQuery('link[href="' + this.href + '"]').length) {
            jQuery('head').append(this);
          }
        });
        assets.filter('script').each(function(el) {
          if (!jQuery('script[src="' + this.src + '"]').length) {
            jQuery('head').append(this);
          }
        });
      }
      else {
        window.WoowGallery.skins[skin] = false;
        window.WoowGallery.lightboxes[lightbox] = false;
      }

      jQuery('#' + id).closest('.elementor-widget-wp-widget-woowgallery').removeClass('elementor-widget-empty').find('.elementor-widget-empty-icon').remove();
    });
  }

  if (lightbox) {
    if (!WoowGallery.lightboxes || !WoowGallery.lightboxes[lightbox]) {
      if (!WoowGallery.lightboxes) {
        WoowGallery.lightboxes = {};
      }
      WoowGallery.lightboxes[lightbox] = true;

      jQuery.get(
        WoowGallery.ajaxurl,
        {
          action: 'woowgallery_skin_assets',
          skin: skin,
          lightbox: lightbox
        }
      ).done(function(response) {
        if (response && response.success) {
          const assets = jQuery(response.data);
          assets.filter('link').each(function(el) {
            if (!jQuery('link[href="' + this.href + '"]').length) {
              jQuery('head').append(this);
            }
          });
          assets.filter('script').each(function(el) {
            if (!jQuery('script[src="' + this.src + '"]').length) {
              jQuery('head').append(this);
            }
          });
        }
        else {
          window.WoowGallery.lightboxes[lightbox] = false;
        }
      });
    }
  }
};
