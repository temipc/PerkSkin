<section class="casesSection" id="featuredCarousel">
  <div class="container-fluid px-4">
    <div class="sectionFrame mb-3">
      <div class="carouselHeader mb-3">
        <h2 class="sectionTitle h5 mb-0"><?= $t('featured.title') ?></h2>
        <div class="d-flex gap-2">
          <button class="carouselNav btnCircle featuredPrev" aria-label="<?= $t('ui.previous') ?>"><span>&lsaquo;</span></button>
          <button class="carouselNav btnCircle featuredNext" aria-label="<?= $t('ui.next') ?>"><span>&rsaquo;</span></button>
        </div>
      </div>
      <div class="swiper featuredSwiper">
        <div class="swiper-wrapper" id="featuredSlides"></div>
      </div>
    </div>
  </div>
</section>
