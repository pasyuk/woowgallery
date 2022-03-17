/**
 * WP Link
 * Example:
 *      1) `<input id="custom-link-field"> <button data-woowgallery-custom-link="custom-link-field">Choose Link</button>`
 *      2) `<input id="custom-link-field" data-woowgallery-custom-link>`
 *
 * @since 1.0.0
 */
jQuery(function($) {
  // Bail out if woowgalleryCustomLink already loaded
  // if(typeof window.woowgalleryCustomLinkLoaded !== 'undefined') {
  //     return;
  // }

  window.woowgalleryCustomLinkLoaded = true;
  if ($('#wp-link-wrap').parent().is('div')) {
    $('#wp-link-backdrop, #wp-link-wrap').appendTo('body');
  }
  $(document).on('click.woowgalleryLink', '[data-woowgallery-custom-link]', function(e) {
    var el = $(this),
      fieldID = el.attr('data-woowgallery-custom-link'),
      linkField;
    if (!fieldID && ('INPUT' === this.tagName || 'TEXTAREA' === this.tagName)) {
      fieldID = el.attr('id');
      if (!fieldID) {
        fieldID = 'id_' + Date.now();
        el.attr('id', fieldID);
      }
    }
    if (!fieldID) {
      return;
    }
    linkField = $('#' + fieldID);
    if (linkField.prop('readonly') || linkField.prop('disabled')) {
      return;
    }
    window.wpActiveEditor = true;
    window.wpLink.open(fieldID);
    window.wpLink.woowgalleryCustomLinkField = linkField;
    $('#wp-link-wrap').removeClass('has-text-field').addClass('woowgalleryLinkModal').find('.link-target').css('visibility', 'hidden');

    return false;
  }).on('click.woowgalleryLink', '.woowgalleryLinkModal #wp-link-submit', function(e) {
    e.preventDefault ? e.preventDefault() : e.returnValue = false;
    e.stopPropagation();

    var link = window.wpLink.getAttrs();
    if (!link.href) {
      closeLinkModal();
      return;
    }

    window.wpLink.textarea = window.wpLink.woowgalleryCustomLinkField;
    window.wpLink.textarea.val(link.href);
    window.wpLink.textarea[0].dispatchEvent(new Event('input'));

    closeLinkModal();
  }).on('click.woowgalleryLink', '#wp-link-cancel, #wp-link-close, #wp-link-backdrop', function(e) {
    closeLinkModal();
  });

  function closeLinkModal() {
    $('#wp-link-wrap').removeClass('woowgalleryLinkModal').find('.link-target').removeAttr('style');
    window.wpLink.close();
  }

});
