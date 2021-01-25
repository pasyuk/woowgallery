window.wp = window.wp || {};

/**
 * Handles:
 * - Copy to Clipboard functionality
 *
 * @since 1.0.0
 */
jQuery(function($) {

  const {__} = wp.i18n;

  $('#screen-meta, #screen-meta-links').appendTo('#woowgallery-screen-meta-block');

  /**
   * Copy to Clipboard
   * @since 1.0.0
   */
  if (typeof ClipboardJS !== 'undefined') {
    let clipboard = new ClipboardJS('.woowgallery-clipboard');
    clipboard.on('success', function(e) {
      //console.info('Action:', e.action);
      //console.info('Text:', e.text);
      //console.info('Trigger:', e.trigger);
      //e.clearSelection();
    });
    clipboard.on('error', function(e) {
      //console.error('Action:', e.action);
      //console.error('Trigger:', e.trigger);
    });
    $(document).on('click', '.woowgallery-clipboard', function(e) {
      e.preventDefault();
    });
  }
  else {
    $('.woowgallery-clipboard').hide();
  }

  $('.cache-clear').on('click', function(e) {
    e.preventDefault();
    let id = $(this).attr('value');

    $(this).prop('disabled', true);
    $.post(
      ajaxurl,
      {
        action: 'woowgallery_cache_clear',
        id: id
      }
    ).done((response) => {
      if (response && response.success) {
        $(this).parent().find('.cache-updated').text(__('Not cached yet', 'woowgallery'));
        $(this).remove();
      }
    }).always(() => {
      $(this).prop('disabled', false);
    });
  });
});
