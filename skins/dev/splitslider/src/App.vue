<template>
  <div class="woowgallery-splitslider" :style="main_styles">
    <slot name="before"></slot>
    <div class="wg--main_container" :class="['swiper-container-' + swiperSettings.direction]">

      <div id="wg-ss1" class="swiper-container wg-ss1" ref="wgSwiper1">
        <div class="swiper-wrapper">
          <div class="swiper-slide" v-for="(slide, i) in slidesEven" :key="i">
            <div class="wg--slide_image"><img v-if="slide.image[0]" :data-src="slide.image[0]" :alt="slide.alt" class="swiper-lazy"/>
              <div v-if="slide.image[0]" class="swiper-lazy-preloader"></div>
            </div>
            <div class="wg--slide_info">
              <div v-if="settings.show_title" class="wg--title">{{ slide.title }}</div>
              <div v-if="settings.show_caption" class="wg--caption">
                <div class="wg--caption_inner">{{ slide.caption }}</div>
              </div>
              <div v-if="settings.show_link && (slide.link.url || slide.type === 'post')" class="wg--slide_button">
                <a :href="slide.type === 'post'? slide.src : slide.link.url" :target="slide.link.target">{{ slide.link.text || settings.default_link_text }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="wg-ss2" class="swiper-container wg-ss2" ref="wgSwiper2">
        <div class="swiper-wrapper">
          <div class="swiper-slide" v-for="(slide, i) in slidesOdd" :key="i">
            <div class="wg--slide_image"><img v-if="slide.image[0]" :data-src="slide.image[0]" :alt="slide.alt" class="swiper-lazy"/>
              <div v-if="slide.image[0]" class="swiper-lazy-preloader"></div>
            </div>
            <div class="wg--slide_info">
              <div v-if="settings.show_title" class="wg--title">{{ slide.title }}</div>
              <div v-if="settings.show_caption" class="wg--caption">
                <div class="wg--caption_inner">{{ slide.caption }}</div>
              </div>
              <div v-if="settings.show_link && (slide.link.url || slide.type === 'post')" class="wg--slide_button">
                <a :href="slide.type === 'post'? slide.src : slide.link.url" :target="slide.link.target">{{ slide.link.text || settings.default_link_text }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Optional controls -->
      <div class="swiper-pagination"></div>
      <!-- <div class="swiper-button-prev" ref="wgPrev"></div> -->
      <!-- <div class="swiper-button-next" ref="wgNext"></div> -->
    </div>
    <slot name="after"></slot>
  </div>
</template>

<script>
  // require sources
  import Swiper from 'swiper/js/swiper.js';

  let swiperSettings = {
    init: false,
    // initialSlide: 0,
    direction: 'vertical',
    speed: 1000,
    // autoHeight: false,
    // effect: 'slide',
    // spaceBetween: 0,
    // slidesPerView: 1,
    // slidesPerColumn: 1,
    // slidesPerColumnFill: 'column',
    // slidesPerGroup: 1,
    // centeredSlides: false,
    // centeredSlidesBounds: false,
    // slidesOffsetBefore: 0,
    // slidesOffsetAfter: 0,
    // grabCursor: false,
    // touchEventsTarget: 'wrapper',
    // touchRatio: 1,
    // simulateTouch: true,
    // freeMode: false,
    // freeModeSticky: true,
    // watchSlidesProgress: false,
    watchSlidesVisibility: false,
    // preloadImages: true,
    // loop: false,
    // loopAdditionalSlides: 0,
    // loopedSlides: null,
    // loopFillGroupWithBlank: false,
    // Responsive breakpoints
    // breakpoints: {
    //   // when window width is >= 320px
    //   320: {
    //     slidesPerView: 2,
    //     spaceBetween: 20
    //   },
    //   // when window width is >= 480px
    //   480: {
    //     slidesPerView: 3,
    //     spaceBetween: 30
    //   },
    //   // when window width is >= 640px
    //   640: {
    //     slidesPerView: 4,
    //     spaceBetween: 40
    //   }
    // },
    navigation: false,
    // navigation: {
    //   nextEl: '.swiper-button-next',
    //   prevEl: '.swiper-button-prev',
    //   hideOnClick: false
    // },
    pagination: false,
    // pagination: {
    //   el: '.swiper-pagination',
    //   type: 'bullets',
    //   dynamicBullets: false,
    //   dynamicMainBullets: 1,
    //   hideOnClick: true,
    //   clickable: true,
    //   progressbarOpposite: false
    // },
    // scrollbar: {
    //   el: null, //'.swiper-scrollbar',
    //   hide: true,
    //   draggable: false
    // },
    autoplay: false,
    // autoplay: {
    //   delay: 5000,
    //   stopOnLastSlide: false,
    //   disableOnInteraction: true
    // },
    // lazy: false,
    lazy: {
      loadPrevNext: true,
      loadPrevNextAmount: 4
    },
    // fadeEffect: {
    //   crossFade: true
    // },
    // coverflowEffect: {
    //   slideShadows: true,
    //   rotate: 50,
    // },
    // flipEffect: {
    //   slideShadows: true,
    //   limitRotation: true,
    // },
    // cubeEffect: {
    //   slideShadows: true,
    //   shadow: true,
    //   shadowOffset: 20,
    // },
    zoom: {
      maxRatio: 3
    },
    keyboard: {
      enabled: false,
      onlyInViewport: true
    },
    mousewheel: false,
    // mousewheel: {
    //   forceToAxis: true,
    //   releaseOnEdges: true,
    //   invert: false,
    //   sensitivity: 1,
    //   eventsTarged: 'container'
    // },
    // virtual: false,
    hashNavigation: false,
    // hashNavigation: {
    //   replaceState: true,
    // },
    history: false,
    // history: {...},
    controller: {
      control: null, // [Swiper Instance],
      inverse: true,
      by: 'slide'
    }
    // a11y: {
    //   prevSlideMessage: 'Previous slide',
    //   nextSlideMessage: 'Next slide',
    // },
  };

  export default {
    props: ['appid'],
    data() {
      return {
        main_styles: {},
        swiper1: null,
        swiper2: null,
        settings: {
          base_width: 2,
          base_height: 1,
          min_height: 250,
          min_height_dimensions: 'px',
          show_title: true,
          show_caption: true,
          show_link: true,
          default_link_text: 'more',
          autoplay: false,
          autoplay_delay: 5000,
          speed: 1000,
          title_color: '#ffffff',
          caption_color: '#ffffff',
          button_bg: '#ffffff',
          button_bg_hover: 'rgba(255,255,255,0)',
          button_color: '#000000',
          button_color_hover: '#ffffff',
          preloader_color: '#000000',
          pagination_color: '#ffffff'
        },
        slides: [],
        // virtual data
        // virtualDataEven: {
        //   slides: []
        // },
        // virtualDataOdd: {
        //   slides: []
        // },
        swiperSettings: swiperSettings
      };
    },
    computed: {
      slidesEven() {
        return this.slides.filter((a, i) => (i % 2 === 0));
      },
      slidesOdd() {
        let slides = this.slides.filter((a, i) => (i % 2 === 1));
        const slides_length = this.slides.length;
        if (slides_length % 2 === 1) {
          slides.push(this.slides[slides_length - 2]);
        }
        return slides.reverse();
      }
    },
    watch: {
      slides: {
        deep: true,
        handler: 'initSplitSlider'
      }
    },
    mounted() {
      this.slides = JSON.parse(document.querySelector(`#${this.appid} .wg-json-content`).text);
      this.settings = {...this.settings, ...JSON.parse(document.querySelector(`#${this.appid} .wg-json-settings`).text)};
      this.swiperSettings.speed = +this.settings.speed;

      this.main_styles['--ratio'] = Math.ceil((this.settings.base_height / this.settings.base_width) * 100) + '%';
      this.main_styles['--min_height'] = this.settings.min_height + this.settings.min_height_dimensions;
      this.main_styles['--title_color'] = this.settings.title_color;
      this.main_styles['--caption_color'] = this.settings.caption_color;
      this.main_styles['--button_bg'] = this.settings.button_bg;
      this.main_styles['--button_bg_hover'] = this.settings.button_bg_hover;
      this.main_styles['--button_color'] = this.settings.button_color;
      this.main_styles['--button_color_hover'] = this.settings.button_color_hover;
      this.main_styles['--swiper-theme-color'] = this.settings.preloader_color;
      this.main_styles['--pagination_color'] = this.settings.pagination_color;
    },
    methods: {
      initSplitSlider() {
        let settings1 = {
          pagination: {
            el: this.$el.querySelector('.swiper-pagination'),
            type: 'bullets',
            dynamicBullets: true,
            dynamicMainBullets: 3,
            hideOnClick: false,
            clickable: true,
            progressbarOpposite: false
          },
          mousewheel: {
            eventsTarged: this.$el
          }
        };

        if (this.settings.autoplay) {
          settings1.autoplay = {
            delay: +this.settings.autoplay_delay
          };
        }

        this.swiper1 = new Swiper(this.$refs.wgSwiper1, {...this.swiperSettings, ...settings1});

        let settings2 = {
          initialSlide: (this.slidesOdd.length - 1)
          // virtual: {
          //   slides: self.slidesOdd,
          //   renderExternal(data) {
          //     self.virtualDataOdd = data;
          //   },
          //   addSlidesBefore: 4,
          //   addSlidesAfter: 4,
          // }
        };
        this.swiper2 = new Swiper(this.$refs.wgSwiper2, {...this.swiperSettings, ...settings2});

        this.swiper2.controller.control = this.swiper1;
        this.swiper1.controller.control = this.swiper2;

        this.swiper2.on('sliderMove', () => {
          if (this.swiper1.autoplay.running) {
            this.swiper1.autoplay.stop();
          }
        });

        this.swiper2.init();
        this.swiper1.init();

        this.$nextTick(() => {
          // this.swiper2.update();
          // this.swiper1.update();
          window.dispatchEvent(new Event('resize'));
          this.swiper1.keyboard.enable();
          if (this.settings.autoplay) {
            this.swiper1.autoplay.start();
          }
        });
      }
    }
  };
</script>

<style lang="scss">
  /*@import "~swiper/css/swiper.min.css";*/
  @import "~swiper/swiper";
  @import "~swiper/components/controller/controller";
  @import "~swiper/components/lazy/lazy";
  @import "~swiper/components/pagination/pagination";

  @mixin fluid-type($properties, $min-vw, $max-vw, $min-value, $max-value) {
    @each $property in $properties {
      #{$property}: $min-value;
    }

    @media (min-width: $min-vw) {
      @each $property in $properties {
        #{$property}: calc(#{$min-value} + #{strip-unit($max-value - $min-value)} * (100vw - #{$min-vw}) / #{strip-unit($max-vw - $min-vw)});
      }
    }

    @media (min-width: $max-vw) {
      @each $property in $properties {
        #{$property}: $max-value;
      }
    }
  }

  @function strip-unit($number) {
    @if type-of($number) == "number" and not unitless($number) {
      @return $number / ($number * 0 + 1);
    }

    @return $number;
  }

  body {
    margin: 0;
  }

  .woowgallery-splitslider {
    position: relative;
    width: 100%;
    /*height: 0;*/
    min-height: var(--min_height, 250px);
    padding-bottom: var(--ratio, 50%);
    box-sizing: border-box;

    * {
      box-sizing: border-box;
    }

    .swiper-container-vertical > .swiper-pagination-bullets.swiper-pagination-bullets-dynamic {
      width: 12px;
    }

    span.swiper-pagination-bullet {
      width: 12px;
      height: 12px;
      border: 1px solid var(--pagination_color, #fff);
      background: transparent;
      box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.3);
      opacity: 1;

      &, &:active, &:focus {
        outline: none;
      }

      &.swiper-pagination-bullet-active {
        background-color: var(--pagination_color, #fff);
      }
    }
  }

  .wg--main_container {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    display: flex;

    .swiper-container {
      width: 100%;
      height: 100%;
      flex: 1 1 50%;
    }

    .swiper-slide {

      &::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.05);
        transition: all 0.6s ease-in-out 0s;
      }

      &:hover {

        &::after {
          background-color: rgba(0, 0, 0, 0.3);
        }

        .wg--title::after {
          transform: scale(1, 1);
        }
      }
    }

    .wg--slide_image {
      position: absolute;
      height: 100%;
      width: 100%;
      left: 0;
      top: 0;

      &:empty::after {
        content: '';
        font-size: 40px;
        color: #fff;
        background: #eee;
        display: flex;
        height: 100%;
        align-items: center;
        justify-content: center;
      }

      img {
        margin: 0;
        border: none;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }

    .wg--slide_info {
      position: absolute;
      left: 0;
      top: 50%;
      width: 100%;
      padding: 0 18%;
      background: transparent;
      text-align: center;
      transform: translateY(-50%);
      z-index: 11;
      color: var(--title_color, #fff);

      .wg--title {
        display: inline-block;
        padding: 0;
        font-size: 30px;
        letter-spacing: 0.1em;
        line-height: 1;
        text-transform: uppercase;

        &::after {
          content: "";
          position: relative;
          display: block;
          margin: 20px auto;
          height: 2px;
          background: var(--title_color, #fff);;
          transform: scale(1.3, 1);
          transition: all 0.3s ease-in-out 0s;
        }

        &:empty {
          display: none;
        }
      }

      .wg--caption {
        color: var(--caption_color, #fff);
      }

      .wg--caption_inner {
        font-style: italic;
        font-size: 16px;
        line-height: 1.4;

        &:empty {
          display: none;
        }
      }

      .wg--slide_button a {
        text-transform: uppercase;
        letter-spacing: 0.1em;
        display: inline-block;
        padding: 10px 20px;
        margin: 30px 0;
        font-style: normal;
        border: 1px solid var(--button_bg, #fff);
        background-color: var(--button_bg, #fff);
        color: var(--button_color, #000);
        text-decoration: none;
        font-size: 13px;
        transition: all .3s ease-in-out;

        &:hover {
          text-decoration: none;
          background-color: var(--button_bg_hover, transparent);
          border-color: var(--button_color_hover, #fff);
          color: var(--button_color_hover, #fff);
        }
      }
    }
  }

  /*.wg-ss2 {*/
  /*  transform: rotate(180deg) translateZ(0);*/
  /*  backface-visibility: hidden;*/

  /*  .swiper-slide {*/
  /*    transform: rotate(180deg) translateZ(0);*/
  /*  }*/
  /*}*/

  .slide-leave-active {
    transition: opacity 1s ease;
    opacity: 0;
    animation: slide-out 1s ease-out forwards;
  }

  .slide-leave {
    opacity: 1;
    transform: translateX(0);
  }

  .slide-enter-active {
    animation: slide-in 1s ease-out forwards;
  }

  @keyframes slide-out {
    0% {
      transform: translateY(0);
    }
    100% {
      transform: translateY(-30px);
    }
  }

  @keyframes slide-in {
    0% {
      transform: translateY(-30px);
    }
    100% {
      transform: translateY(0);
    }
  }
</style>
