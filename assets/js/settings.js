/**
 * WoowGallery Settings
 */

/**
 * You'll need to use CodeKit or similar, as this file is a placeholder to combine
 * the following JS files into __FILE__.min.js:
 */

// @codekit-prepend "src/globals.js";
// @codekit-prepend "src/tabs.js";
// @codekit-prepend "src/settings-mixins.js";
// @codekit-prepend "src/settings-lightbox.js";
// @codekit-prepend "src/settings-skins.js";

(function($) {

  // let woowgallery = window.WoowGalleryAdmin;
  //
  // Vue.use(Toasted);
  //
  // $(function() {
  //   let $license_field = $('#woowgallery-license'),
  //       $license_button = $('.woowgallery-license-action-button');
  //   $license_button.on('click', function(e) {
  //     e.preventDefault();
  //
  //     let license = $license_field.val(),
  //         action = $(this).attr('data-action');
  //
  //     if (license && action) {
  //       woowgallery_check_license(license, action);
  //     }
  //   });
  //
  //   woowgallery_check_license($license_field.val(), 'check');
  //
  //   function woowgallery_check_license(license, action) {
  //     if ('check' === action && !license) {
  //       return;
  //     }
  //
  //     $license_button.parent().addClass('activity');
  //
  //     // Send the ajax request to validate the license.
  //     $.post(
  //         'https://woowgallery.com/woowbox/license-server.php',
  //         {
  //           action: action,
  //           site: woowgallery.l10n.siteurl,
  //           key: license
  //         },
  //         function(response) {
  //           let key = '';
  //           // Response should be a JSON success with the gallery data
  //           if (response && response.success) {
  //             key = response.key;
  //             if (response.success.message) {
  //               Vue.toasted.success(response.success.message, {duration: 2000});
  //             }
  //           }
  //           else if (response && response.error) {
  //             Vue.toasted.error(response.error.message, {duration: 2000});
  //           }
  //           else {
  //             Vue.toasted.error(':(', {duration: 2000});
  //             if ('check' === action) {
  //               return;
  //             }
  //           }
  //           if ('activate' === action && !key) {
  //             return;
  //           }
  //           if ('check' === action && key) {
  //             return;
  //           }
  //           $.post(
  //               ajaxurl,
  //               {
  //                 action: 'woowgallery_license',
  //                 _nonce_woowgallery_settings_save: $('#_nonce_woowgallery_settings_save').val(),
  //                 license: key,
  //                 license_action: action
  //               },
  //               (response) => {
  //                 // Response should be a JSON success with the message
  //                 if (response && response.success) {
  //                   if (response.data) {
  //                     Vue.toasted.success(response.data, {duration: 2000});
  //                   }
  //                   setTimeout(() => {
  //                     window.location.href = window.location.href;
  //                   }, 1600);
  //                 }
  //                 else if (response && response.data) {
  //                   if (response.data) {
  //                     // Display some error here
  //                     Vue.toasted.error(response.data, {duration: 2000});
  //                   }
  //                 }
  //                 else if (key) {
  //                   Vue.toasted.error(':@', {duration: 2000});
  //                 }
  //               },
  //               'json'
  //           );
  //         },
  //         'json'
  //     ).always(function() {
  //       $license_button.parent().removeClass('activity');
  //     });
  //   }
  // });

})(jQuery);
