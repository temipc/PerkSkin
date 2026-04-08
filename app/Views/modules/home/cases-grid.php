<section class="casesSection" id="cases">
  <div class="container-fluid px-4">
    <div class="sectionFrame mb-3">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="sectionTitle h5 mb-0"><?= $t('cases.title') ?></h2>
        <div class="filters d-flex gap-2 align-items-center flex-wrap">
          <div class="prettySelect" data-target="priceFilter">
            <select id="priceFilter" class="nativeSelect">
              <option value="all"><?= $t('filters.priceAll') ?></option>
              <option value="low">$</option>
              <option value="mid">$$</option>
              <option value="high">$$$</option>
            </select>
            <span class="selectDisplay"></span>
            <span class="selectArrow"></span>
            <div class="dropdownPanel"></div>
          </div>
          <div class="prettySelect" data-target="tagFilter">
            <select id="tagFilter" class="nativeSelect">
              <option value="all"><?= $t('filters.tagsAll') ?></option>
              <option value="starter"><?= $t('tag.starter') ?></option>
              <option value="limited"><?= $t('tag.limited') ?></option>
              <option value="hot"><?= $t('tag.hot') ?></option>
            </select>
            <span class="selectDisplay"></span>
            <span class="selectArrow"></span>
            <div class="dropdownPanel"></div>
          </div>
        </div>
      </div>
      <div class="sectionRibbon"><span><?= $t('ribbon.gettingSerious') ?></span></div>
      <div class="row g-3" id="casesGrid"></div>
    </div>
  </div>
</section>
