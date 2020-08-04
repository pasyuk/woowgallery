/**
 * Creates and handles a WoowGallery CPT, allowing the user to create dynamic galleries.
 */

/**
 * You'll need to use CodeKit or similar, as this file is a placeholder to combine
 * the following JS files into __FILE__.min.js:
 */
// @codekit-prepend "src/globals.js";
// @codekit-prepend "src/tabs.js";
// @codekit-prepend "src/view-item-modal.js";
// @codekit-prepend "src/settings-mixins.js";
// @codekit-prepend "src/settings-skins.js";
// @codekit-prepend "src/misc.js";

(function($, _) {

  let woowgallery = window.WoowGalleryAdmin,
    $activity = $('#activity'),
    ajaxCall = null;

  // Register Multiselect globally.
  Vue.component('vue-multiselect', window.VueMultiselect.default);

  let app = new Vue({
    el: '#woowgallery-dynamic-query',
    mixins: woowgallery.mixins,
    data: {
      gallery: null,
      page: 1,
      per_page: woowgallery.l10n.per_page,
      error: '',

      query_type: null,
      woowgallery_type: 'dynamic',
      hints: true,
      loading: false,

      wp_taxonomy_terms_options: {
        loading: false,
        data: []
      },
      wp: {
        post_type: null,
        post_author: [],
        order: 'DESC',
        orderby: 'date',
        terms_relation: 'IN',
        taxonomy_terms: [],
        limit: 50,
        offset: 0,
        ignore_sticky: true,
        post_status: [],
        has_password: '',
        post_password: '',
        meta_key: '',
        meta_compare: 'EXISTS',
        meta_value: '',
        post_parent: '',
        post__not_in: ''
      },

      instagram: {
        sources: [],
        sorting: 'date',
        limit_type: 'all',
        limit: 12
      },

      flagallery_source_options: {
        loading: false,
        data: []
      },
      flagallery: {
        source: null,
        order: 'ASC',
        orderby: 'sortorder'
      }
    },
    computed: {
      pages: function() {
        if (!this.per_page) {
          return 0;
        }

        return Math.ceil(this.gallery.post_count / this.per_page);
      },
      gallery_paged: function() {
        if (this.gallery && this.per_page) {
          return this.gallery.posts.slice((this.page - 1) * this.per_page, this.page * this.per_page);
        }
        return [];
      },
      wp_json: function() {
        if ('wp' !== this.query_type) {
          return [];
        }
        let json = JSON.parse(JSON.stringify(this.wp));
        json.post__not_in = _.uniq(_.filter(_.map(json.post__not_in.split(','), (id) => parseInt(id.trim(), 10)))).join(',');
        json.query_type = this.query_type;

        return json;
      },
      wp_taxterms_list: function() {
        return _.flatten(_.pluck(this.wp_taxonomy_terms_options.data, 'terms'));
      },

      instagram_json: function() {
        if ('instagram' !== this.query_type) {
          return [];
        }
        let json = JSON.parse(JSON.stringify(this.instagram));
        json.query_type = this.query_type;

        return json;
      },

      flagallery_json: function() {
        if ('flagallery' !== this.query_type) {
          return [];
        }
        let json = JSON.parse(JSON.stringify(this.flagallery));
        json.query_type = this.query_type;

        return json;
      }
    },
    watch: {
      query_type: function(n, o) {
        if (null !== o) {
          this.gallery = null;
          this.error = '';
        }
        if (this[n + '_json']) {
          $('#woowgallery-data').val(JSON.stringify(this[n + '_json']));
        }
        if ('flagallery' === n) {
          this.flagallery_refreshSource();
        }
      },
      wp_json: function(n, o) {
        if ('wp' !== this.query_type || null === o.post_type) {
          return;
        }

        if (n.terms_relation !== o.terms_relation || JSON.stringify(n.post_type) !== JSON.stringify(o.post_type)) {
          this.wp_refreshTaxonomyTerms();
        }

        $('#woowgallery-data').val(JSON.stringify(n));
      },
      instagram_json: function(n, o) {
        if ('instagram' !== this.query_type) {
          return;
        }

        $('#woowgallery-data').val(JSON.stringify(n));
      },
      flagallery_json: function(n, o) {
        if ('flagallery' !== this.query_type) {
          return;
        }

        $('#woowgallery-data').val(JSON.stringify(n));
      },
      loading: function(status) {
        $activity.toggleClass('activity', status);
      }
    },
    filters: {},
    mounted: function() {
      // On init get gallery info from post_content_filtered input.
      let query_str = $('#woowgallery-data').val();
      let query = query_str ? JSON.parse(query_str) : {};

      this.query_type = query.query_type || 'wp';
      if ('wp' === query.query_type) {
        this.wp_taxonomy_terms_options.data = JSON.parse(window.wp_taxonomy_terms_options);
        this.wp = $.extend({}, this.wp, query);
      }
      else if ('instagram' === query.query_type) {
        this.instagram = $.extend({}, this.instagram, query);
      }
      else if ('flagallery' === query.query_type) {
        this.flagallery = $.extend({}, this.flagallery, query);
      }

      let post_count = window.woowgallery_content.length;
      if (post_count) {
        this.gallery = {
          post_count: post_count,
          query: query_str,
          posts: window.woowgallery_content
        };
      }

      this.$nextTick(() => {
        let query_type = this.query_type;
        $('#woowgallery-data').val(JSON.stringify(this[query_type + '_json']));
      });
    },
    methods: {
      wp_refreshTaxonomyTerms: function() {
        this.wp_taxonomy_terms_options.loading = true;
        $.getJSON(
          ajaxurl,
          {
            action: 'woowgallery_dynamic_refresh_taxonomy_terms',
            post_type: _.pluck(this.wp.post_type, 'name'),
            terms_relation: this.wp.terms_relation
          },
          (r) => {
            this.wp_taxonomy_terms_options.loading = false;
            this.wp_taxonomy_terms_options.data = r.data;
          }
        );
      },
      wp_taxtermMissed: function(id) {
        return !_.some(this.wp_taxterms_list, {id: id});
      },
      wp_fetchQuery: function() {
        const json = $('#woowgallery-data').val();
        const post_id = $('#post_ID').val();

        if (ajaxCall) {
          ajaxCall.abort();
        }
        this.loading = true;
        ajaxCall = $.ajax({
          url: ajaxurl,
          method: 'GET',
          // xhrFields: {
          //   withCredentials: true
          // },
          data: {
            action: 'woowgallery_dynamic_fetch_query',
            gallery_id: post_id,
            json: json
          }
        }).done((data, status, xhr) => {
          ajaxCall = null;
          if (data.success) {
            this.gallery = data.data;
            if (data.data.errors && data.data.errors.length) {
              console.log(data.data.errors);
              this.error = data.data.errors[0];
            }
            else {
              this.error = '';
            }
          }
          else {
            console.log(data);
            this.gallery = null;
            if (data.data) {
              this.error = data.data;
            }
          }
          this.loading = false;
        });
      },

      addSource: function(newSource) {
        this.instagram.sources.push(newSource);
      },

      flagallery_refreshSource: function() {
        this.flagallery_source_options.loading = true;
        $.getJSON(
          ajaxurl,
          {
            action: 'woowgallery_dynamic_refresh_flagallery_source'
          },
          (r) => {
            this.flagallery_source_options.loading = false;
            this.flagallery_source_options.data = r.data;
          }
        );
      },
      flagallerySourceSelect: function(option) {
        if (option) {
          this.flagallery.source = {
            gid: option.gid,
            title: option.title
          };
        }
      },

      // Get item's src.
      itemOriginalSrc: function(item) {

        if (item.original) {
          return item.original;
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

        return '';
      },

      // Get item's thumbnail.
      itemThumbnail: function(item) {
        if (item.thumb && item.thumb[0]) {
          return item.thumb[0];
        }

        return woowgallery.l10n.icons_url + '/default.png';
      },

      // Get item's image.
      itemImage: function(item) {
        if ('instagram' === item.type && item._original && item._original[0]) {
          return item._original;
        }
        if (item.image && item.image[0]) {
          return item.image;
        }

        return [woowgallery.l10n.icons_url + '/default.png', 300, 300];
      },

      // Removes an item by ID out of the gallery
      removeItem: function(remove_id, event) {
        if (event) {
          event.preventDefault();
          // Bail out if the user does not actually want to remove the image.
          let confirm_message = $(event.target).attr('data-confirm');
          if (confirm_message && !confirm(confirm_message.toString())) {
            return;
          }
        }
        this.wp.post__not_in = _.filter(_.union(_.map(this.wp.post__not_in.split(','), (id) => parseInt(id.trim(), 10)), [remove_id])).join(',');
        this.gallery.posts = $.grep(this.gallery.posts, (item) => (item.id !== remove_id));
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

      captionHashtags: function(str) {
        return str.replace(/(?<!&)(\#[\-\w]+)/ig, '<span class="hashtag">$1</span>');
      }
    }
  });

  woowgallery.Gallery = app;

})(jQuery, _);
