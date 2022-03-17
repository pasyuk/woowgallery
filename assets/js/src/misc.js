/**
 * Misc JS
 */
(function($) {

  let woowgallery = window.WoowGalleryAdmin;

  woowgallery.mixins.push({
    filters: {
      formatTags: function(tags) {
        if (!tags) {
          return '';
        }
        if (_.isString(tags)) {
          tags = tags.split(',');
        }
        if (!_.isArray(tags) || !_.size(tags)) {
          return '';
        }
        tags = $.map(tags, (tag) => $.trim(tag));
        return tags.sort((a, b) => a.localeCompare(b, undefined, {sensitivity: 'base'})).join(', ');
      }
    }
  });

  $(function() {
    let choose_skin_button = $('.woowgallery-choose-skin'),
      woowgallery_skins = $('#woowgallery-skin-select');

    choose_skin_button.on('click', 'button', function(e) {
      e.preventDefault();
      if (choose_skin_button.hasClass('closed')) {
        choose_skin_button.removeClass('closed');
        woowgallery_skins.css('display', 'none').removeClass('closed').slideDown();
      }
      else {
        choose_skin_button.addClass('closed');
        woowgallery_skins.slideUp(() => {
          $(this).addClass('closed');
        });
      }
    });

    let tick;
    woowgallery_skins.on('click', 'label', function() {
      if (tick) {
        clearTimeout(tick);
      }
      else {
        choose_skin_button.addClass('activity');
      }
      tick = setTimeout(() => {
        woowgallery.Skin.skin = $('input:checked', this).val();
        choose_skin_button.removeClass('activity');
        tick = null;
      }, 300);
    });

    var parent = window.opener || window.top;
    if (parent && typeof parent.WoowGalleryAdmin === 'object' && typeof parent.WoowGalleryAdmin.blocksUpdater === 'function') {
      const post_ID = parseInt($('#post_ID').val());
      const post_type = $('#post_type').val();

      if ($('#message').length) {
        parent.WoowGalleryAdmin.blocksUpdater({action: 'updated', id: post_ID, type: post_type});
      }

      $('#publish[name="publish"]').on('click', function() {
        parent.WoowGalleryAdmin.blocksUpdater({action: 'prepare', id: post_ID, type: post_type});
      });
    }
  });

})(jQuery);
