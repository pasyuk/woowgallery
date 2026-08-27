/**
 * WoowGallery block editor integration.
 *
 * This file is the canonical source for ../blocks.build.js. It intentionally
 * uses WordPress globals so the plugin can ship without a JavaScript toolchain.
 */
(function (root, factory) {
  const api = factory(root);

  if (typeof module === "object" && module.exports) {
    module.exports = api;
    return;
  }

  root.WoowGalleryBlock = api;
  api.register(root.wp, root);
})(
  typeof window === "undefined" ? globalThis : window,
  function (defaultWindow) {
    "use strict";

    const BLOCK_NAME = "woowplugins/woowgallery";
    const CLASS_PREFIX = "woowgallery-";
    const FRAME_RUNTIME_KEY = "__woowgalleryBlockRuntime";

    function trace(error) {
      if (defaultWindow.console && defaultWindow.console.error) {
        defaultWindow.console.error(error);
      }
    }

    function createPreviewRuntime(rootElement, parentWindow) {
      if (!rootElement || !rootElement.ownerDocument) {
        throw new Error("WoowGallery preview root has no ownerDocument.");
      }

      const ownerDocument = rootElement.ownerDocument;
      const frameWindow = ownerDocument.defaultView;

      if (!frameWindow) {
        throw new Error("WoowGallery preview document has no defaultView.");
      }

      if (frameWindow[FRAME_RUNTIME_KEY]) {
        return frameWindow[FRAME_RUNTIME_KEY];
      }

      const sourceWindow = parentWindow || defaultWindow;
      const parentNamespace = sourceWindow.WoowGallery || {};
      const namespace = Object.assign({}, parentNamespace, {
        galleries: {},
        skins: {},
      });
      const runtime = {
        document: ownerDocument,
        namespace,
        scripts: new Map(),
        window: frameWindow,
      };

      frameWindow.WoowGallery = namespace;
      frameWindow[FRAME_RUNTIME_KEY] = runtime;

      return runtime;
    }

    function loadScript(runtime, url) {
      if (runtime.scripts.has(url)) {
        return runtime.scripts.get(url);
      }

      const promise = new Promise(function (resolve, reject) {
        const script = runtime.document.createElement("script");

        script.setAttribute("type", "text/javascript");
        script.async = false;
        script.src = url;
        script.onload = function () {
          resolve(script);
        };
        script.onerror = function () {
          runtime.scripts.delete(url);
          reject(
            new Error("Unable to load WoowGallery preview script: " + url),
          );
        };

        runtime.document.body.appendChild(script);
      });

      runtime.scripts.set(url, promise);
      return promise;
    }

    function loadScriptsInOrder(runtime, urls) {
      return urls.reduce(function (promise, url) {
        return promise.then(function () {
          return loadScript(runtime, url);
        });
      }, Promise.resolve());
    }

    async function initializePreview(rootElement, gallery, parentWindow) {
      if (!gallery || !gallery.skin || !gallery.skin.info) {
        throw new Error("WoowGallery preview is missing skin information.");
      }

      const scripts = gallery.skin.info.scripts;
      if (!Array.isArray(scripts) || !scripts.length) {
        throw new Error("WoowGallery preview skin has no scripts.");
      }

      const wrapper = rootElement.querySelector(".woowgallery-wrapper");
      if (!wrapper || !wrapper.id) {
        throw new Error("WoowGallery preview wrapper was not found.");
      }

      const runtime = createPreviewRuntime(rootElement, parentWindow);
      await loadScriptsInOrder(runtime, scripts);

      const manager = runtime.namespace.skins[gallery.skin.slug];
      if (!manager || typeof manager.searchAndInit !== "function") {
        throw new Error(
          "WoowGallery preview skin did not register: " + gallery.skin.slug,
        );
      }

      manager.searchAndInit(wrapper.id);

      let destroyed = false;
      return {
        destroy() {
          if (destroyed) {
            return;
          }
          destroyed = true;

          if (typeof manager.deleteGalleryById === "function") {
            manager.deleteGalleryById(wrapper.id);
          }
        },
        id: wrapper.id,
        manager,
        runtime,
      };
    }

    function serializeShortcode(attributes) {
      const gallery = attributes && attributes.gallery;
      if (!gallery || !gallery.id || !gallery.subtype) {
        return "";
      }

      return "[" + gallery.subtype + ' id="' + gallery.id + '"]';
    }

    function register(wp, blockWindow) {
      if (!wp || !wp.blocks || !wp.element || !wp.blockEditor) {
        return;
      }

      const editorWindow = blockWindow || defaultWindow;
      const settings = editorWindow.woowgalleryBlockSettings || {};
      const apiVersion = parseInt(settings.apiVersion, 10) || 1;
      const el = wp.element.createElement;
      const Component = wp.element.Component;
      const Fragment = wp.element.Fragment;
      const RawHTML = wp.element.RawHTML;
      const createRef = wp.element.createRef;
      const __ = wp.i18n.__;
      const _n = wp.i18n._n;
      const sprintf = wp.i18n.sprintf;
      const blockEditor = wp.blockEditor;
      const components = wp.components;

      function getAdmin() {
        return editorWindow.WoowGalleryAdmin;
      }

      function initBlocksUpdater() {
        const admin = getAdmin();
        if (!admin) {
          trace("Error: WoowGalleryAdmin is missing.");
          return;
        }

        if (admin.blocksUpdater) {
          return;
        }

        admin.blocks = {};
        admin.blocksUpdater = function (event) {
          Object.keys(admin.blocks).forEach(function (clientId) {
            const actions = admin.blocks[clientId];
            if (typeof actions[event.action] === "function") {
              actions[event.action](event);
            }
          });
        };
      }

      function GalleryInformer(props) {
        const thumbnail = props.thumb ? props.thumb[0] : undefined;
        let count;

        if (props.count !== undefined) {
          count =
            props.subtype === "woowgallery-album"
              ? sprintf(
                  _n(
                    "%d Gallery",
                    "%d Galleries",
                    parseInt(props.count, 10),
                    "woowgallery",
                  ),
                  props.count,
                )
              : sprintf(
                  _n(
                    "%d Media Item",
                    "%d Media Items",
                    parseInt(props.count, 10),
                    "woowgallery",
                  ),
                  props.count,
                );
        }

        return el(
          "div",
          { className: "pgc-rev-gallery-informer-view" },
          thumbnail && el("img", { alt: props.alt || "", src: thumbnail }),
          el(
            "div",
            { className: "pgc-rev-gallery-informer-title" },
            props.title,
          ),
          props.date &&
            el(
              "div",
              { className: "pgc-rev-gallery-informer-date" },
              new Date(props.date).toLocaleString(),
            ),
          el("div", { className: "pgc-rev-gallery-informer-count" }, count),
        );
      }

      function GalleryMenu(props) {
        return el(
          components.DropdownMenu,
          {
            className: CLASS_PREFIX + "menu-select-type",
            icon: "admin-appearance",
            label: __("Create New", "woowgallery"),
            popoverProps: { position: "bottom" },
            toggleProps: {
              className: "is-button is-default is-large is-secondary",
              "data-label": __("Create New", "woowgallery"),
            },
          },
          function (menuProps) {
            return el(
              Fragment,
              null,
              el(
                components.MenuGroup,
                null,
                el(
                  components.MenuItem,
                  {
                    onClick() {
                      menuProps.onClose();
                      props.editor.createNewGallery("woowgallery");
                    },
                  },
                  __("Create Gallery", "woowgallery"),
                ),
                el(
                  components.MenuItem,
                  {
                    onClick() {
                      menuProps.onClose();
                      props.editor.createNewGallery("woowgallery-dynamic");
                    },
                  },
                  __("Create Dynamic Gallery", "woowgallery"),
                ),
                el(
                  components.MenuItem,
                  {
                    onClick() {
                      menuProps.onClose();
                      props.editor.createNewGallery("woowgallery-album");
                    },
                  },
                  __("Create Album", "woowgallery"),
                ),
              ),
              props.includeSelect &&
                el(
                  components.MenuGroup,
                  null,
                  el(
                    components.MenuItem,
                    {
                      onClick() {
                        menuProps.onClose();
                        props.editor.selectGallery();
                      },
                    },
                    __("Select Gallery", "woowgallery"),
                  ),
                ),
            );
          },
        );
      }

      function EmptyGallery(props) {
        const error =
          props.attributes.galleryRawHtml.indexOf("Error:") === 0
            ? props.attributes.galleryRawHtml.slice(6)
            : "";

        return el(
          "div",
          { className: CLASS_PREFIX + "wrapper" },
          el(
            "div",
            { className: CLASS_PREFIX + "header" },
            __("WoowGallery", "woowgallery"),
          ),
          el(
            "div",
            { className: CLASS_PREFIX + "content" },
            error && el("div", { className: CLASS_PREFIX + "notic" }, error),
            el(GalleryMenu, { editor: props.editor }),
            el(
              components.Button,
              { onClick: props.editor.selectGallery, variant: "secondary" },
              __("Select Gallery", "woowgallery"),
            ),
          ),
        );
      }

      function Inspector(props) {
        const gallery = props.attributes.gallery;

        return el(
          blockEditor.InspectorControls,
          null,
          gallery
            ? el(
                components.PanelBody,
                { title: __("Gallery Details", "woowgallery") },
                el(
                  "div",
                  { className: CLASS_PREFIX + "content" },
                  el(
                    components.Button,
                    {
                      onClick: props.editor.selectGallery,
                      variant: "secondary",
                    },
                    __("Replace Gallery", "woowgallery"),
                  ),
                  el(GalleryInformer, gallery),
                  el(
                    components.Button,
                    { onClick: props.editor.editGallery, variant: "primary" },
                    __("Edit Gallery", "woowgallery"),
                  ),
                ),
              )
            : el(
                components.PanelBody,
                { title: __("Add Gallery", "woowgallery") },
                el(
                  "div",
                  { className: CLASS_PREFIX + "content inspector" },
                  el(GalleryMenu, {
                    editor: props.editor,
                    includeSelect: true,
                  }),
                ),
              ),
        );
      }

      function Toolbar(props) {
        const gallery = props.attributes.gallery;
        const ToolbarContainer = components.ToolbarGroup || components.Toolbar;

        return el(
          blockEditor.BlockControls,
          null,
          el(
            ToolbarContainer,
            { label: "WoowGallery" },
            el(components.ToolbarButton, {
              className: "wg-toolbar-button",
              icon: "screenoptions",
              label: __("Select / Replace WoowGallery", "woowgallery"),
              onClick: props.editor.selectGallery,
            }),
            gallery &&
              el(components.ToolbarButton, {
                icon: "edit",
                label: __("Edit Gallery", "woowgallery"),
                onClick: props.editor.editGallery,
              }),
            gallery &&
              el(components.ToolbarButton, {
                icon: "update",
                label: __("Refresh", "woowgallery"),
                onClick: props.editor.reloadGallery,
              }),
          ),
        );
      }

      class GalleryPreview extends Component {
        constructor(props) {
          super(props);
          this.preview = undefined;
          this.previewGeneration = 0;
          this.previewRef = createRef();
          this.state = { error: "" };
        }

        componentDidMount() {
          this.initialize();
        }

        componentDidUpdate(previousProps) {
          const previousGallery = previousProps.attributes.gallery;
          const gallery = this.props.attributes.gallery;
          if (
            previousProps.attributes.galleryRawHtml !==
              this.props.attributes.galleryRawHtml ||
            !previousGallery ||
            !gallery ||
            previousGallery.id !== gallery.id ||
            previousGallery.skin.slug !== gallery.skin.slug
          ) {
            this.initialize();
          }
        }

        componentWillUnmount() {
          this.previewGeneration += 1;
          if (this.preview) {
            this.preview.destroy();
          }
        }

        initialize() {
          const generation = ++this.previewGeneration;
          if (this.preview) {
            this.preview.destroy();
            this.preview = undefined;
          }

          initializePreview(
            this.previewRef.current,
            this.props.attributes.gallery,
            editorWindow,
          )
            .then((preview) => {
              if (generation !== this.previewGeneration) {
                preview.destroy();
                return;
              }

              this.preview = preview;
              if (this.state.error) {
                this.setState({ error: "" });
              }
            })
            .catch((error) => {
              trace(error);
              if (generation === this.previewGeneration) {
                this.setState({ error: error.message });
              }
            });
        }

        render() {
          return el(
            "div",
            { ref: this.previewRef },
            this.state.error &&
              el(
                "div",
                { className: CLASS_PREFIX + "notic" },
                this.state.error,
              ),
            this.props.attributes.galleryRawHtml &&
              el(
                RawHTML,
                { className: "woowgallery-block-raw" },
                this.props.attributes.galleryRawHtml,
              ),
          );
        }
      }

      class GalleryEditor extends Component {
        constructor(props) {
          super(props);
          this.actionCell = undefined;
          this.initedGallery = props.attributes.gallery === undefined;
          this.prepareNewGalleryId = undefined;

          [
            "createNewGallery",
            "editGallery",
            "prepareNewGalleryHandler",
            "reloadGallery",
            "requestGallery",
            "selectGallery",
            "selectGalleryHandler",
            "updatedHandler",
          ].forEach((method) => {
            this[method] = this[method].bind(this);
          });
        }

        componentDidMount() {
          const gallery = this.props.attributes.gallery;
          if (gallery) {
            this.requestGallery(gallery.subtype, gallery.id);
          } else {
            this.props.setAttributes({ loading: false });
          }

          initBlocksUpdater();
          const admin = getAdmin();
          if (admin) {
            admin.blocks = admin.blocks || {};
            admin.blocks[this.props.clientId] =
              admin.blocks[this.props.clientId] || {};
            this.actionCell = admin.blocks[this.props.clientId];
          }
        }

        componentWillUnmount() {
          const admin = getAdmin();
          if (admin && admin.blocks) {
            delete admin.blocks[this.props.clientId];
          }
        }

        reloadGallery() {
          const gallery = this.props.attributes.gallery;
          if (gallery) {
            this.requestGallery(gallery.subtype, gallery.id);
          }
        }

        createNewGallery(subtype) {
          const admin = getAdmin();
          if (!admin || !admin.ModalGallery) {
            trace("WoowGallery modal is missing.");
            return;
          }

          admin.ModalGallery.createModal(
            subtype,
            undefined,
            __("WoowGallery", "woowgallery"),
          );
          this.prepareNewGalleryId = undefined;
          if (this.actionCell) {
            this.actionCell.prepare = this.prepareNewGalleryHandler;
          }
        }

        prepareNewGalleryHandler(event) {
          this.prepareNewGalleryId = event.id;
          if (this.actionCell) {
            this.actionCell.updated = this.updatedHandler;
          }
        }

        updatedHandler(event) {
          const gallery = this.props.attributes.gallery;
          if (
            !gallery &&
            this.prepareNewGalleryId &&
            parseInt(this.prepareNewGalleryId, 10) === parseInt(event.id, 10)
          ) {
            this.requestGallery(event.type, event.id);
            return;
          }

          if (gallery && parseInt(gallery.id, 10) === parseInt(event.id, 10)) {
            this.reloadGallery();
          }
        }

        editGallery() {
          const gallery = this.props.attributes.gallery;
          const admin = getAdmin();
          if (!gallery || !admin || !admin.ModalGallery) {
            return;
          }

          admin.ModalGallery.createModal(
            gallery.subtype,
            gallery.id,
            __("WoowGallery", "woowgallery"),
          );
          if (this.actionCell) {
            this.actionCell.updated = this.updatedHandler;
          }
        }

        selectGallery() {
          const admin = getAdmin();
          if (!admin || !admin.ModalGallery) {
            trace("WoowGallery modal is missing.");
            return;
          }

          const gallery = this.props.attributes.gallery;
          admin.ModalGallery.openModal(
            "block",
            gallery ? gallery.subtype : "woowgallery",
            __("WoowGallery", "woowgallery"),
            this.selectGalleryHandler,
          );
        }

        selectGalleryHandler(gallery) {
          if (gallery && gallery.id) {
            this.props.setAttributes({ gallery });
            this.requestGallery(gallery.subtype, gallery.id);
          }
        }

        requestGallery(subtype, id) {
          const admin = getAdmin();
          if (!admin || !admin.post_types || !admin.post_types[subtype]) {
            this.handleGalleryError(
              new Error("WoowGallery REST settings are missing."),
            );
            return;
          }

          this.props.setAttributes({ loading: true });
          const postType = admin.post_types[subtype].base;
          const url = new URL(admin.wpApiRoot + "wp/v2/" + postType + "/" + id);
          url.searchParams.set("context", "view");
          url.searchParams.set("_fields", "wg_shortcode,wg_data");
          url.searchParams.set("_post_type", postType);

          wp.apiFetch({
            headers: { "X-WP-Nonce": admin.wpApiNonce },
            url: url.toString(),
          })
            .then((response) => {
              const gallery = response.wg_data;
              const galleryRawHtml = response.wg_shortcode;
              if (gallery && galleryRawHtml) {
                this.initedGallery = true;
                this.props.setAttributes({ gallery, galleryRawHtml });
              }
              this.prepareNewGalleryId = undefined;
              this.props.setAttributes({ loading: false });
            })
            .catch((error) => {
              this.handleGalleryError(error);
            });
        }

        handleGalleryError(error) {
          const gallery = this.props.attributes.gallery;
          this.props.setAttributes({
            gallery: undefined,
            galleryRawHtml:
              "Error:" +
              (gallery
                ? gallery.title +
                  " " +
                  __("gallery is not available!", "woowgallery")
                : error.message),
            loading: false,
          });
        }

        render() {
          const props = Object.assign({ editor: this }, this.props);
          const attributes = this.props.attributes;

          return el(
            Fragment,
            null,
            el(Inspector, props),
            el(Toolbar, props),
            !attributes.gallery && el(EmptyGallery, props),
            (!this.initedGallery || attributes.loading) &&
              el(
                "div",
                { className: "pgc-rev-preloader-view" },
                el(components.Spinner),
              ),
            attributes.loading === false &&
              this.initedGallery &&
              attributes.gallery &&
              attributes.galleryRawHtml &&
              el(GalleryPreview, props),
          );
        }
      }

      function Edit(props) {
        let blockProps;
        if (typeof blockEditor.useBlockProps === "function") {
          blockProps = blockEditor.useBlockProps({ className: "block" });
        } else {
          blockProps = {
            className:
              (props.className || "wp-block-woowplugins-woowgallery") +
              " block",
          };
        }

        return el("div", blockProps, el(GalleryEditor, props));
      }

      function Save(props) {
        return el(Fragment, null, serializeShortcode(props.attributes));
      }

      const icon = settings.iconUrl
        ? el("img", { alt: "", src: settings.iconUrl })
        : "format-gallery";

      wp.blocks.registerBlockType(BLOCK_NAME, {
        apiVersion,
        attributes: {
          galleryRawHtml: { default: "", type: "string" },
          gallery: { type: "object" },
          loading: { default: true, type: "boolean" },
        },
        category: "common",
        description: __(
          "Insert a WoowGallery into your content",
          "woowgallery",
        ),
        edit: Edit,
        icon: { src: icon },
        keywords: [
          __("woowgallery", "woowgallery"),
          __("gallery", "woowgallery"),
          __("album", "woowgallery"),
        ],
        save: Save,
        supports: { align: ["wide", "full"], html: false },
        title: __("WoowGallery", "woowgallery"),
      });
    }

    return {
      createPreviewRuntime,
      initializePreview,
      loadScriptsInOrder,
      register,
      serializeShortcode,
    };
  },
);
