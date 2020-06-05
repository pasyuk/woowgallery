window.wp = window.wp || {};

(function($, _) {

  var media = wp.media,
    l10n = window.woowgallery_l10n_taxterms || {},
    l10n_defaults = {media_orderby: 'date', media_order: 'DESC'},
    original = {};

  const {__} = wp.i18n;

  _.extend(l10n, media.view.l10n);
  _.defaults(l10n, l10n_defaults);

  /**
   * wp.media.view.AttachmentFilters.Taxonomy
   *
   */
  media.view.AttachmentFilters.Taxonomy = media.view.AttachmentFilters.extend({

    id: function() {
      return 'media-attachment-' + this.options.taxonomy + '-filters';
    },

    className: function() {
      return 'attachment-filters wg-taxonomy-filters attachment-' + this.options.taxonomy + '-filter';
    },

    createFilters: function() {
      var filters = {},
        self = this;

      _.each(self.options.termList || {}, function(term, key) {

        var term_id = term.term_id,
          term_name = $('<div/>').html(term.name).text();

        filters[term_id] = {
          text: term_name,
          props: {
            // orderby: l10n.media_orderby,
            // order: l10n.media_order
          },
          priority: key + 4
        };

        filters[term_id]['props'][self.options.taxonomy] = term_id;
      });

      filters.all = {
        text: __('Filter by %s', 'woowgallery').replace('%s', self.options.singularName),
        props: {
          // orderby: l10n.media_orderby,
          // order: l10n.media_order
        },
        priority: 1
      };

      filters['all']['props'][self.options.taxonomy] = null;

      filters.in = {
        text: '&#8212; ' + __('All %s', 'woowgallery').replace('%s', self.options.pluralName) + ' &#8212;',
        props: {
          // orderby: l10n.media_orderby,
          // order: l10n.media_order
        },
        priority: 2
      };

      filters['in']['props'][self.options.taxonomy] = 'in';

      filters.not_in = {
        text: '&#8212; ' + __('Not in %s', 'woowgallery').replace('%s', self.options.singularName) + ' &#8212;',
        props: {
          // orderby: l10n.media_orderby,
          // order: l10n.media_order
        },
        priority: 3
      };

      filters['not_in']['props'][self.options.taxonomy] = 'not_in';

      this.filters = filters;
    }
  });

  /**
   * wp.media.view.AttachmentsBrowser
   *
   */
  original.AttachmentsBrowser = {
    initialize: media.view.AttachmentsBrowser.prototype.initialize,
    createToolbar: media.view.AttachmentsBrowser.prototype.createToolbar
  };

  _.extend(media.view.AttachmentsBrowser.prototype, {

    initialize: function() {
      original.AttachmentsBrowser.initialize.apply(this, arguments);

      this.on('ready', this.fixLayout, this);
      this.$window = $(window);
      this.$window.on('resize', _.debounce(_.bind(this.fixLayout, this), 15));
    },

    fixLayout: function() {
      var $browser = this.$el,
        $attachments = $browser.find('.attachments'),
        $uploader = $browser.find('.uploader-inline'),
        $toolbar = $browser.find('.media-toolbar');

      if (!this.controller.isModeActive('select')) {
        return;
      }

      let top = $toolbar.height() + 10 + 'px';
      $attachments.css('top', top);
      $uploader.css('top', top);
      $browser.find('.eml-loader').css('top', top);
    },

    createToolbar: function() {
      // Make sure to load the original toolbar.
      original.AttachmentsBrowser.createToolbar.call(this);
      var self = this,
          i = 1;

      $.each(l10n.taxonomies, function(taxonomy, values) {

        if (values.term_list.length) {

          self.toolbar.set(taxonomy + 'FilterLabel', new media.view.Label({
            value: __('Filter by %s', 'woowgallery').replace('%s', values.singular_name),
            attributes: {
              'for': 'media-attachment-' + taxonomy + '-filters'
            },
            priority: -70 + i++
          }).render());

          self.toolbar.set(taxonomy + '-filter', new media.view.AttachmentFilters.Taxonomy({
            controller: self.controller,
            model: self.collection.props,
            priority: -70 + i++,
            taxonomy: taxonomy,
            termList: values.term_list,
            singularName: values.singular_name,
            pluralName: values.plural_name
          }).render());
        }
      });
    }
  });

  // /**
  //  * Handler for clicking a list/grid view switch
  //  */
  // $(document).on('click', '.wp-filter .view-switch > a', function(e) {
  //   var href = $(this).attr('href').split('?', 2);
  //   if(2 === href.length) {
  //     var mode = href[1].split('mode=', 2);
  //     if(2 === mode.length) {
  //       mode = mode[1].split('&', 2)[0];
  //       href = href[0] + '?mode=' + mode;
  //       window.location.assign(href);
  //
  //       return false;
  //     }
  //   }
  //
  //   return true;
  // });

})(jQuery, _);
