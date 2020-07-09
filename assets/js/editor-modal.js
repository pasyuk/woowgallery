/**
 * Creates and handles a WoowGallery modal
 */

window.WoowGalleryAdmin = window.WoowGalleryAdmin || {l10n: {}};

(function($, _, i18n) {

  let woowgallery = window.WoowGalleryAdmin;
  let ajaxCall = null;

  let modal = new Vue({
    el: '#woowgallery-modal',
    data: {
      content: [],
      // selected ids
      selected: [],
      selected_last_item: null,
      search_term: '',
      page: 1,
      pages: 1,
      per_page: woowgallery.l10n.per_page,
      loading: false,
      inserting: false,
      frame_title: '',
      menu_toggle: false,
      wg_width: '100',
      wg_width_unit: '%',
      wg_align: 'center',
      time: null,
      modal_type: null,
      restbase: null,
      get_post_type: null,
      prepend_mode: woowgallery.l10n.selection_prepend,
      hide_selected: false,
      multiselect: false,
      openModalCallback: null,
      modalIframeSrc: null,
      previous_screen: null
    },
    computed: {
      // store media IDs in custom order
      media_ids: function() {
        return _.pluck(this.content, 'id');
      },
      gallery_posttype_ids: function() {
        if ('shortcode' === this.modal_type || 'block' === this.modal_type) {
          return [];
        }
        return _.pluck(_.filter(woowgallery.Gallery.gallery, (item) => (item.subtype === this.get_post_type)), 'id');
      },
      selected_last: function() {
        return _.last(this.selected);
      }
    },
    watch: {
      selected_last: function(id) {
        if (id) {
          this.selected_last_item = _.find(this.content, (item) => (item.id === id));
        }
        else {
          this.selected_last_item = null;
        }
      },
      search_term: function(s) {
        if (s.length >= 3 || '' === s) {
          if (this.time) {
            clearTimeout(this.time);
          }
          this.time = setTimeout(() => {
            this.getPosts(true);
          }, 1000);
        }
      },
      page: function(p) {
        this.getPosts();
      },
      pages: function(pages) {
        if (pages < this.page) {
          this.page = pages ? pages : 1;
        }
      },
      prepend_mode: function(mode) {
        woowgallery.l10n.selection_prepend = mode;
      },
      hide_selected: function(hide) {
        let exclude = hide ? this.gallery_posttype_ids : false;
        this.getPosts(true, exclude);
      },
      get_post_type: function(post_type) {
        this.rest_base = woowgallery.post_types[post_type].base;
        this.content = [];
        this.selected = [];
        this.search_term = '';
        let exclude = this.hide_selected ? this.gallery_posttype_ids : false;
        this.getPosts(true, exclude);
      }
    },
    filters: {
      mediaDate: function(date) {
        return (new Date(date)).toLocaleString();
      },
      mediaTags: function(tags) {
        return tags.length ? i18n.sprintf(i18n.__('Tags: %s', 'woowgallery'), tags.join(', ')) : '';
      }
    },
    mounted: function() {
      let vm = this;
      $(document).on('click', '.woowgallery-modal-button', function() {
        const modal_type = $(this).data('modal');
        const post_type = $(this).data('posttype');
        const title = $(this).attr('title');
        vm.openModal(modal_type, post_type, title);
      });
    },
    methods: {
      openModal: function(modal_type, post_type, title, callback) {
        this.frame_title = title;
        this.multiselect = !('shortcode' === modal_type || 'block' === modal_type);
        if (modal_type !== this.modal_type || post_type !== this.get_post_type) {
          this.content = [];
          this.modal_type = modal_type;
          this.get_post_type = post_type;
        }
        else if (this.content.length === 0) {
          let exclude = this.hide_selected ? this.gallery_posttype_ids : false;
          this.getPosts(true, exclude);
        }
        this.$el.classList.add('woowgallery-modal-active');

        if (typeof callback === 'function') {
          this.openModalCallback = callback;
        }
      },
      createModal: function(post_type, id, title, previous_screen) {
        if(previous_screen) {
          this.previous_screen = {
            frame_title: this.frame_title,
            modal_type: this.modal_type,
            get_post_type: this.get_post_type
          };
        }
        this.frame_title = title;
        if (id) {
          this.modalIframeSrc = woowgallery.editModalSrc + '&post=' + id + '&action=edit';
        }
        else {
          this.modalIframeSrc = woowgallery.editModalSrc + '&post_type=' + post_type;
        }
        this.$el.classList.add('woowgallery-modal-active');
      },
      // Get WoowGallery Posts.
      getPosts: function(reset_page, exclude) {
        if (!this.get_post_type) {
          return;
        }
        if (ajaxCall) {
          ajaxCall.abort();
        }
        this.menu_toggle = false;
        this.loading = true;
        ajaxCall = $.ajax({
          url: woowgallery.wpApiRoot + 'wp/v2/' + this.rest_base,
          method: 'GET',
          beforeSend: (xhr) => {
            xhr.setRequestHeader('X-WP-Nonce', woowgallery.wpApiNonce);
          },
          data: {
            'context': 'view',
            'status': ['future', 'publish', 'private'],
            'per_page': this.per_page,
            'page': reset_page ? 1 : this.page,
            'search': this.search_term,
            'exclude': exclude ? exclude : [],
            '_fields': 'wg_data',
            '_post_type': this.get_post_type
          }
        }).done((data, status, xhr) => {
          ajaxCall = null;
          let wg_data = _.compact(_.pluck(data, 'wg_data'));
          if (!wg_data.length) {
            this.content = [];
            this.pages = 1;
            this.loading = false;
            return;
          }

          let wg_data_indexed = _.indexBy(wg_data, 'id');
          window.woowgallery_content_indexed = {...window.woowgallery_content_indexed, ...wg_data_indexed};
          if (reset_page) {
            this.page = 1;
          }
          this.content = wg_data;
          this.pages = xhr.getResponseHeader('X-WP-TotalPages') || 1;
          this.loading = false;
        });
      },

      insert: function() {
        if (!this.selected_last) {
          return;
        }

        if ('block' === this.modal_type) {
          if (!!wp.blockEditor) {
            if (this.openModalCallback) {
              this.openModalCallback(this.selected_last_item);
            }

            if (woowgallery.blockProps) {
              woowgallery.blockProps.setAttributes({
                id: this.selected_last,
                align: this.wg_align,
                posttype: this.selected_last_item.subtype,
                width: {
                  value: this.wg_width,
                  unit: this.wg_width_unit
                }
              });
            }
          }
        }
        else if ('shortcode' === this.modal_type) {
          let shortcode, id, atts = '';
          id = `id="${this.selected_last}"`;
          if ('100' !== this.wg_width || '%' !== this.wg_width_unit) {
            atts += ` width="${this.wg_width}${this.wg_width_unit}"`;
          }
          if ('center' !== this.wg_align) {
            atts += ` align="${this.wg_align}"`;
          }
          shortcode = `[${this.get_post_type} ${id}${atts}]`;
          wp.media.editor.insert(shortcode);
        }
        else if ('woowgallery' === this.modal_type) {
          let media = this.content.filter((item) => (this.selected.indexOf(item.id) > -1));
          $(document).trigger('woowgalleryInsertMedia', [this, media]);
        }

        this.modalClose();
      },

      loadPreviousScreen: function() {
        this.modalIframeSrc = null;
        this.openModalCallback = null;
        this.modal_type = this.previous_screen.modal_type;
        this.frame_title = this.previous_screen.frame_title;
        this.get_post_type = this.previous_screen.get_post_type;
        this.previous_screen = null;
      },

      mediaTitle: function(item) {
        if ('private' === item.status) {
          return i18n.sprintf(i18n.__('%s - Private', 'woowgallery'), item.title);
        }
        else if ('future' === item.status) {
          return i18n.sprintf(i18n.__('%s - Scheduled', 'woowgallery'), item.title);
        }

        return item.title;
      },

      mediaCount: function(item) {
        let count = parseInt(item.count, 10);
        if (!count) {
          return '';
        }
        return 'woowgallery-album' === item.subtype ?
          i18n.sprintf(i18n._n('%d Gallery', '%d Galleries', count, 'woowgallery'), count)
          :
          i18n.sprintf(i18n._n('%d Media Item', '%d Media Items', count, 'woowgallery'), count);
      },

      modalClose: function() {
        this.selected = [];
        this.search_term = '';
        this.modalIframeSrc = null;
        this.openModalCallback = null;
        this.previous_screen = null;
        this.$el.classList.remove('woowgallery-modal-active');
      },

      // Add classes for item.
      showSelectSwitchers: function() {
        return ('woowgallery' === this.modal_type) && this.content.length && woowgallery.Gallery && woowgallery.Gallery.media_ids && woowgallery.Gallery.media_ids.length;
      },

      // Add classes for item.
      selectedItemClasses: function(id) {
        let classes = [];
        if (this.inGallery(id)) {
          classes.push('selected', 'woowgallery-selected');
        }
        else if (this.isSelected(id)) {
          classes.push('selected');
        }
        if (this.selected_last === id) {
          classes.push('selected-last');
        }

        return classes;
      },

      // Toggle selection of an item in the gallery.
      toggleSelectItem: function(id, event) {
        if (event) {
          event.preventDefault();
        }
        if (this.isSelected(id)) {
          this.deselectItem(id);
        }
        else {
          this.selectItem(id);
        }
      },

      // Checks if an item is already selected.
      isSelected: function(id) {
        return this.selected.indexOf(id) > -1;
      },

      // Checks if an item is already in gallery.
      inGallery: function(id) {
        if ('shortcode' === this.modal_type || 'block' === this.modal_type) {
          return false;
        }

        if (woowgallery.Gallery && woowgallery.Gallery.media_ids) {
          return woowgallery.Gallery.media_ids.indexOf(id) > -1;
        }

        return false;
      },

      // Removes an item from the selected array.
      deselectItem: function(id, event) {
        if (event) {
          event.preventDefault();
        }
        this.selected = $.grep(this.selected, (item) => (item !== id));
      },

      // Adds an item to the selected array.
      selectItem: function(id, event) {
        if (event) {
          event.preventDefault();
        }
        if (this.multiselect) {
          this.selected.push(id);
        }
        else {
          this.selected = [id];
        }
      },

      // Adds an item to the selected array
      selectItemsTo: function(id, event) {
        if (!this.multiselect || !this.selected_last) {
          this.selectItem(id, event);
          return;
        }
        if (event) {
          event.preventDefault();
        }
        let i1 = this.media_ids.indexOf(this.selected_last);
        let i2 = this.media_ids.indexOf(id);
        let newSelectedIds = this.media_ids.slice(Math.min(i1, i2), (Math.max(i1, i2) + 1));
        this.selected = _.uniq(this.selected.concat(newSelectedIds));
      }

    }

  });

  woowgallery.ModalGallery = modal;

})(jQuery, _, window.wp.i18n);
