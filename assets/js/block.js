/**
 * Gutenberg Block Editor script
 */
(function(blocks, compose, editor, components, i18n, element, _) {
  let el = element.createElement;
  let registerBlockType = blocks.registerBlockType;
  let withState = compose.withState;
  let BlockControls = window.wp.BlockControls || editor.BlockControls;
  let InspectorControls = window.wp.InspectorControls || editor.InspectorControls;
  let TextControl = components.TextControl;
  let SelectControl = components.SelectControl;
  let ServerSideRender = window.wp.ServerSideRender || components.ServerSideRender;

  const woowgalleryIcon = el('svg',
    {
      width: 24,
      height: 24,
      viewBox: '0 0 24 24'
    },
    el('image',
      {
        width: '24px',
        height: '24px',
        href: 'data:image/png;base64,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 RK5CYII='
      }
    )
  );

  registerBlockType('woowplugins/woowgallery', {
    title: i18n.__('WoowGallery', 'wgtd'),
    description: i18n.__('Insert a WoowGallery into your content', 'wgtd'),
    category: 'common',
    keywords: [
      i18n.__('woowgallery', 'wgtd'),
      i18n.__('gallery', 'wgtd'),
      i18n.__('album', 'wgtd')
    ],
    supports: {
      align: true,
      multiple: true,
      html: false
    },
    icon: woowgalleryIcon,
    attributes: {
      id: {type: 'string', default: ''},
      align: {type: 'string', default: 'center'},
      posttype: {type: 'string', default: 'woowgallery'},
      width: {
        type: 'object',
        default: {value: 100, unit: '%'}
      }
    },
    edit: withState({reload: 0})(function(props) {
        let atts = props.attributes;
        let modal_posttype = atts.posttype;
        let modal_title, details_title, edit_label;
        if ('woowgallery-dynamic' === modal_posttype) {
          modal_title = i18n.__('WoowGallery Dynamic Galleries', 'wgtd');
          details_title = i18n.__('Dynamic Gallery Details', 'wgtd');
          edit_label = i18n.__('Edit Dynamic Gallery', 'wgtd');
        }
        else if ('woowgallery-album' === modal_posttype) {
          modal_title = i18n.__('WoowGallery Albums', 'wgtd');
          details_title = i18n.__('Album Details', 'wgtd');
          edit_label = i18n.__('Edit Album', 'wgtd');
        }
        else {
          modal_title = i18n.__('WoowGallery Galleries', 'wgtd');
          details_title = i18n.__('Gallery Details', 'wgtd');
          edit_label = i18n.__('Edit Gallery', 'wgtd');
        }

        let globalProps = function() {
          window.WoowGalleryAdmin.blockProps = props;
        };

        let gallerySelect, galleryDetails, galleryRender;
        if (!atts.id) {
          gallerySelect = el('div',
            {
              className: 'woowgallery-select-block'
            },
            el(components.Button,
              {
                className: 'button woowgallery-modal-button',
                title: modal_title,
                'data-modal': 'shortcode',
                'data-posttype': modal_posttype,
                onClick: globalProps
              },
              [
                el('span',
                  {
                    className: 'dashicons dashicons-screenoptions'
                  }
                ),
                i18n.__('Select Gallery', 'wgtd')
              ]
            )
          );

          galleryRender = el('div',
            {
              className: props.className
            },
            el('div',
              {
                className: 'woowgallery-block-edit'
              },
              [
                el('div',
                  {
                    className: 'woowgallery-block-edit-header'
                  },
                  'WoowGallery'
                ),
                el(components.Button,
                  {
                    className: 'button woowgallery-modal-button',
                    title: modal_title,
                    'data-modal': 'shortcode',
                    'data-posttype': modal_posttype,
                    onClick: globalProps
                  },
                  [
                    el('span',
                      {
                        className: 'dashicons dashicons-screenoptions'
                      }
                    ),
                    i18n.__('Select Gallery', 'wgtd')
                  ]
                )
              ]
            )
          );
        }
        else {
          if (!props.gallery || _.toString(props.gallery.id) !== _.toString(atts.id)) {
            jQuery.ajax({
              url: window.WoowGalleryAdmin.wpApiRoot + 'wp/v2/' + window.WoowGalleryAdmin.post_types[atts.posttype].base + '/' + atts.id,
              method: 'GET',
              beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', window.WoowGalleryAdmin.wpApiNonce);
              },
              data: {
                'context': 'view',
                '_fields': 'wg_data',
                '_post_type': atts.posttype
              }
            }).done((data, status, xhr) => {
              if ('success' === status && data.wg_data) {
                props.setState({gallery: data.wg_data});
                atts.id = data.wg_data.id;
              }
              else {
                atts.id = '';
              }
            });
          }
          else {
            galleryDetails = el('div',
              {
                className: 'woowgallery-details'
              },
              [
                props.gallery.thumb[0] ? (
                  el('img',
                    {
                      src: props.gallery.thumb[0]
                    }
                  )
                ) : '',
                el('div',
                  {
                    className: 'wg-title'
                  },
                  props.gallery.title
                ),
                el('div',
                  {
                    className: 'wg-date'
                  },
                  (new Date(props.gallery.date)).toLocaleString()
                ),
                props.gallery.count ? el('div',
                  {
                    className: 'wg-count'
                  },
                  'woowgallery-album' === atts.posttype ?
                    i18n.sprintf(i18n._n('%d Gallery', '%d Galleries', parseInt(props.gallery.count, 10), 'wgtd'), props.gallery.count)
                    :
                    i18n.sprintf(i18n._n('%d Media Item', '%d Media Items', parseInt(props.gallery.count, 10), 'wgtd'), props.gallery.count)
                ) : '',
                props.gallery.edit_link ?
                  el('div',
                    {
                      className: 'wg-edit'
                    },
                    el('a',
                      {
                        className: 'button button-primary woowgallery-edit-button',
                        href: props.gallery.edit_link,
                        target: '_blank'
                      },
                      [
                        el('span',
                          {
                            className: 'dashicons dashicons-edit'
                          }
                        ),
                        edit_label
                      ]
                    )
                  ) : ''
              ]
            );
          }

          gallerySelect = el('div',
            {
              className: 'woowgallery-select-block'
            },
            [
              el(components.Button,
                {
                  className: 'button woowgallery-modal-button',
                  title: modal_title,
                  'data-modal': 'shortcode',
                  'data-posttype': modal_posttype,
                  onClick: globalProps
                },
                [
                  el('span',
                    {
                      className: 'dashicons dashicons-screenoptions'
                    }
                  ),
                  i18n.__('Replace Gallery', 'wgtd')
                ]
              ),
              galleryDetails,
              el('div',
                {
                  className: 'woowgallery-flexblock'
                },
                [
                  el(TextControl,
                    {
                      type: 'number',
                      label: i18n.__('Gallery Width', 'wgtd'),
                      value: atts.width.value,
                      onChange: function(newWidth) {
                        props.setAttributes({width: {value: newWidth, unit: atts.width.unit}});
                      }
                    }
                  ),
                  el(SelectControl,
                    {
                      label: i18n.__('Unit', 'wgtd'),
                      value: atts.width.unit,
                      options: [
                        {label: '%', value: '%'},
                        {label: 'px', value: 'px'},
                        {label: 'vw', value: 'vw'}
                      ],
                      onChange: function(newWidthUnit) {
                        props.setAttributes({width: {value: atts.width.value, unit: newWidthUnit}});
                      }
                    }
                  )
                ]
              )
            ]
          );

          galleryRender = el(
            'div',
            {
              className: 'align' + atts.align,
              style: {width: atts.width.value + atts.width.unit}
            },
            el(ServerSideRender,
              {
                block: 'woowplugins/woowgallery',
                attributes: atts,
                reload: props.reload
              }
            )
          );
        }

        return [
          el(BlockControls,
            {key: 'woowgallery-controls'},
            el('div',
              {
                className: 'components-toolbar'
              },
              el(components.IconButton,
                {
                  icon: 'screenoptions',
                  label: i18n.__('Select / Replace WoowGallery', 'wgtd'),
                  className: 'woowgallery-modal-button',
                  title: modal_title,
                  'data-modal': 'shortcode',
                  'data-posttype': modal_posttype,
                  onClick: globalProps
                }
              ),
              atts.id ?
                el(components.IconButton,
                  {
                    icon: 'edit',
                    label: edit_label,
                    onClick: function() {
                      window.open(props.gallery.edit_link, '_blank');
                    }
                  }
                ) : '',
              atts.id ?
                el(components.IconButton,
                  {
                    icon: 'update',
                    label: i18n.__('Refresh', 'wgtd'),
                    onClick: function() {
                      props.setState({reload: Date.now()});
                    }
                  }
                ) : ''
            )
          ),

          el(InspectorControls,
            {key: 'woowgallery-inspector'},
            el(components.PanelBody,
              {
                title: details_title,
                className: 'block-content',
                initialOpen: true
              },
              gallerySelect
            )
          ),

          galleryRender

        ];
      }
    ),
    save: function(props) {
      let attributes = props.attributes;
      let shortcode, id, atts = '';
      id = `id="${attributes.id}"`;
      if ('100' !== attributes.width.value || '%' !== attributes.width.unit) {
        atts += ` width="${attributes.width.value}${attributes.width.unit}"`;
        // atts += ' width="' + attributes.width + '"';
        // atts += ' width_unit="' + attributes.width_unit + '"';
      }
      // if ('center' !== attributes.align) {
      atts += ` align="${attributes.align}"`;
      // }
      shortcode = `[${attributes.posttype} ${id}${atts}]`;

      return el(
        'div',
        {
          className: 'align' + attributes.align,
          style: {width: attributes.width.value + attributes.width.unit}
        },
        shortcode
      );
    }
  });

})(
  window.wp.blocks,
  window.wp.compose,
  window.wp.editor,
  window.wp.components,
  window.wp.i18n,
  window.wp.element,
  _
);
