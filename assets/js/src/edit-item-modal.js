/**
 * Edit gallery item
 */
(function($, _) {

  let woowgallery = window.WoowGalleryAdmin;

  woowgallery.mixins.push({
    data: function() {
      return {
        editItem: false,
        editItemSaving: false
      };
    },
    computed: {
      editItemIndex: function() {
        if (this.editItem) {
          return _.findIndex(this.gallery_paged, {id: this.editItem.id});
        }

        return -1;
      },
      // Edit next item.
      editItemNext: function() {
        let index = this.editItemIndex;
        if (index < 0 || (index + 1) >= this.gallery_paged.length) {
          return false;
        }

        return this.gallery_paged[index + 1];
      },

      // Edit previous item.
      editItemPrev: function() {
        let index = this.editItemIndex;
        if (index < 0 || (index - 1) < 0) {
          return false;
        }

        return this.gallery_paged[index - 1];
      }
    },
    watch: {
      editItem: function(newEditItem, oldEditItem) {
        if (newEditItem) {
          if (!oldEditItem) {
            $('body').addClass('modal-open');

            // Init QuickTags on the caption editor
            this.$nextTick(() => {
              quicktags(
                {
                  id: 'item-caption',
                  buttons: 'strong,em,link,ul,ol,li,close'
                }
              );
              QTags._buttonsInit();
              woowgalleryTagBox.init();
            });
          }
          else {
            this.$nextTick(() => {
              woowgalleryTagBox.quickClicks();
            });
          }
        }
        else {
          $('body').removeClass('modal-open');
        }
      }
    },
    methods: {
      // Trigger edit item modal window.
      editItemSet: function(item, event) {
        if (event) {
          event.preventDefault();
        }

        if (!item) {
          return false;
        }

        this.editItemClose();
        this.$nextTick(() => {
          this.editItem = _.find(this.gallery, (media) => (media.id === item.id));
        });
      },

      // Close edit item modal window.
      editItemClose: function() {
        this.editItem = false;
      },

      editItemPageLink: function() {
        if ('attachment' === this.editItem.type) {
          let attachment = wp.media.attachment(this.editItem.id),
            att = attachment.attributes;
          if (att && att.link) {
            return att.link;
          }
        }

        let content_item = window.woowgallery_content_indexed[this.editItem.id];
        if (content_item && content_item.src) {
          return content_item.src;
        }

        return '';
      },

      editItemSetCopyright: function({type, target}) {
        if ('attachment' === this.editItem.type && 'change' === type) {
          let content_item = window.woowgallery_content_indexed[this.editItem.id];
          if (content_item) {
            content_item.copyright = target.value;
          }

          // Set copyright for media item.
          this.editItemSaving = true;
          $.post(
            ajaxurl,
            {
              action: 'woowgallery_set_media_copyright',
              _nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val(),
              media_id: this.editItem.id,
              copyright: target.value
            }
          ).done((r) => {
            this.editItemSaving = false;
          });
        }
      },

      editItemTagsTaxonomy: function(real) {
        let content_item = window.woowgallery_content_indexed[this.editItem.id];
        if (content_item && content_item.tags_taxonomy) {
          return !content_item.tags_taxonomy && !real ? 'media_tag' : content_item.tags_taxonomy;
        }

        return real && 'attachment' !== this.editItem.type ? '' : 'media_tag';
      },

      editItemTypeIn: function(types) {
        types = Array.isArray(types) ? types : [types];
        return types.indexOf(this.editItem.type) > -1;
      }
    }
  });

})(jQuery, _);
