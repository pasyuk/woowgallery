/**
 * View gallery item
 */
(function($, _) {

  let woowgallery = window.WoowGalleryAdmin;

  woowgallery.mixins.push({
    data: function() {
      return {
        viewItem: false,
        viewItemCarousel: null
      };
    },
    computed: {
      viewItemIndex: function() {
        if (this.viewItem) {
          return _.findIndex(this.gallery_paged, {id: this.viewItem.id});
        }

        return -1;
      },
      // Edit next item.
      viewItemNext: function() {
        let index = this.viewItemIndex;
        if (index < 0 || (index + 1) >= this.gallery_paged.length) {
          return false;
        }

        return this.gallery_paged[index + 1];
      },

      // Edit previous item.
      viewItemPrev: function() {
        let index = this.viewItemIndex;
        if (index < 0 || (index - 1) < 0) {
          return false;
        }

        return this.gallery_paged[index - 1];
      }
    },
    watch: {
      viewItem: function(newEditItem, oldEditItem) {
        if (newEditItem) {
          if (!oldEditItem) {
            $('body').addClass('modal-open');
          }
          this.$nextTick(() => {
            this.viewItemCarouselInit();
          });
        }
        else {
          $('body').removeClass('modal-open');
          this.viewItemCarouselDestroy();
        }
      }
    },
    beforeDestroy() {
      this.$nextTick(() => {
        this.viewItemCarouselDestroy();
      });
    },
    methods: {
      // Trigger edit item modal window.
      viewItemSet: function(item, event) {
        if (!item || ['instagram'].indexOf(item.type) === false) {
          return false;
        }

        if (event) {
          event.preventDefault();
        }

        this.viewItem = _.find(this.gallery_paged, (media) => (media.id === item.id));
      },

      // Close edit item modal window.
      viewItemClose: function() {
        this.viewItem = false;
        this.viewItemCarouselDestroy();
      },

      viewItemCarouselInit: function() {
        this.viewItemCarouselDestroy();

        this.$nextTick(() => {
          const carousel = document.getElementById('carousel-' + this.viewItem.id);
          if (carousel) {
            var self = this;
            this.viewItemCarousel = new Swiper(carousel, {
              cssMode: true,
              navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
              },
              pagination: {
                el: '.swiper-pagination',
                clickable: true
              },
              on: {
                init: function() {
                  setTimeout(() => {
                    self.viewItemCarouselVideoPlayPause(this.activeIndex);
                  }, 0);
                },
                slideChange: function() {
                  self.viewItemCarouselVideoPlayPause(this.activeIndex);
                }
              }
            });
          }
        });
        this.$forceUpdate();
      },

      viewItemCarouselVideoPlayPause: function(activeIndex) {
        if (!this.viewItemCarousel) {
          return;
        }

        if (this.viewItemVideo) {
          this.viewItemVideo.pause();
        }
        this.viewItemVideo = null;
        let video = $('.swiper-slide', this.viewItemCarousel.$el).eq(activeIndex).find('video');
        if (video && video.length) {
          video[0].play();
          this.viewItemVideo = video[0];
        }
      },

      viewItemCarouselDestroy: function() {
        if (this.viewItemCarousel) {
          this.viewItemCarousel.destroy && this.viewItemCarousel.destroy();
          this.viewItemCarousel = null;
        }
      }
    }
  });

})(jQuery, _);
