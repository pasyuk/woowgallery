/**
 * Handles tabbed interfaces within WoowGallery:
 */

jQuery(function($) {

  // Define some general vars
  var woowgallery_tabs_nav = '.woowgallery-tabs-nav',     // Container of tab navigation items (typically an unordered list)
    woowgallery_tabs_hash = window.location.hash,
    woowgallery_tabs_current_tab = woowgallery_tabs_hash.replace('!', '').split('/')[0];

  // Update the form action to contain the selected tab as a hash in the URL
  // This means when the user saves their Gallery, they'll see the last selected tab 'open' on reload
  $(window).on('woowgallery_post_action', function() {
    var woowgallery_post_action = $('#post').attr('action');
    if (woowgallery_post_action) {
      // Remove any existing hash from the post action
      woowgallery_post_action = woowgallery_post_action.split('#')[0];

      // Append the selected tab as a hash to the post action
      $('#post').attr('action', woowgallery_post_action + window.location.hash);
    }
  });

  // Change tabs on click.
  // Tabs should be clickable elements, such as an anchor or label.
  $(woowgallery_tabs_nav).on('click', '.nav-tab, a', function(e) {

    // Prevent the default action
    e.preventDefault();

    // Get the clicked element and the nav tabs
    var woowgallery_tabs = $(this).closest(woowgallery_tabs_nav),
      woowgallery_tabs_section = $(woowgallery_tabs).data('container'),
      woowgallery_tab = ((typeof $(this).attr('href') !== 'undefined') ? $(this).attr('href') : $(this).data('tab'));

    // Don't do anything if we're clicking the already active tab.
    if ($(this).hasClass('woowgallery-active')) {
      return;
    }

    // Remove the active class from everything in this tab navigation and section
    $(woowgallery_tabs).find('.woowgallery-active').removeClass('woowgallery-active');
    $(woowgallery_tabs_section).find('div.woowgallery-active').removeClass('woowgallery-active');

    // Add the active class to the chosen tab and section
    $(this).addClass('woowgallery-active');
    $(woowgallery_tabs_section).find(woowgallery_tab).addClass('woowgallery-active');

    // Update the window URL to contain the selected tab as a hash in the URL.
    window.location.hash = woowgallery_tab.split('#').join('#!');
    // Update the form action to contain the selected tab as a hash in the URL
    $(window).trigger('woowgallery_post_action');

    // Code Editor refresh for misc tab.
    if (window.wgCodeEditor_custom_css && '#woowgallery-tab-misc' === woowgallery_tab) {
      window.wgCodeEditor_custom_css.codemirror.refresh();
    }
  });

  // If the URL contains a hash beginning with woowgallery-tab, mark that tab as open
  // and display that tab's panel.
  if (woowgallery_tabs_hash && woowgallery_tabs_hash.indexOf('woowgallery-tab-') >= 0) {
    // Find the tab panel that the tab corresponds to
    var woowgallery_tabs_section = $(woowgallery_tabs_current_tab).parent(),
      woowgallery_tab_nav = $(woowgallery_tabs_section).data('navigation');

    $(woowgallery_tab_nav).find('a[href="' + woowgallery_tabs_current_tab + '"]').trigger('click');

    // // Remove the active class from everything in this tab navigation and section
    // $(woowgallery_tab_nav).find('.woowgallery-active').removeClass('woowgallery-active');
    // $(woowgallery_tabs_section).find('div.woowgallery-active').removeClass('woowgallery-active');

    // // Add the active class to the chosen tab and section
    // $(woowgallery_tab_nav).find('a[href="' + woowgallery_tabs_current_tab + '"]').addClass('woowgallery-active');
    // $(woowgallery_tabs_current_tab).addClass('woowgallery-active');
    //
    // // Update the form action to contain the selected tab as a hash in the URL
    // $(window).trigger('woowgallery_post_action');
  }

});
