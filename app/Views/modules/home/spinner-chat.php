<section class="spinnerSection" id="spinner">
  <div class="container-fluid px-4">
    <div class="sectionFrame">
      <div class="row">
        <div class="col-lg-6">
          <h2 class="sectionTitle h5"><?= $t('spinner.title') ?></h2>
          <div class="spinnerDemo" id="spinnerDemo">
            <div class="spinnerViewport">
              <div class="spinnerReel" id="spinnerReel"></div>
              <div class="spinnerPointer"></div>
            </div>
            <div class="d-flex gap-2 mt-3">
              <button class="btn btnPrimary" id="spinButton"><?= $t('spinner.spin') ?></button>
              <label class="prettySwitch align-self-center mb-0" title="<?= $t('spinner.fastRun') ?>">
                <input type="checkbox" id="spinFastToggle">
                <span class="switchTrack"><span class="switchThumb"></span></span>
              </label>
              <span class="small align-self-center"><?= $t('spinner.fast') ?></span>
            </div>
            <div class="small mt-1" id="spinQuotaInfo"></div>
          </div>
        </div>
        <div class="col-lg-6">
          <h2 class="sectionTitle h5"><?= $t('chat.title') ?></h2>
          <div class="chatBox" id="chatBox"></div>
          <div class="input-group mt-2 chatComposer">
            <input type="email" class="d-none" tabindex="-1" autocomplete="username" aria-hidden="true">
            <input type="password" class="d-none" tabindex="-1" autocomplete="current-password" aria-hidden="true">
            <input type="search" class="form-control chatInputAero" id="chatInput" name="community_message_input" autocomplete="off" autocorrect="off" autocapitalize="sentences" spellcheck="false" data-form-type="other" data-lpignore="true" placeholder="<?= $t('chat.placeholder') ?>">
            <button class="btn chatSendAero" id="chatSend"><?= $t('chat.send') ?></button>
          </div>
          <div class="small text-muted mt-2" id="chatStatus"></div>
        </div>
      </div>
    </div>
  </div>
</section>
