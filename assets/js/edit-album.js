/**
 * Creates and handles a WoowGallery CPT, allowing the user to insert images
 * from the WordPress Media Library into their Gallery
 */

/**
 * Media Item data structure
 *
 data: {
  "type": "attachment",
  "subtype": "image",
  "status": "publish",
  "id": "123",
  "title": "Title",
  "alt": "",
  "caption": "",
  "link": {
    "url": "url",
    "target": "_self",
    "text": "link text"
  },
  "author": {
    "id": "1",
    "name": "John Doe",
    "url": ""
  },
  "date": 1234567890000,
  "src": "src",
  "thumb": [
    "src",
    100,
    100
  ],
  "image": [
    "src",
    1000,
    1000
  ],
  "meta": {
    "filesize": 702562,
    "aperture": "",
    "credit": "",
    "camera": "",
    "created_timestamp": "",
    "copyright": "",
    "focal_length": "",
    "iso": "",
    "shutter_speed": "",
    "keywords": []
  },
  "tags": "One,Two"
}
 */

/**
 * You'll need to use CodeKit or similar, as this file is a placeholder to combine
 * the following JS files into __FILE__.min.js:
 */
// @codekit-prepend "src/globals.js";
// @codekit-prepend "src/tabs.js";
// @codekit-prepend "src/wp-link.js";
// @codekit-prepend "src/edit-item-modal.js";
// @codekit-prepend "src/tags-box.js";
// @codekit-prepend "src/gallery.js";
// @codekit-prepend "src/sortable.js";
// @codekit-prepend "src/settings-mixins.js";
// @codekit-prepend "src/settings-skins.js";
// @codekit-prepend "src/misc.js";

(function($) {

  let woowgallery = window.WoowGalleryAdmin;

  let app = new Vue({
    el: '#woowgallery-preview',
    mixins: woowgallery.mixins,
    data: {
      woowgallery_type: 'album'
    },
    computed: {},
    watch: {},
    filters: {},
    mounted: function() {
      // Index woowgallery content by `id`.
      window.woowgallery_content_indexed = _.indexBy(window.woowgallery_content, 'id');

      // On init get gallery info from post_content_filtered input
      let gallery = $('#woowgallery-data').val(),
        new_gallery;
      this.gallery = gallery ? JSON.parse(gallery) : [];

      // On trigger media insert, store selected media in the gallery variable
      $(document).on('woowgalleryInsertMedia', (e, modal, media) => {
        media = _.pluck(media, 'id').filter((id) => (this.media_ids.indexOf(id.toString()) === -1));
        if (!media.length) {
          return;
        }

        app.toggleClassActivity(true);

        // Send the ajax request with our data to be processed.
        $.post(
          ajaxurl,
          {
            action: 'woowgallery_get_media_data',
            // make this a JSON string so we can send larger amounts of data (images), otherwise max is around 20 by default for most server configs
            media: JSON.stringify(media)
          },
          (response) => {
            // Response should be a JSON success with the gallery data
            if (response && response.success) {
              if (woowgallery.l10n.selection_prepend) {
                new_gallery = [].concat(response.data, this.gallery);
              }
              else {
                new_gallery = [].concat(this.gallery, response.data);
              }
              this.gallery = this.doGallerySorted(new_gallery);
            }
          },
          'json'
        ).always(() => {
          this.toggleClassActivity(false);
        });
      });

      // On trigger media remove, delete media from gallery
      $(document).on('woowgalleryDeleteMedia', (e, id) => {
        this.removeItem(id);
      });

    },
    methods: {
    }

  });

  woowgallery.Gallery = app;

})(jQuery);
