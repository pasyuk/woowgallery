/**
 * Make gallery sortable
 */
(function($, _) {

  var woowgallery = window.WoowGalleryAdmin;

  Vue.directive('sortable', function(el, binding, vnode) {
    var options = binding.value || {};
    woowgallery.sortable = Sortable.create(el, options);
  });

  woowgallery.mixins.push({
    data: function() {
      return {
        sortableAnimationTime: 200,
        sortableGroup: false,
        sorting: false,
        sortingHash: _.now(),
        sortingPageChanged: false,
        sortingStartPage: 0,
        sortingElementIndex: 0,
        sortingElementID: 0
      };
    },
    computed: {
      sortableDisabled: function() {
        return (this.filter || 'custom' !== this.sortby);
      }
    },
    methods: {
      // Drag & Drop items
      sortable: function() {
        var app = this,
            timeoutStart = 0,
            timeoutEnd = 0,
            styles;

        return {
          group: {
            name: 'woowgallery',
            pull: true
          },  // or { name: "...", pull: [true, false, 'clone', array], put: [true, false, array] }
          sort: true,  // sorting inside list
          delay: 0, // time in milliseconds to define when the sorting should start
          delayOnTouchOnly: false, // only delay if user is using touch
          touchStartThreshold: 0, // px, how many pixels the point should move before cancelling a delayed drag event
          disabled: app.sortableDisabled, // Disables the sortable if set to true.
          store: null,  // @see Store
          animation: app.sortableAnimationTime,  // ms, animation speed moving items when sorting, `0` — without animation
          easing: 'cubic-bezier(1, 0, 0, 1)', // Easing for animation. Defaults to null. See https://easings.net/ for examples.
          handle: '.sort-handle',  // Drag handle selector within list items
          filter: function(event) {
            return app.sortableDisabled;
          },  // Selectors that do not lead to dragging (String or Function)
          preventOnFilter: false, // Call `event.preventDefault()` when triggered `filter`
          draggable: 'li',  // Specifies which items inside the element should be draggable

          dataIdAttr: 'data-id',

          ghostClass: 'sort-ghost',  // Class name for the drop placeholder
          chosenClass: 'sort-chosen',  // Class name for the chosen item
          dragClass: 'sort-drag',  // Class name for the dragging item

          swapThreshold: 1, // Threshold of the swap zone
          invertSwap: false, // Will always use inverted swap zone if set to true
          invertedSwapThreshold: 1, // Threshold of the inverted swap zone (will be set to swapThreshold value by default)
          // direction: 'horizontal', // Direction of Sortable (will be detected automatically if not given)

          forceFallback: true,  // ignore the HTML5 DnD behaviour and force the fallback to kick in

          fallbackClass: 'sortable-fallback',  // Class name for the cloned DOM Element when using forceFallback
          fallbackOnBody: 'parent',  // Appends the cloned DOM Element into the Document's Body
          fallbackTolerance: 2, // Specify in pixels how far the mouse should move before it's considered as a drag.

          dragoverBubble: false,
          removeCloneOnHide: true, // Remove the clone element when it is not showing, rather than just hiding it
          emptyInsertThreshold: 5, // px, distance mouse must be from empty sortable to insert drag element into it

          // Element is chosen
          onChoose: function(event) {
            app.sortableGroup = false;
          },

          // Element dragging started
          onStart: function(event) {
            var item = $(event.item),
                itemID = item.attr('data-id'),
                selectedQty = app.selected.length;

            app.sortingStartPage = app.page;
            app.sortingElementIndex = event.oldIndex + ((app.page - 1) * app.per_page);
            app.sortingElementID = itemID;

            clearTimeout(timeoutStart);
            timeoutStart = setTimeout(function() {
              app.sorting = true;

              // Check: Maybe it's a group sort?
              if (selectedQty > 1 && item.hasClass('selected')) {

                var parent = $(event.srcElement);

                // It's a group sort - do some animations
                app.sortableGroup = true;
                parent.addClass('sort-group');
                var css = '';
                css += 'li.sort-chosen .attachment-preview::after{content:\'' + selectedQty + '\'}';
                _.each(app.selected, function(id) {
                  if (id === itemID) {
                    return;
                  }
                  css += 'li[data-id="' + id + '"]{transform:scale(0.5)}';
                });
                styles = $('<style type="text/css" id="woowgallery-sort-group-styles">' + css + '</style>').appendTo(document.head);

              }

            }, app.sortableAnimationTime / 1.5);
          },

          // Element dragging ended
          onEnd: function(event) {
            clearTimeout(timeoutStart);

            var item = $(event.item),
                parent = $(event.srcElement),
                itemID = item.attr('data-id'),
                moved = [],
                gallery = JSON.parse(JSON.stringify(app.gallery)),
                oldIndex = app.sortingElementIndex,
                newIndex = event.newIndex + ((app.page - 1) * app.per_page);

            if (newIndex >= gallery.length) {
              newIndex = gallery.length - 1;
            }

            if (oldIndex !== newIndex) {
              if (app.sortingStartPage < app.page) {
                newIndex--;
              }
              gallery.splice(newIndex, 0, gallery.splice(oldIndex, 1)[0]);

              if (app.sortableGroup) {
                _.each(app.selected, function(id) {
                  if (id === itemID) {
                    return;
                  }
                  // Find index of selected item in the gallery
                  oldIndex = _.map(gallery, function(c) { return c.id; }).indexOf(id);
                  // Move selected item to the moved array
                  moved.push(gallery.splice(oldIndex, 1)[0]);
                });

                newIndex = 1 + _.map(gallery, function(c) { return c.id; }).indexOf(itemID);
                var i = moved.length;
                while (i--) {
                  gallery.splice(newIndex, 0, moved[i]);
                }
              }

              app.gallery = gallery;
            }

            clearTimeout(timeoutEnd);
            timeoutEnd = setTimeout(function() {
              app.sorting = false;
              if (app.sortableGroup) {
                parent.removeClass('sort-group');
                if (styles) {
                  styles.remove();
                }
              }
              app.sortableGroup = false;
            }, app.sortableAnimationTime);

            app.sortingPageChanged = false;
            app.sortingStartPage = 0;
            app.sortingElementIndex = 0;
            app.sortingElementID = 0;
            app.sortingHash = _.now();
          },

          // Element is dropped into the list from another list
          onAdd: function(event) {
            event.item.remove();
          }
          //
          // // Changed sorting within list
          // onUpdate: function(event) {
          //   console.log('onUpdate');
          // },
          //
          // // Called by any change to the list (add / update / remove)
          // onSort: function(event) {
          //   console.log('onSort');
          // },
          //
          // // Element is removed from the list into another list
          // onRemove: function(event) {
          //   console.log('onRemove');
          // },
          //
          // // Attempt to drag a filtered element
          // onFilter: function(event) {
          // },
          //
          // // Event when you move an item in the list or between lists
          // onMove: function(event, originalEvent) {
          //   // Example: http://jsbin.com/tuyafe/1/edit?js,output
          //   console.log('onMove', event.dragged); // dragged HTMLElement
          //   console.log('onMove', event.draggedRect); // TextRectangle {left, top, right и bottom}
          //   console.log('onMove', event.related); // HTMLElement on which have guided
          //   console.log('onMove', event.relatedRect); // TextRectangle
          //   console.log('onMove', originalEvent.clientY); // mouse position
          //   // return false; — for cancel
          // },
          //
          // // Called when creating a clone of element
          // onClone: function(event) {
          //   var origEl = event.item;
          //   var cloneEl = event.clone;
          //   console.log('onClone', origEl, cloneEl);
          // },
        };
      },

      sortableMoveToPage: function(page) {
        if (!this.sorting) {
          return false;
        }

        this.page = page;
        this.sortingPageChanged = true;
      }
    }
  });

})(jQuery, _);
