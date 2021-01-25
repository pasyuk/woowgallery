(function($, _) {
  let woowgallery = window.WoowGalleryAdmin;
  woowgallery.views = woowgallery.views || {};
  woowgallery.models = woowgallery.models || {};
  woowgallery.frames = woowgallery.frames || {};
  woowgallery.actions = woowgallery.actions || {};

  $(function() {

    /**
     * Extend Media Frame Post to make media multiple select without Shift key
     * wp.media.view.MediaFrame.Post
     */
    let Select = wp.media.view.MediaFrame.Select;

    woowgallery.views.MediaFramePost = wp.media.view.MediaFrame.Post.extend({
      initialize: function() {
        _.defaults(this.options, {
          multiple: false,
          editing: false,
          state: 'insert',
          metadata: {}
        });

        this.woowgallery = true;

        // Call 'initialize' directly on the parent class.
        Select.prototype.initialize.apply(this, arguments);

      },

      /**
       * Create the default states.
       */
      createStates: function() {
        let options = this.options,
          l10n = $.extend({}, wp.media.view.l10n, woowgallery.l10n);

        this.states.add([
          // Main states.
          new wp.media.controller.Library({
            classes: 'media-modal woowgallery-media-modal',
            id: 'insert',
            title: l10n.insertMediaTitle,
            priority: 20,
            toolbar: 'main-insert',
            filterable: 'all',
            library: wp.media.query(options.library),
            multiple: 'add',
            editable: true,

            // If the user isn't allowed to edit fields,
            // can they still edit it locally?
            allowLocalEdits: false,

            // Show the attachment display settings.
            displaySettings: false,
            // Update user settings when users adjust the
            // attachment display settings.
            displayUserSettings: false,

            //searchable:         true,
            //sortable:           true,
            //autoSelect:         true,
            //describe:           true,
            //contentUserSetting: true,
            //menu:               'default',
            router: 'browse'
          }),

          // Embed states.
          // new wp.media.controller.Embed( { metadata: options.metadata } ),

          new wp.media.controller.EditImage({model: options.editImage})

        ]);

        // if ( wp.media.view.settings.post.featuredImageId ) {
        //     this.states.add( new wp.media.controller.FeaturedImage() );
        // }

      },

      bindHandlers: function() {
        let handlers;

        Select.prototype.bindHandlers.apply(this, arguments);

        this.on('activate', this.activate, this);

        this.on('toolbar:create:main-insert', this.createToolbar, this);
        this.on('toolbar:create:featured-image', this.featuredImageToolbar, this);
        this.on('toolbar:create:main-embed', this.mainEmbedToolbar, this);

        handlers = {
          menu: {
            'default': 'mainMenu'
          },

          content: {
            'embed': 'embedContent',
            'edit-image': 'editImageContent',
            'edit-selection': 'editSelectionContent'
          },

          toolbar: {
            'main-insert': 'mainInsertToolbar'
          }
        };

        _.each(handlers, (regionHandlers, region) => {
          _.each(regionHandlers, (callback, handler) => {
            this.on(region + ':render:' + handler, this[callback], this);
          });
        });
      }
    });

    /**
     * Extend AttachmentsBrowser View to add selection toggles
     * wp.media.view.AttachmentsBrowser
     */
    let AttachmentsBrowser = {
      createToolbar: wp.media.view.AttachmentsBrowser.prototype.createToolbar
    };
    _.extend(wp.media.view.AttachmentsBrowser.prototype, {
      createToolbar: function() {
        AttachmentsBrowser.createToolbar.apply(this, arguments);

        if (this.controller.woowgallery) {
          let hide_selected = !!woowgallery.l10n.selection_hide_selected;
          this.controller.$el.toggleClass('hide-selected', hide_selected);

          let selection = window.WoowGalleryAdmin.Gallery.media_ids;
          if (!selection || !selection.length) {
            return;
          }
          let self = this;
          let SelectSwitchers = wp.media.View.extend({
            className: 'woowgallery-select-switchers',
            template: wp.template('woowgallery-select-switchers'),
            events: {
              'change .woowgallery-selection-prepend input[type="checkbox"]': 'toggleSelectionPrepend',
              'change .woowgallery-selection-display input[type="checkbox"]': 'toggleSelectionDisplay'
            },
            toggleSelectionPrepend: function(e) {
              woowgallery.l10n.selection_prepend = $(e.target).prop('checked');
            },
            toggleSelectionDisplay: function(e) {
              let checked = $(e.target).prop('checked');
              self.controller.$el.toggleClass('hide-selected', checked);
              woowgallery.l10n.selection_hide_selected = checked;
              if (checked) {
                self.controller.content.get().$el.children('ul').trigger('scroll');
              }
            }
          });

          this.toolbar.set('selectAppendPrependSwitcher', new SelectSwitchers({
            controller: this.controller,
            priority: -70
          }).render());
        }
      }
    });

    /**
     * Extend Attachment View for WoowGallery
     * wp.media.view.Settings.Attachment
     */
    let Attachment = {
      render: wp.media.view.Attachment.prototype.render,
      toggleSelection: wp.media.view.Attachment.prototype.toggleSelection,
      // select: wp.media.view.Attachment.prototype.select,
      save: wp.media.view.Attachment.prototype.save
    };
    _.extend(wp.media.view.Attachment.prototype, {
      /**
       * @returns {wp.media.view.Attachment} Returns itself to allow chaining
       */
      render: function() {
        let vm = Attachment.render.apply(this, arguments);

        if (this.controller.woowgallery) {
          this.woowgallerySelect();
        }

        return vm;
      },
      woowgallerySelect: function() {
        let selection = window.WoowGalleryAdmin.Gallery.media_ids;
        this.options.woowgallerySelection = selection;

        let model_id = this.model.get('id');
        // Check if a selection exists and if model is in selection.
        if (!selection || (typeof model_id === 'undefined') || selection.indexOf(model_id.toString()) === -1) {
          return;
        }

        // Bail if the model is already selected.
        if (this.$el.hasClass('woowgallery-selected')) {
          return;
        }

        // Add 'selected' class to model, set aria-checked to true.
        this.$el.addClass('woowgallery-selected').attr('aria-checked', true);
      },
      /**
       * @param {Backbone.Model} model
       * @param {Backbone.Collection} collection
       */
      /*select: function(model, collection) {
        // Bail if the model is already woowgallery-selected.
        if (this.$el.hasClass('woowgallery-selected')) {
          return;
        }

        Attachment.select.apply(this, arguments);
      },*/
      ignoreWoowgallerySelection: function() {
        let selection = this.options.selection,
          woowgallerySelection = this.options.woowgallerySelection;

        if (woowgallerySelection.length) {
          _.each(woowgallerySelection, (id) => {
            let remove_model = selection.get(id);
            if (remove_model) {
              selection.remove(remove_model);
            }
          });
        }
      },
      /**
       * @param {Object} options
       */
      toggleSelection: function(options) {
        if (this.controller.woowgallery) {
          let collection = this.collection,
            selection = this.options.selection,
            model = this.model,
            method = options && options.method,
            single, models, singleIndex, modelIndex;

          if (!selection) {
            return;
          }

          single = selection.single();
          method = _.isUndefined(method) ? selection.multiple : method;

          // If the `method` is set to `between`, select all models that
          // exist between the current and the selected model.
          if ('between' === method && single && selection.multiple) {
            // If the models are the same, short-circuit.
            if (single === model) {
              return;
            }

            singleIndex = collection.indexOf(single);
            modelIndex = collection.indexOf(this.model);

            if (singleIndex < modelIndex) {
              models = collection.models.slice(singleIndex, modelIndex + 1);
            }
            else {
              models = collection.models.slice(modelIndex, singleIndex + 1);
            }

            selection.add(models);
            this.ignoreWoowgallerySelection();
            if (this.$el.hasClass('woowgallery-selected')) {
              selection.add(model);
            }
            selection.single(model);
            return;

            // If the `method` is set to `toggle`, just flip the selection
            // status, regardless of whether the model is the single model.
          }
          else if ('toggle' === method) {
            this.ignoreWoowgallerySelection();
            selection[this.selected() ? 'remove' : 'add'](model);
            selection.single(model);
            return;
          }
          else if ('add' === method) {
            this.ignoreWoowgallerySelection();
            selection.add(model);
            selection.single(model);
            return;
          }

        }

        Attachment.toggleSelection.apply(this, arguments);

      },
      /**
       * Pass all the arguments to the model's save method.
       *
       * Records the aggregate status of all save requests and updates the
       * view's classes accordingly.
       */
      save: function() {
        Attachment.save.apply(this, arguments);

        let save = this._save,
          requests;

        if (save) {
          requests = save.requests;
          requests.always(() => {
            $(document).trigger('woowgalleryUpdateData', [this.model]);
          });
        }
      }
    });

    /**
     * Extend Attachment Details for WoowGallery
     * wp.media.view.Settings.AttachmentDisplay
     */
    let Details = wp.media.view.Attachment.Details;
    wp.media.view.Attachment.Details = Details.extend({
      constructor: function(options) {
        this.woowgallery = false;
        if (options && options.controller && options.controller.woowgallery) {
          this.woowgallery = options.controller.woowgallery;
        }

        Details.prototype.constructor.apply(this, arguments);
      },
      deleteAttachment: function(event) {
        Details.prototype.deleteAttachment.apply(this, arguments);

        if (this.woowgallery) {
          this.woowgalleryDeleteAttachment(event);
        }
      },
      trashAttachment: function(event) {
        Details.prototype.trashAttachment.apply(this, arguments);

        if (this.woowgallery) {
          this.woowgalleryDeleteAttachment(event);
        }
      },
      woowgalleryDeleteAttachment: function(event) {
        $(document).trigger('woowgalleryDeleteMedia', [this.model.get('id')]);
      }
    });

    /**
     * WoowGallery Insert Media Frame
     * @var l10n object Localisation
     * @var selected array Selected media IDs
     * @returns woowgallery.frames.MediaFramePost
     */
    woowgallery.actions.MediaFramePost = function(selected, l10n) {

      l10n = $.extend({}, wp.media.view.l10n, woowgallery.l10n, l10n);

      let vm = this;
      this.selected = selected || [];

      // If the wp.media.frames.woowgallery instance already exists, reopen it
      if (woowgallery.frames.MediaFramePost) {
        woowgallery.frames.MediaFramePost.open();

        return woowgallery.frames.MediaFramePost;
      }
      else {
        // Create the wp.media.frames.woowgallery instance (one time)
        woowgallery.frames.MediaFramePost = new woowgallery.views.MediaFramePost({
          className: 'media-frame hide-menu woowgallery-media-frame',
          title: l10n.insertIntoPost,
          button: {
            text: l10n.insertIntoPost
          }
        });
      }

      woowgallery.frames.MediaFramePost.on('open', function() {
        // Re-render content and toolbar regions in case we changed gallery entries
        woowgallery.frames.MediaFramePost.content.render();
        woowgallery.frames.MediaFramePost.toolbar.render();

        // Get any previously selected images and reset them
        let selection = woowgallery.frames.MediaFramePost.state().get('selection');
        // If `selected` variable not empty, select media when the modal is opened
        // selection.reset(vm.selected);
        // Reset selection
        // selection.reset();

        // // If `selected` variable not empty, select media when the modal is opened
        if (vm.selected && vm.selected.length) {
          _.each(vm.selected, (id) => {
            selection.add([wp.media.attachment(id)]);
          });
        }
      });

      woowgallery.frames.MediaFramePost.on('menu:render:default', function(view) {
        Object.keys(view._views).forEach(function(name) {
          if ('insert' !== name) {
            view.unset(name);
          }
        });
      });

      // Insert into Gallery Button Clicked
      woowgallery.frames.MediaFramePost.on('insert', function(selection) {
        let media = [];
        // Iterate through selected items, building collection array
        selection.each(function(attachment) {
          // Add the attachment to the array
          media.push(attachment.toJSON());
        }, this);

        $(document).trigger('woowgalleryInsertMedia', [woowgallery.frames.MediaFramePost, media]);

        // Reset selected media after insert
        selection.reset();

      });

      // Open the media frame
      woowgallery.frames.MediaFramePost.open();

      return woowgallery.frames.MediaFramePost;

    };

  });

})(jQuery, _);
