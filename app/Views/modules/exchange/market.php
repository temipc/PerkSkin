<div class="sectionFrame">
  <h2 class="sectionTitle h6 mb-3"><?= ___('Marketplace - Exchanges') ?></h2>
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card p-3 h-100">
        <div class="h6 mb-2"><?= ___('New offer') ?></div>
        <div class="mb-2 small text-white-50 exchangeSubtleText"><?= ___('Specify an item and a desired consideration.') ?></div>
        <div class="mb-2">
          <label class="form-label small mb-1" style="color:#fff;"><?= ___('Select item (inventory)') ?></label>
          <div class="prettySelect">
            <select id="exItemName" class="nativeSelect"></select>
            <span class="selectDisplay"></span>
            <span class="selectArrow"></span>
            <div class="dropdownPanel"></div>
          </div>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label small mb-1" style="color:#fff;"><?= ___('Offered value') ?> (<span id="exCurLabel1">USD</span>)</label>
            <input id="exItemValue" type="number" step="0.001" min="0" class="form-control formControlDark" placeholder="0.000" readonly />
          </div>
          <div class="col-6">
            <label class="form-label small mb-1" style="color:#fff;"><?= ___('Requested value') ?> (<span id="exCurLabel2">USD</span>)</label>
            <input id="exAskValue" type="number" step="0.001" min="0" class="form-control formControlDark" placeholder="0.000" />
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btnPrimary" id="exCreateOffer"><?= ___('Publish offer') ?></button>
          <button class="btn btn-outline-light" id="exClear"><?= ___('Clear fields') ?></button>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="h6 mb-0"><?= ___('Open offers') ?></div>
          <div class="small text-white-50 exchangeSubtleText"><?= ___('10 rows / page') ?></div>
        </div>
        <div class="table-responsive">
          <table class="table table-dark table-sm align-middle mb-0">
            <thead>
              <tr>
                <th><?= ___('Time') ?></th>
                <th><?= ___('Item') ?></th>
                <th><?= ___('Offer') ?></th>
                <th><?= ___('Ask') ?></th>
                <th><?= ___('Status') ?></th>
                <th></th>
              </tr>
            </thead>
            <tbody id="exOffersBody"></tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-2">
          <div class="small text-white-50 exchangeSubtleText" id="exPagerInfo">1 / 1</div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-light" id="exPrev"><?= ___('Prev') ?></button>
            <button class="btn btn-sm btn-outline-light" id="exNext"><?= ___('Next') ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="exchangeBidModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header">
        <h3 class="h5 mb-0"><?= ___('Make offer') ?></h3>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="exchangeBidOfferId">
        <div class="small text-white-50 exchangeSubtleText mb-3" id="exchangeBidOfferTarget">-</div>
        <div class="mb-3">
          <label class="form-label small mb-1 text-white"><?= ___('Offer type') ?></label>
          <select id="exchangeBidType" class="form-select formControlDark">
            <option value="inventory"><?= ___('Coupon / inventory item') ?></option>
            <option value="cash"><?= ___('Money amount') ?></option>
            <option value="gems"><?= ___('Gems') ?></option>
          </select>
        </div>
        <div class="mb-3" id="exchangeBidInventoryWrap">
          <label class="form-label small mb-1 text-white"><?= ___('Your inventory item') ?></label>
          <select id="exchangeBidInventory" class="form-select formControlDark"></select>
        </div>
        <div class="mb-3 d-none" id="exchangeBidCashWrap">
          <label class="form-label small mb-1 text-white"><?= ___('Money amount') ?> (USD)</label>
          <input id="exchangeBidCash" type="number" min="0" step="0.001" class="form-control formControlDark" placeholder="0.000">
        </div>
        <div class="mb-3 d-none" id="exchangeBidGemsWrap">
          <label class="form-label small mb-1 text-white"><?= ___('Gems') ?></label>
          <input id="exchangeBidGems" type="number" min="0" step="1" class="form-control formControlDark" placeholder="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?= ___('Cancel') ?></button>
        <button type="button" class="btn btnPrimary" id="exchangeBidSubmit"><?= ___('Send offer') ?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="exchangeReviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content modalDark">
      <div class="modal-header">
        <h3 class="h5 mb-0"><?= ___('Received offers') ?></h3>
        <button type="button" class="btn-close btnCloseWhite" data-bs-dismiss="modal" aria-label="<?= ___('Close') ?>"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="exchangeReviewOfferId">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="card p-3 h-100">
              <div class="small text-white-50 mb-1"><?= ___('Your listed item') ?></div>
              <div class="h6 mb-1 text-white" id="exchangeReviewOwnName">-</div>
              <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-info text-dark" id="exchangeReviewOwnValue">-</span>
                <span class="badge bg-secondary" id="exchangeReviewOwnAsk">-</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card p-3 h-100">
              <div class="small text-white-50 mb-1"><?= ___('Decision helper') ?></div>
              <div class="small text-white mb-2"><?= ___('Compare the incoming offer value against your listed item value and requested amount.') ?></div>
              <div class="small text-white-50" id="exchangeReviewHint">-</div>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-dark table-sm align-middle mb-0">
            <thead>
              <tr>
                <th><?= ___('Time') ?></th>
                <th><?= ___('Offer') ?></th>
                <th><?= ___('Value') ?></th>
                <th><?= ___('Status') ?></th>
                <th class="text-end"></th>
              </tr>
            </thead>
            <tbody id="exchangeReviewBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
