/**
 * Bulk Edit gallery items
 */
(function($, _) {

  let woowgallery = window.WoowGalleryAdmin;

  woowgallery.mixins.push({
    data: function() {
      return {
        bulkEdit: false,
        bulkEditType: '',
        bulkEditSaving: false
      };
    },
    computed: {
      bulkEditItems: function() {
        if (this.bulkEdit) {
          return this.gallery_extended.filter((item) => (this.selected.indexOf(item.id) > -1));
        }

        return [];
      }
    },
    watch: {
      bulkEdit: function(edit) {
        if (edit) {
          $('body').addClass('modal-open');

          this.bulkEditType = this.bulkEditItems[0].type;

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
          $('body').removeClass('modal-open');
        }
      }
    },
    methods: {
      // Trigger bulk edit modal window.
      bulkEditSet: function() {
        if (this.selected_types.length > 1) {
          return;
        }
        this.bulkEdit = JSON.parse(JSON.stringify(woowgallery.l10n.bulkEdit));
      },

      // Close edit item modal window.
      bulkEditClose: function() {
        this.bulkEdit = false;
        this.bulkEditType = '';
        this.bulkEditSaving = false;
      },

      // Trigger bulk edit selected items
      bulkEditSave: function(event) {
        if (event) {
          event.preventDefault();
        }

        let set = this.bulkEdit,
          selected;

        selected = this.gallery.filter((item) => (this.selected.indexOf(item.id) > -1));

        $.each(selected, (i, item) => {

          if (set.status && ('publish' === item.status || 'private' === item.status)) {
            item.status = set.status;
          }

          if (set.title_src) {
            item.title_src = set.title_src;
            if ('custom' === set.title_src) {
              item.title = set.title;
            }
          }

          if (set.caption_src) {
            item.caption_src = set.caption_src;
            if ('custom' === set.caption_src) {
              item.caption = set.caption;
            }
          }

          if (set.alt_src) {
            item.alt_src = set.alt_src;
            if ('custom' === set.alt_src) {
              item.alt = set.alt;
            }
          }

          if (set.link.url_change) {
            item.link.url = set.link.url;
          }

          if (set.link.text_change) {
            item.link.text = set.link.text;
          }

          if (set.link.target) {
            item.link.target = set.link.target;
          }

          if (set.copyright) {
            let content_item = window.woowgallery_content_indexed[item.id];
            if (content_item) {
              content_item.copyright = set.copyright.trim();
            }
          }

          if (set.tags) {
            item.tags = array_unique_noempty(item.tags.split(',').concat(set.tags.split(','))).join(',');
          }
        });

        if (set.tags || set.copyright) {
          this.bulkEditSaving = true;
          // Set tags for media items.
          $.post(
            ajaxurl,
            {
              action: 'woowgallery_bulk_set_media_data',
              _nonce_woowgallery_ajax: $('#_nonce_woowgallery_ajax').val(),
              media: JSON.stringify(selected),
              tags: set.tags,
              copyright: set.copyright
            },
            (r) => {
              this.bulkEditSaving = false;
              this.bulkEditClose();
            }
          );
        }

        if (!this.bulkEditSaving) {
          this.bulkEditClose();
        }
      },

      bulkEditTagsTaxonomy: function() {
        if ('attachment' === this.bulkEditType) {
          return 'media_tag';
        }
        if ('post' === this.bulkEditType) {
          return 'post_tag';
        }

        return this.bulkEditType + '_tag';
      }
    }
  });

})(jQuery, _);
