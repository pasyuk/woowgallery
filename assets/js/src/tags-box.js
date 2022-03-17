(function($) {

  let quickClicks = window.tagBox.quickClicks;

  window.tagBox.quickClicks = function(el) {
    quickClicks(el);
    var the_tags = $('.the-tags', el);
    if (the_tags.length) {
      the_tags[0].dispatchEvent(new Event('input'));

      if ('add' === window.tagBox.userAction || 'remove' === window.tagBox.userAction) {
        window.tagBox.userAction = '';
        var media_id = the_tags.attr('data-media-id'),
          taxonomy = the_tags.attr('data-taxonomy'),
          tags = the_tags.val();

        if (!media_id) {
          return;
        }

        if (taxonomy) {
          // Set tags for media.
          $.post(
            ajaxurl,
            {
              action: 'woowgallery_set_media_tags',
              _nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val(),
              media_id: media_id,
              taxonomy: taxonomy,
              tags: tags
            },
            function(r) {
            }
          );
          let tags_array = _.uniq(_.compact(tags.split(',')));
          let content_item = window.woowgallery_content_indexed[media_id];
          if (content_item) {
            content_item.tags = tags_array;
          }

          let editItem = window.WoowGalleryAdmin.Gallery.editItem;
          if (editItem && 'attachment' === editItem.type) {
            let attachment = wp.media.model.Attachment.get(media_id);
            attachment.set('woowgallery_tags', tags_array);
          }
        }
      }
    }
  };

  /**
   * The TagBox object.
   *
   * Contains functions to create and manage tags that can be associated with a post.
   *
   * @global
   */
  window.woowgalleryTagBox = {
    quickClicks: function() {
      var woowgallery_portal = $('#woowgallery-portal');

      $('.tagsdiv', woowgallery_portal).each(function() {
        tagBox.quickClicks(this);
      });
    },
    /**
     * Initializes the tags box by setting up the links, buttons. Sets up event
     * handling.
     *
     * This includes handling of pressing the enter key in the input field and the
     * retrieval of tag suggestions.
     */
    init: function() {
      var woowgallery_portal = $('#woowgallery-portal');

      $('.tagsdiv', woowgallery_portal).each(function() {
        tagBox.quickClicks(this);
      });

      $('.tagadd', woowgallery_portal).click(function() {
        tagBox.userAction = 'add';
        tagBox.flushTags($(this).closest('.tagsdiv'));
      });

      /**
       * Handles pressing enter on the new tag input field.
       *
       * Prevents submitting the post edit form. Uses `keypress` to take
       * into account Input Method Editor (IME) converters.
       *
       * @param {Event} event The keypress event that occurred.
       */
      $('input.newtag', woowgallery_portal).keypress(function(event) {
        if (13 == event.which) {
          tagBox.userAction = 'add';
          tagBox.flushTags($(this).closest('.tagsdiv'));
          event.preventDefault();
          event.stopPropagation();
        }
      }).each(function(i, element) {
        $(element).wpTagsSuggest();
      });

      /**
       * Handles clicking on the tag cloud link.
       *
       * Makes sure the ARIA attributes are set correctly.
       *
       * @since 2.9.0
       *
       * @return {void}
       */
      $('.tagcloud-link', woowgallery_portal).click(function() {
        // On the first click, fetch the tag cloud and insert it in the DOM.
        tagBox.get($(this).attr('id'));
        // Update button state, remove previous click event and attach a new one to toggle the cloud.
        $(this).attr('aria-expanded', 'true').unbind().click(function() {
          $(this).attr('aria-expanded', 'false' === $(this).attr('aria-expanded') ? 'true' : 'false').siblings('.the-tagcloud').toggle();
        });
      });
    }
  };
})(jQuery);
