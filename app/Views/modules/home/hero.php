<section class="heroBanner" id="events">
  <div class="container-fluid px-4">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="display-5 fw-bold mb-3"><?= $t('hero.title') ?></h1>
        <p class="lead mb-4"><?= $t('hero.subtitle') ?></p>
        <div class="d-flex gap-2">
          <a href="#cases" class="btn btnPrimary btn-lg"><?= $t('hero.ctaOpen') ?></a>
          <a href="#how" class="btn btn-outline-light btn-lg"><?= $t('hero.ctaLearn') ?></a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="swiper heroSwiper">
          <div class="swiper-wrapper">
            <div class="swiper-slide"><img class="img-fluid heroImage" src="/assets/images/hero-1.svg" alt="<?= $t('hero.bannerAlt') ?> 1"></div>
            <div class="swiper-slide"><img class="img-fluid heroImage" src="/assets/images/hero-2.svg" alt="<?= $t('hero.bannerAlt') ?> 2"></div>
            <div class="swiper-slide"><img class="img-fluid heroImage" src="/assets/images/hero-3.svg" alt="<?= $t('hero.bannerAlt') ?> 3"></div>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
    </div>
  </div>
</section>
