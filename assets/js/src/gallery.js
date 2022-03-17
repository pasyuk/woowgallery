/**
 * Make gallery sortable
 */
(function($, _) {

  let woowgallery = window.WoowGalleryAdmin,
    $activity = $('#activity');

  woowgallery.mixins.push({
    data: function() {
      return {
        // gallery data
        gallery: [],
        gallery_extended: [],
        gallery_paged: [],
        filter: '',
        sortby: woowgallery.l10n.sortby,
        sortorder: woowgallery.l10n.sortorder,
        // selected ids
        selected: [],
        // gallery view page
        page: 1,
        per_page: woowgallery.l10n.per_page,
        total_media: 0,
        // gallery view type
        view: woowgallery.l10n.view_mode,
        ready: {}
      };
    },
    computed: {
      // store media IDs in custom order
      all_ready: function() {
        if (Object.keys(this.ready).length) {
          for (let key in this.ready) {
            if (this.ready[key] === false) {
              return false;
            }
          }
        }
        return true;
      },
      // store media IDs in custom order
      media_ids: function() {
        return $.map(this.gallery, (item) => item.id);
      },
      selected_last: function() {
        return _.last(this.selected);
      },
      selected_types: function() {
        if (!this.selected.length) {
          return [];
        }

        return _.uniq(_.pluck(this.gallery.filter((item) => (this.selected.indexOf(item.id) > -1)), 'type'));
      },
      pages: function() {
        if (!this.per_page) {
          return 0;
        }

        return Math.ceil(this.total_media / this.per_page);
      }
    },
    watch: {
      all_ready: function(ready) {
        // Show the view
        this.toggleClassActivity(!ready);
      },
      // on gallery change
      gallery: {
        handler: function(newGallery) {
          let newGallery_str = JSON.stringify(newGallery),
            newGallery_json = JSON.parse(newGallery_str);
          // store base info to the post_content_filtered field
          $('#woowgallery-data').val(newGallery_str);

          this.gallery_extended = $.each(newGallery_json, (i, item) => {
            item.title = this.itemTitle(item);
            item.caption = this.itemCaption(item);
            item.alt = this.itemAlt(item);
            item.thumb = this.itemThumbnail(item);
            item.image = this.itemImage(item);
            item.date = this.itemDate(item);
            item.tags = this.itemTags(item);
            if ('post' === item.type) {
              item.has_password = this.itemHasPassword(item);
            }
            if ('woowgallery' === item.subtype) {
              item.count = this.itemGalleryCount(item);
            }
          });

          this.doGalleryPaged();

        },
        deep: true
      },
      page: function(page) {
        this.doGalleryPaged();
      },
      pages: function(pages) {
        if (pages < this.page) {
          this.page = pages ? pages : 1;
        }
      },
      filter: function(filter) {
        if (this.page > 1) {
          this.page = 1;
        }
        else {
          this.doGalleryPaged();
        }
      },
      // on media_ids array change
      media_ids: function(ids) {
        // check if deleted items was selected and rebuild selected array
        this.selected = $.grep(this.selected, (id) => (ids.indexOf(id) > -1));
      },
      sortby: function(newSortby) {
        if ('custom' !== newSortby) {
          this.gallery = this.doGallerySorted(this.gallery);
        }
      },
      sortorder: function(newSortorder, oldSortorder) {
        if (newSortorder !== oldSortorder) {
          this.gallery = this.gallery.reverse();
        }
      }
    },
    methods: {
      // toggle activity indicator
      toggleClassActivity: function(status) {
        $activity.toggleClass('activity', status);
      },

      setReady: function(key, value) {
        if (typeof this.ready[key] === 'undefined') {
          this.$set(this.ready, key, value);
        }
        else {
          this.ready[key] = value;
        }
      },

      // Fetch attachments data that are on current gallery page.
      fetchMedia: function(gallery) {
        let attachments = _.filter(gallery, (item) => {
          if ('attachment' === item.type) {
            let attachment = wp.media.attachment(item.id),
              att = attachment.attributes;
            return (!att || !att.title);
          }

          return false;
        });
        if (attachments.length) {
          let fetch_attachments_ids = _.pluck(attachments, 'id');
          this.fetchWPMedia(fetch_attachments_ids);
        }

        let posts = _.filter(gallery, (item) => {
          if ('post' === item.type) {
            let content_item = window.woowgallery_content_indexed[item.id];
            return (!content_item || typeof content_item.excerpt === 'undefined');
          }
          return false;
        });
        if (posts.length) {
          let post_types = _.uniq(_.pluck(posts, 'subtype'));
          _.each(post_types, (post_type) => {
            let fetch_posts_ids = _.pluck(_.filter(posts, (item) => (post_type === item.subtype)), 'id');
            this.fetchWPPosts(fetch_posts_ids, post_type);
          });
        }
      },

      // Fetch attachments data that are on current gallery page.
      fetchWPMedia: function(fetch_attachments_ids) {
        let att_length = fetch_attachments_ids.length;
        if (att_length) {
          let vm = this;
          this.setReady('attachments', false);

          // build query for ids in the gallery
          let query = wp.media.query({post__in: fetch_attachments_ids, orderby: 'post__in'});
          loadQuery(query, att_length);

          // load attachments data via AJAX, check if there are more pages and load them all recursively
          function loadQuery(query, att_length) {
            query.more().then((data) => {
              if (query.hasMore() && query.length < att_length) {
                loadQuery(query);
              }
              else {
                vm.updateMediaData();
                vm.setReady('attachments', true);
              }
            });
          }
        }
        else {
          this.setReady('attachments', true);
        }
      },

      // Fetch attachments data that are in gallery
      fetchWPPosts: function(fetch_ids, post_type) {
        if (fetch_ids.length && woowgallery.post_types[post_type]) {
          this.setReady(post_type, false);
          $.ajax({
            url: woowgallery.wpApiRoot + 'wp/v2/' + woowgallery.post_types[post_type].base,
            method: 'GET',
            beforeSend: (xhr) => {
              xhr.setRequestHeader('X-WP-Nonce', woowgallery.wpApiNonce);
            },
            data: {
              'context': 'view',
              // 'status': ['future', 'publish', 'private', 'draft', 'pending'],
              'status': 'any',
              'include': fetch_ids,
              'per_page': this.per_page,
              'orderby': 'include',
              '_fields': 'wg_data',
              '_post_type': post_type
            }
          }).done((data, status, xhr) => {
            // _pages = _pages || xhr.getResponseHeader( 'X-WP-TotalPages' ) || 1;
            let wg_data = _.compact(_.pluck(data, 'wg_data'));
            if (wg_data.length) {
              let wg_data_indexed = _.indexBy(wg_data, 'id');
              window.woowgallery_content_indexed = {...window.woowgallery_content_indexed, ...wg_data_indexed};
            }
            this.updateMediaData();
            this.updatePostStatus();
            this.setReady(post_type, true);
          });
        }
        else {
          this.setReady(post_type, true);
        }
      },

      // sort gallery due to sortby value
      doGallerySorted: function(gallery) {
        let order = 'asc' === this.sortorder ? 1 : -1,
          compareA, compareB;

        return gallery.sort((a, b) => {
          switch (this.sortby) {
            case 'title':
              compareA = this.itemTitle(a);
              compareB = this.itemTitle(b);
              return compareA.localeCompare(compareB, undefined, {sensitivity: 'base'}) * order;
            case 'caption':
              compareA = this.itemCaption(a);
              compareB = this.itemCaption(b);
              return compareA.localeCompare(compareB, undefined, {sensitivity: 'base'}) * order;
            case 'alt':
              compareA = this.itemAlt(a);
              compareB = this.itemAlt(b);
              return compareA.localeCompare(compareB, undefined, {sensitivity: 'base'}) * order;
            case 'tags':
              compareA = a.tags;
              compareB = b.tags;
              return compareA.localeCompare(compareB, undefined, {sensitivity: 'base'}) * order;
            case 'date':
              compareA = this.itemDate(a);
              compareB = this.itemDate(b);
              return (compareA < compareB) ? -1 * order : order;
            case 'slug':
              compareA = this.itemSlug(a);
              compareB = this.itemSlug(b);
              return (compareA < compareB) ? -1 * order : order;
          }
          return 0;
        });
      },

      // set gallery_paged value
      doGalleryPaged: function() {
        let gallery,
          filter = this.filter;

        if (filter) {
          gallery = this.gallery_extended.filter((item) => (
            item.title.indexOf(filter) > -1
            || item.caption.indexOf(filter) > -1
            || item.alt.indexOf(filter) > -1
            || item.tags.indexOf(filter) > -1
          ));
        }
        else {
          gallery = this.gallery_extended;
        }

        this.total_media = gallery.length;

        if (this.per_page) {
          gallery = gallery.slice((this.page - 1) * this.per_page, this.page * this.per_page);
        }

        this.fetchMedia(gallery);
        this.gallery_paged = gallery;
      },

      // Update media Tags
      updateMediaData: function() {
        this.gallery = $.each(this.gallery, (i, item) => {
          if ('attachment' === item.type) {
            let attachment = wp.media.attachment(item.id),
              att = attachment.attributes;
            if (att) {
              if (att.woowgallery_tags) {
                // item.tags = _.uniq(_.compact([...att.woowgallery_tags, ...item.tags.split(',')])).join(',');
                item.tags = att.woowgallery_tags.join(',');
              }
              if (att.copyright) {
                let content_item = window.woowgallery_content_indexed[item.id];
                if (content_item) {
                  content_item.copyright = att.copyright;
                }
              }
            }
          }
          else {
            let content_item = window.woowgallery_content_indexed[item.id];
            if (content_item && content_item.tags) {
              if (content_item.tags_taxonomy) {
                item.tags = content_item.tags.join(',');
              }
              else {
                item.tags = _.uniq(_.compact([...content_item.tags, ...item.tags.split(',')])).join(',');
              }
            }
          }
        });
      },

      // Update post status
      updatePostStatus: function() {
        this.gallery = $.each(this.gallery, (i, item) => {
          if ('post' === item.type) {
            item.status = this.itemStatus(item);
          }
        });
      },

      // Get item by ID
      getItemById: function(id) {
        return _.find(this.gallery, (media) => (media.id === id));
      },

      // Check item type
      isType: function(id, type) {
        let item = this.getItemById(id);
        if (item) {
          type = Array.isArray(type) ? type : [type];
          return type.indexOf(item.type) > -1;
        }

        return false;
      },

      remoteImageSize: function(url) {
        return new Promise((resolve, reject) => {
          let img = new Image();
          img.onload = () => resolve(img);
          img.onerror = reject;
          img.src = url;
        });
      },

      // Get item's original src.
      itemOriginalSrc: function(item) {

        if (item.original) {
          return item.original;
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.original) {
          return content_item.original;
        }

        if ('attachment' === item.type) {
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.url) {
            return att.url;
          }
        }

        if (item.src) {
          return item.src;
        }

        if (content_item && content_item.src) {
          return content_item.src;
        }
      },

      // Get item's thumbnail
      itemThumbnail: function(item) {

        if (item.thumb && item.thumb[0]) {
          return item.thumb;
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.thumb && content_item.thumb[0]) {
          let thumb = content_item.thumb;
          return [thumb[0], thumb[1], thumb[2]];
        }

        if ('attachment' === item.type) {
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.name) {
            if (att.sizes) {
              if (att.sizes.medium) {
                return [att.sizes.medium.url, att.sizes.medium.width, att.sizes.medium.height];
              }
              else if (att.sizes.thumbnail) {
                return [att.sizes.thumbnail.url, att.sizes.thumbnail.width, att.sizes.thumbnail.height];
              }
              else if (att.sizes.large) {
                return [att.sizes.large.url, att.sizes.large.width, att.sizes.large.height];
              }
              else if (att.sizes.full) {
                return [att.sizes.full.url, att.sizes.full.width, att.sizes.full.height];
              }
            }
            else if ((att.image && att.image.src) && (att.image.src !== att.icon)) {
              return [att.image.src, att.image.width, att.image.height];
            }
            else if (att.icon) {
              let icon_name = att.icon.split('/').reverse()[0];

              return [woowgallery.l10n.icons_url + '/' + icon_name, 300, 300, false, true];
            }
          }

          return [woowgallery.l10n.icons_url + '/' + item.subtype + '.png', 300, 300, false, true];
        }
        else {

          return [woowgallery.l10n.icons_url + '/default.png', 300, 300, false, true];
        }
      },

      // Get item's thumbnail
      itemImage: function(item) {
        if (item.image && item.image[0]) {
          return item.image;
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.image && content_item.image[0]) {
          let image = content_item.image;
          return [image[0], image[1], image[2]];
        }

        if ('attachment' === item.type) {
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.name) {
            if (att.sizes) {
              if (att.sizes.full) {
                return [att.sizes.full.url, att.sizes.full.width, att.sizes.full.height];
              }
            }
            else if ((att.image && att.image.src) && (att.image.src !== att.icon)) {
              return [att.image.src, att.image.width, att.image.height];
            }
            else if (att.icon) {
              let icon_name = att.icon.split('/').reverse()[0];

              return [woowgallery.l10n.icons_url + '/' + icon_name, 300, 300, false, true];
            }
          }

          return [woowgallery.l10n.icons_url + '/' + item.subtype + '.png', 300, 300, false, true];
        }
        else {

          return [woowgallery.l10n.icons_url + '/default.png', 300, 300, false, true];
        }
      },

      // Get item's title
      itemTitle: function(item) {
        if ('custom' === item.title_src) {
          return item.title;
        }

        if ('attachment' === item.type) {
          var attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.name) {
            if (att[item.title_src]) {
              return att[item.title_src];
            }
            else {
              return att.title;
            }
          }
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.title) {
          return content_item.title;
        }

        return item.title;
      },

      // Get item's alt
      itemAlt: function(item) {
        if ('custom' === item.alt_src) {
          return item.alt;
        }

        if ('attachment' === item.type) {
          if ('title' === item.alt_src) {
            return this.itemTitle(item);
          }
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.alt) {
            if (att[item.alt_src]) {
              return att[item.alt_src];
            }
            else {
              return att.alt;
            }
          }
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.alt) {
          return content_item.alt;
        }

        return item.alt;
      },

      // Get item's caption
      itemCaption: function(item) {
        if ('custom' === item.caption_src) {
          return item.caption;
        }

        if ('attachment' === item.type) {
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.caption) {
            if (att[item.caption_src]) {
              return att[item.caption_src];
            }
            else {
              return att.caption;
            }
          }
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.caption) {
          if (content_item[item.caption_src]) {
            return content_item[item.caption_src];
          }
          else {
            return content_item.caption;
          }
        }

        return item.caption;
      },

      // Get item's date
      itemDate: function(item) {
        if ('attachment' === item.type) {
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.date) {
            return (typeof att.date.toISOString === 'function') ? att.date.toISOString().split('.')[0] : (new Date(att.date)).toISOString().split('.')[0];
          }
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.date) {
          return content_item.date;
        }

        return item.date ? item.date : '0000-00-00T00:00:00';
      },

      // Get item's slug
      itemSlug: function(item) {
        if ('attachment' === item.type) {
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.name) {
            return att.name;
          }
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.slug) {
          return content_item.slug;
        }

        return item.slug || '';
      },

      // Get item's tags
      itemTags: function(item) {
        if ('attachment' === item.type) {
          let attachment = wp.media.attachment(item.id),
            att = attachment.attributes;
          if (att && att.woowgallery_tags) {
            return _.uniq(_.compact([...att.woowgallery_tags, ...item.tags.split(',')])).join(',');
          }
        }

        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.tags) {
          return _.uniq(_.compact([...content_item.tags, ...item.tags.split(',')])).join(',');
        }

        return item.tags;
      },

      // Gallery item media count
      itemGalleryCount: function(item) {
        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.count) {
          return content_item.count;
        }

        return '';
      },

      // Gallery item post status
      itemStatus: function(item) {
        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && 'publish' !== content_item.status && 'private' !== content_item.status) {
          return content_item.status;
        }

        return item.status;
      },

      // Gallery item copyright
      itemCopyright: function(item) {
        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.copyright) {
          return content_item.copyright;
        }

        return '';
      },

      // Gallery item has password
      itemHasPassword: function(item) {
        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.has_password) {
          return content_item.has_password;
        }

        return false;
      },

      // Gallery item edit link
      itemEditLink: function(item) {
        let content_item = window.woowgallery_content_indexed[item.id];
        if (content_item && content_item.edit_link) {
          return content_item.edit_link;
        }

        return '';
      },

      // Item subtype icon
      subtypeIcon: function(item) {
        if ('post' === item.type) {
          if (woowgallery.post_types[item.subtype]) {
            return woowgallery.post_types[item.subtype].icon_html;
          }
          else {
            return `<span class="wg-posttype-icon dashicons dashicons-no"><b>${item.subtype}</b></span>`;
          }
        }
        else if ('attachment' === item.type && 'image' !== item.subtype) {
          let dashicon;
          switch (item.subtype) {
            case 'audio':
            case 'video':
              dashicon = 'format-' + item.subtype;
              break;
            default:
              dashicon = 'paperclip';
              break;
          }

          return `<span class="wg-posttype-icon dashicons dashicons-${dashicon}"></span>`;
        }

        return '';
      },

      // Toggle selection of all items in the gallery
      toggleSelectAll: function() {
        if (this.selected.length) {
          this.selected = [];
        }
        else {
          this.selected = $.map(this.gallery, (item) => item.id);
        }
      },

      // Toggle selection of an item in the gallery
      toggleSelectItem: function(id, event) {
        if (this.sorting) {
          return;
        }
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

      // Checks if an item is already selected
      isSelected: function(id) {
        return this.selected.indexOf(id) > -1;
      },

      // Removes an item from the selected array
      deselectItem: function(id, event) {
        if (event) {
          event.preventDefault();
        }
        this.selected = $.grep(this.selected, (item) => (item !== id));
      },

      // Adds an item to the selected array
      selectItem: function(id, event) {
        if (event) {
          event.preventDefault();
        }
        this.selected.push(id);
      },

      // Adds an item to the selected array
      selectItemsTo: function(id, event) {
        if (!this.selected_last) {
          this.toggleSelectItem(id, event);
          return;
        }
        if (event) {
          event.preventDefault();
        }
        let i1 = this.media_ids.indexOf(this.selected_last);
        let i2 = this.media_ids.indexOf(id);
        let newSelectedIds = this.media_ids.slice(Math.min(i1, i2), (Math.max(i1, i2) + 1));
        this.selected = _.uniq(this.selected.concat(newSelectedIds));
      },

      // Removes an item by ID out of the gallery
      removeItem: function(id, event) {
        if (event) {
          event.preventDefault();
          // Bail out if the user does not actually want to remove the image.
          let confirm_message = $(event.target).attr('data-confirm');
          if (confirm_message && !confirm(confirm_message.toString())) {
            return;
          }
        }
        this.gallery = $.grep(this.gallery, (item) => (item.id !== id));
      },

      // Removes selected items out of the gallery
      removeSelectedItems: function(event) {
        if (event) {
          event.preventDefault();
          // Bail out if the user does not actually want to remove the image.
          let confirm_message = $(event.target).attr('data-confirm');
          if (confirm_message && !confirm(confirm_message.toString())) {
            return;
          }
        }
        if (this.selected.length) {
          this.gallery = $.grep(this.gallery, (item) => (this.selected.indexOf(item.id) === -1));
        }
      },

      // Set status for an item
      setStatus: function(id, status, event) {
        if (event) {
          event.preventDefault();
        }
        this.gallery = $.each(this.gallery, (i, item) => {
          if (item.id === id) {
            item.status = status;
          }
        });
      }
    }
  });

})(jQuery, _);
