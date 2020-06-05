<template>
  <div class="pe-grid" :style="main_styles" @touchstart="touchAnimation(true)" @touchend="touchAnimation(false)">
    <div class="pe-item" v-for="item in content" :key="item.id" :data-wgid="item.id" :class="['pe-item-' + item.id]" ref="item">
      <v-lazy-image class="pe-item-thumb" v-if="item.thumb[0]" :src="item.thumb[0]" :alt="item.alt" @click="thumbClick(item)" @load="thumbLoaded(item.id)"/>
      <div class="pe-item-info" v-if="settings.show_info && (settings.show_title || settings.show_caption)">
        <div class="pe-item-title" v-if="settings.show_title" v-html="item.title"></div>
        <div class="pe-item-caption" v-if="settings.show_caption" v-html="item.caption"></div>
      </div>
    </div>
  </div>
</template>

<script>
  import Vue from 'vue';
  import {VLazyImagePlugin} from 'v-lazy-image';
  import './assets/jquery.proximity';

  Vue.use(VLazyImagePlugin);

  export default {
    props: ['appid'],
    data() {
      return {
        main_styles: {},
        settings: {},
        set: {
          int: {
            thumb_width: 200,
            thumb_height: 200,
            grid_gap: 0,
            border_radius: 0,
            border_radius_hover: 0,
            proximity_distance: 200
          },
          float: {
            min_scale: 1,
            max_scale: 1.2,
            min_opacity: 0.7,
            max_opacity: 0.95
          },
          bool: {
            show_info: true,
            show_title: true,
            show_caption: true
          },
          string: {
            grid_bg: 'rgba(255,255,255,0)',
            grid_bg_img: '',
            info_bg: 'rgba(0,0,0,0.8)',
            title_color: '#ffffff',
            caption_color: '#ffffff'
          }
        },
        content: [],
        touch_tick: 0
      };
    },
    computed: {},
    watch: {
      content: {
        deep: true,
        handler: function() {
          this.$nextTick(() => {
            this.init();
          });
        }
      }
    },
    mounted() {
      let settings = {...this.set.int, ...this.set.float, ...this.set.bool, ...this.set.string, ...JSON.parse(document.querySelector(`#${this.appid} .wg-json-settings`).text)};
      for (const [key, value] of Object.entries(settings)) {
        if (key in this.set.bool) {
          settings[key] = (!(!value || value === '0' || value === 'false' || value === 'off'));
        }
        else if (key in this.set.int) {
          settings[key] = parseInt(value);
        }
        else if (key in this.set.float) {
          settings[key] = parseFloat(value);
        }
      }
      this.settings = settings;
      this.content = JSON.parse(document.querySelector(`#${this.appid} .wg-json-content`).text);

      let img_w = this.settings.thumb_width,
        img_h = this.settings.thumb_height,
        img_n_w = this.settings.max_scale * img_w,
        img_n_h = this.settings.max_scale * img_h;

      this.main_styles = {
        '--grid_gap': settings.grid_gap + 'px',
        '--item_width': img_n_w + 'px',
        '--max_scale': settings.max_scale,
        '--item_padding_b': Math.ceil((img_n_h / img_n_w) * 100) + '%',
        '--thumb_opacity': settings.min_opacity,
        '--thumb_scale': 'scale(' + settings.min_scale + ') translateZ(0)',
        '--thumb_border_radius': settings.border_radius + 'px',
        '--grid_bg': settings.grid_bg,
        '--grid_bg_img': settings.grid_bg_img,
        '--info_bg': settings.info_bg,
        '--title_color': settings.title_color,
        '--caption_color': settings.caption_color
      };

      jQuery(window).on('resize.peGrid', this.infoPosition);
    },
    beforeDestroy() {
      jQuery(this.$refs.item).off('proximity.Photo');
      jQuery(window).off('resize.peGrid');
    },
    methods: {
      infoPosition() {
        let gridBox = this.$el.getBoundingClientRect(),
          items = jQuery(this.$refs.item);

        items.each(function() {
          let el = this,
            $info = el.querySelector('.pe-item-info');

          if($info) {
            let box = el.getBoundingClientRect(),
              to_left = (box.right + $info.offsetWidth) > gridBox.right,
              to_bottom = to_left && ((box.left - gridBox.left) < $info.offsetWidth);

            el.classList.toggle('to-left', to_left && !to_bottom);
            el.classList.toggle('to-bottom', to_bottom);
          }
        });
      },
      init() {
        let self = this,
          items = jQuery(this.$refs.item);

        this.infoPosition();

        items.off('proximity.Photo').on('proximity.Photo', {max: this.settings.proximity_distance, min: 0, throttle: 10, fireOutOfBounds: true}, function(event, proximity, distance) {
          let el = this,
            $thumb = el.querySelector('.pe-item-thumb');
          let scaleVal = proximity * (self.settings.max_scale - self.settings.min_scale) + self.settings.min_scale,
            borderRadius = proximity * (self.settings.border_radius_hover - self.settings.border_radius) + self.settings.border_radius,
            scaleExp = 'scale(' + scaleVal + ') translateZ(0)';

          el.classList.toggle('scale-max', (scaleVal === self.settings.max_scale));

          if (!$thumb) {
            el.classList.add('pe-item-ready');
          }

          if (!$thumb || !el.classList.contains('pe-item-ready')) {
            return;
          }
          jQuery($thumb).css({
            '-webkit-transform': scaleExp,
            '-moz-transform': scaleExp,
            '-o-transform': scaleExp,
            '-ms-transform': scaleExp,
            'transform': scaleExp,
            'opacity': (proximity * (self.settings.max_opacity - self.settings.min_opacity) + self.settings.min_opacity),
            'border-radius': (borderRadius < 0 ? borderRadius * (-1) : borderRadius) + 'px'
          });
        });
      },
      thumbClick(item) {
        jQuery(window).trigger('click.woowgalleryItem', [item, this]);
      },
      thumbLoaded(id) {
        setTimeout(() => {
          jQuery('.pe-item-' + id, this.$el).addClass('pe-item-ready');
        }, 400);
      },
      touchAnimation(enabled) {
        if (this.touch_tick) {
          clearTimeout(this.touch_tick);
        }
        if (enabled) {
          this.$el.classList.add('touch-animation');
        }
        else {
          this.touch_tick = setTimeout(() => {
            this.$el.classList.remove('touch-animation');
          }, 300);
        }
      }
    }
  };
</script>

<style lang="scss">
  @font-face {
    font-family: 'BebasNeueRegular';
    src: url('./assets/fonts/BebasNeue-webfont.eot');
    src: url('./assets/fonts/BebasNeue-webfont.eot?#iefix') format('embedded-opentype'),
    url('./assets/fonts/BebasNeue-webfont.woff') format('woff'),
    url('./assets/fonts/BebasNeue-webfont.ttf') format('truetype'),
    url('./assets/fonts/BebasNeue-webfont.svg#BebasNeueRegular') format('svg');
    font-weight: normal;
    font-style: normal;
  }

  .pe-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(var(--item_width), 1fr));
    grid-gap: var(--grid_gap);
    position: relative;
    box-sizing: border-box;

    &:before {
      content: "";
      display: block;
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      background: var(--grid_bg_img, transparent);
      background-color: var(--grid_bg_color);
    }

    &.touch-animation {
      .pe-item-thumb {
        transition: all 0.2s ease-in;
      }
    }

    * {
      box-sizing: border-box;
    }

    .pe-item {
      position: relative;
      z-index: 1;
      height: 0;
      padding-bottom: var(--item_padding_b);

      .pe-item-info {
        position: absolute;
        top: calc(-50% * (var(--max_scale) - 1));
        left: calc(100% + (50% * (var(--max_scale) - 1)));
        width: calc(200% + (2 * var(--grid_gap)) - (50% * (var(--max_scale) - 1)));
        height: calc(100% + (100% * (var(--max_scale) - 1)));
        padding: 10px;
        background: var(--info_bg);
        z-index: 1;
        text-align: left;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.1s ease-in-out;

        .pe-item-title {
          padding: 0;
          line-height: 20px;
          font-family: 'BebasNeueRegular', 'Arial Narrow', Arial, sans-serif;
          font-size: 22px;
          color: var(--title_color);
          margin: 0;

          + .pe-item-caption {
            margin-top: 10px;
            border-top: 1px solid var(--caption_color);
            padding-top: 10px;
          }
        }

        .pe-item-caption {
          font-size: 11px;
          font-style: italic;
          color: var(--caption_color);
        }
      }

      &.to-left {
        .pe-item-info {
          right: calc(100% + (50% * (var(--max_scale) - 1)));
          left: auto;
        }
      }

      &.to-bottom {
        .pe-item-info {
          left: calc(-50% * (var(--max_scale) - 1));;
          top: calc(100% + (50% * (var(--max_scale) - 1)));
          width: calc(100% + (100% * (var(--max_scale) - 1)));
          height: auto;
          min-height: calc(100% + (2 * var(--grid_gap)));
        }
      }

      &.pe-item-ready.scale-max {
        z-index: 2;

        &:hover {
          z-index: 3;

          .pe-item-info {
            opacity: 1;
            transition: opacity 0.4s ease-in-out 0.4s;
          }
        }
      }

      .pe-item-thumb {
        display: block;
        position: absolute;
        left: 0;
        top: 0;
        z-index: 2;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        border-radius: var(--thumb_border_radius);
        transform: var(--thumb_scale);
      }

      &.pe-item-ready {
        .pe-item-thumb {
          animation: .4s ease-in imgLoaded;
          opacity: var(--thumb_opacity);
        }
      }
    }

    @keyframes imgLoaded {
      from {
        opacity: 0;
      }
      to {
        opacity: var(--thumb_opacity);
      }
    }
  }
</style>
