<section class="casesSection" id="communityCarousel">
  <div class="container-fluid px-4">
    <div class="sectionFrame mb-3">
      <div class="carouselHeader mb-3">
        <h2 class="sectionTitle h5 mb-0"><?= $t('cases.carouselTitle') ?></h2>
        <div class="d-flex gap-2">
          <button class="carouselNav btnCircle carouselPrev" aria-label="<?= $t('ui.previous') ?>"><span>&lsaquo;</span></button>
          <button class="carouselNav btnCircle carouselNext" aria-label="<?= $t('ui.next') ?>"><span>&rsaquo;</span></button>
        </div>
      </div>
      <div class="swiper carouselSwiper">
        <div class="swiper-wrapper" id="carouselSlides"></div>
      </div>
    </div>
  </div>
</section>
