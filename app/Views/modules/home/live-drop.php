<section class="liveDrop" id="liveDrop">
  <div class="container-fluid px-4">
    <div class="sectionFrame">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="sectionTitle h5 mb-0"><?= $t('liveDrop.title') ?></h2>
        <label class="prettySwitch">
          <input type="checkbox" id="toggleLiveDrop" checked>
          <span class="switchTrack"><span class="switchThumb"></span></span>
          <span class="switchLabel ms-2 small"><?= $t('ui.show') ?></span>
        </label>
      </div>
      <div class="ticker" id="liveDropTicker"></div>
    </div>
  </div>
</section>
