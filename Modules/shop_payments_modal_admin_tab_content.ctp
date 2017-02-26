<div class="tab-pane" id="tab_stripe">

  <h3><?= $Lang->get('SHOPPLUS__STRIPE_ADMIN') ?></h3>

  <br><br>

  <?php if($permissions['SHOPPLUS__ADMIN_CONFIG_STRIPE']): ?>
    <form action="<?= $this->Html->url(array('controller' => 'stripe', 'action' => 'config', 'plugin' => 'ShopPlus', 'admin' => true)) ?>" data-ajax="true">

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__STRIPE_ADMIN_CONFIG_SECRET_KEY') ?></label>
        <input type="text" class="form-control" name="secret_key" placeholder="Ex: sk_live_xGGvwDl3DMO0fArcK77iFnYE"<?= (isset($stripeConfig['StripeConfiguration']['secret_key'])) ? ' value="'.$stripeConfig['StripeConfiguration']['secret_key'].'"' : '' ?>>
      </div>

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__STRIPE_ADMIN_CONFIG_PUBLISHABLE_KEY') ?></label>
        <input type="text" class="form-control" name="publishable_key" placeholder="Ex: pk_live_XRw7YMJYVepvYZH32IP1TwUh"<?= (isset($stripeConfig['StripeConfiguration']['publishable_key'])) ? ' value="'.$stripeConfig['StripeConfiguration']['publishable_key'].'"' : '' ?>>
      </div>

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__STRIPE_ADMIN_CONFIG_DEFAULT_CREDITS_GIVED_FOR_1_AS_AMOUNT', array('{MONEY_NAME}' => ucfirst($Configuration->getMoneyName()))) ?></label>
        <input type="text" class="form-control" name="credits_for_1" placeholder="Ex: 80"<?= (isset($stripeConfig['StripeConfiguration']['credits_for_1'])) ? ' value="'.$stripeConfig['StripeConfiguration']['credits_for_1'].'"' : '' ?>>
      </div>

      <div class="checkbox">
        <input name="status" type="checkbox"<?= (isset($stripeConfig['StripeConfiguration']['status']) && $stripeConfig['StripeConfiguration']['status']) ? ' checked' : '' ?>>
         <label>
           <?= $Lang->get('SHOPPLUS__STRIPE_ADMIN_CONFIG_ENABLE') ?>
         </label>
       </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary"><?= $Lang->get('GLOBAL__SUBMIT') ?></button>
      </div>

    </form>
  <?php endif; ?>

  <?php if($permissions['SHOPPLUS__ADMIN_VIEW_STRIPE_HISTORY']): ?>
    <hr>

    <h3><?= $Lang->get('SHOPPLUS__STRIPE_HISTORIES') ?></h3>

    <table class="table table-bordered dataTable" id="histories_stripe">
      <thead>
        <tr>
          <th><?= $Lang->get('SHOPPLUS__STRIPE_HISTORIES_ID') ?></th>
          <th><?= $Lang->get('USER__USERNAME') ?></th>
          <th><?= $Lang->get('SHOPPLUS__STRIPE_HISTORIES_AMOUNT') ?></th>
          <th><?= ucfirst($Configuration->getMoneyName()) ?></th>
          <th><?= $Lang->get('SHOPPLUS__STRIPE_HISTORIES_TOKEN') ?></th>
          <th><?= $Lang->get('SHOPPLUS__STRIPE_HISTORIES_CHARGE_ID') ?></th>
          <th><?= $Lang->get('GLOBAL__CREATED') ?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <script type="text/javascript">
    $(document).ready(function() {
      $('#histories_stripe').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        'searching': true,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?= $this->Html->url(array('controller' => 'stripe', 'action' => 'get_histories', 'plugin' => 'ShopPlus', 'admin' => true)) ?>",
        "aoColumns": [
            {mData:"StripeHistory.id"},
            {mData:"User.pseudo"},
            {mData:"StripeHistory.amount"},
            {mData:"StripeHistory.credits"},
            {mData:"StripeHistory.stripe_token"},
            {mData:"StripeHistory.charge_id"},
            {mData:"StripeHistory.created"}
        ],
      });
    });
    </script>
    <hr>
  <?php endif; ?>

</div>
<div class="tab-pane" id="tab_paymill">

  <h3><?= $Lang->get('SHOPPLUS__PAYMILL_ADMIN') ?></h3>

  <br><br>

  <?php if($permissions['SHOPPLUS__ADMIN_CONFIG_PAYMILL']): ?>
    <form action="<?= $this->Html->url(array('controller' => 'paymill', 'action' => 'config', 'plugin' => 'ShopPlus', 'admin' => true)) ?>" data-ajax="true">

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__PAYMILL_ADMIN_CONFIG_SECRET_KEY') ?></label>
        <input type="text" class="form-control" name="secret_key" placeholder="Ex: 631efc87935fd6dde004868fb0585663"<?= (isset($paymillConfig['PaymillConfiguration']['secret_key'])) ? ' value="'.$paymillConfig['PaymillConfiguration']['secret_key'].'"' : '' ?>>
      </div>

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__PAYMILL_ADMIN_CONFIG_PUBLIC_KEY') ?></label>
        <input type="text" class="form-control" name="public_key" placeholder="Ex: 199292295933c61e323bdb95c636ddae"<?= (isset($paymillConfig['PaymillConfiguration']['public_key'])) ? ' value="'.$paymillConfig['PaymillConfiguration']['public_key'].'"' : '' ?>>
      </div>

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__PAYMILL_ADMIN_CONFIG_DEFAULT_CREDITS_GIVED_FOR_1_AS_AMOUNT', array('{MONEY_NAME}' => ucfirst($Configuration->getMoneyName()))) ?></label>
        <input type="text" class="form-control" name="credits_for_1" placeholder="Ex: 80"<?= (isset($paymillConfig['PaymillConfiguration']['credits_for_1'])) ? ' value="'.$paymillConfig['PaymillConfiguration']['credits_for_1'].'"' : '' ?>>
      </div>

      <div class="checkbox">
        <input name="status" type="checkbox"<?= (isset($paymillConfig['PaymillConfiguration']['status']) && $paymillConfig['PaymillConfiguration']['status']) ? ' checked' : '' ?>>
         <label>
           <?= $Lang->get('SHOPPLUS__PAYMILL_ADMIN_CONFIG_ENABLE') ?>
         </label>
       </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary"><?= $Lang->get('GLOBAL__SUBMIT') ?></button>
      </div>

    </form>
  <?php endif; ?>

  <?php if($permissions['SHOPPLUS__ADMIN_VIEW_PAYMILL_HISTORY']): ?>
    <hr>

    <h3><?= $Lang->get('SHOPPLUS__PAYMILL_HISTORIES') ?></h3>

    <table class="table table-bordered dataTable" id="histories_paymill">
      <thead>
        <tr>
          <th><?= $Lang->get('SHOPPLUS__PAYMILL_HISTORIES_ID') ?></th>
          <th><?= $Lang->get('USER__USERNAME') ?></th>
          <th><?= $Lang->get('SHOPPLUS__PAYMILL_HISTORIES_AMOUNT') ?></th>
          <th><?= ucfirst($Configuration->getMoneyName()) ?></th>
          <th><?= $Lang->get('SHOPPLUS__PAYMILL_HISTORIES_TOKEN') ?></th>
          <th><?= $Lang->get('SHOPPLUS__PAYMILL_HISTORIES_PAYMENT_ID') ?></th>
          <th><?= $Lang->get('SHOPPLUS__PAYMILL_HISTORIES_TRANSACTION_ID') ?></th>
          <th><?= $Lang->get('GLOBAL__CREATED') ?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <script type="text/javascript">
    $(document).ready(function() {
      $('#histories_paymill').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        'searching': true,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?= $this->Html->url(array('controller' => 'paymill', 'action' => 'get_histories', 'plugin' => 'ShopPlus', 'admin' => true)) ?>",
        "aoColumns": [
            {mData:"PaymillHistory.id"},
            {mData:"User.pseudo"},
            {mData:"PaymillHistory.amount"},
            {mData:"PaymillHistory.credits"},
            {mData:"PaymillHistory.paymill_token"},
            {mData:"PaymillHistory.payment_id"},
            {mData:"PaymillHistory.transaction_id"},
            {mData:"PaymillHistory.created"}
        ],
      });
    });
    </script>
    <hr>
  <?php endif; ?>

</div>
<div class="tab-pane" id="tab_hipay_wallet">

  <h3><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN') ?></h3>

  <br><br>

  <?php if($permissions['SHOPPLUS__ADMIN_CONFIG_HIPAY_WALLET']): ?>
    <form action="<?= $this->Html->url(array('controller' => 'HipayWallet', 'action' => 'config', 'plugin' => 'ShopPlus', 'admin' => true)) ?>" data-ajax="true">

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_CONFIG_USER_ACCOUNT_ID') ?></label>
        <input type="text" class="form-control" name="user_account_id" placeholder="Ex: 579770"<?= (isset($hipayWalletConfig['HipayWalletConfiguration']['user_account_id'])) ? ' value="'.$hipayWalletConfig['HipayWalletConfiguration']['user_account_id'].'"' : '' ?>>
      </div>

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_CONFIG_WEBSITE_ID') ?></label>
        <input type="text" class="form-control" name="website_id" placeholder="Ex: 419145"<?= (isset($hipayWalletConfig['HipayWalletConfiguration']['website_id'])) ? ' value="'.$hipayWalletConfig['HipayWalletConfiguration']['website_id'].'"' : '' ?>>
      </div>

      <div class="form-group">
        <label><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_CONFIG_PRIVATE_KEY', array('{MONEY_NAME}' => ucfirst($Configuration->getMoneyName()))) ?></label>
        <textarea class="form-control" rows="16" name="private_key"><?= (isset($hipayWalletConfig['HipayWalletConfiguration']['private_key'])) ? $hipayWalletConfig['HipayWalletConfiguration']['private_key'].'"' : '' ?></textarea>
      </div>

      <div class="checkbox">
        <input name="test" type="checkbox"<?= (isset($hipayWalletConfig['HipayWalletConfiguration']['test']) && $hipayWalletConfig['HipayWalletConfiguration']['test']) ? ' checked' : '' ?>>
         <label>
           <?= $Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_CONFIG_TEST_MODE') ?>
         </label>
      </div>

      <div class="checkbox">
        <input name="status" type="checkbox"<?= (isset($hipayWalletConfig['HipayWalletConfiguration']['status']) && $hipayWalletConfig['HipayWalletConfiguration']['status']) ? ' checked' : '' ?>>
         <label>
           <?= $Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_CONFIG_ENABLE') ?>
         </label>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary"><?= $Lang->get('GLOBAL__SUBMIT') ?></button>
      </div>

    </form>
  <?php endif; ?>

  <?php if($permissions['SHOPPLUS__ADMIN_CONFIG_HIPAY_WALLET']): ?>
    <hr>

    <h3>
      <?= $Lang->get('SHOPPLUS__HIPAY_WALLET_OFFERS') ?>
      <a href="#addHipayWalletOfferModal" class="btn btn-success" data-toggle="modal"><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_OFFERS_ADD') ?></a>
    </h3>


    <table class="table table-bordered" id="hipayWalletOffers">
      <thead>
        <tr>
          <th>ID</th>
          <th><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_HISTORIES_AMOUNT') ?></th>
          <th><?= ucfirst($Configuration->getMoneyName()) ?></th>
          <th><?= $Lang->get('GLOBAL__CREATED') ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($hipayWalletOffers as $offer) {
          echo '<tr>';
            echo '<td>' . $offer['HipayWalletOffer']['id'] . '</td>';
            echo '<td>' . $offer['HipayWalletOffer']['amount'] . '</td>';
            echo '<td>' . $offer['HipayWalletOffer']['credits'] . '</td>';
            echo '<td>' . $Lang->date($offer['HipayWalletOffer']['created']) . '</td>';
            echo '<td>';
              echo '<a class="btn btn-danger" href="' . $this->Html->url(array('controller' => 'HipayWallet', 'action' => 'offer_delete', 'admin' => true, 'plugin' => 'ShopPlus', 'id' => $offer['HipayWalletOffer']['id'])) . '">' . $Lang->get('GLOBAL__DELETE') . '</a>';
            echo '</td>';
          echo '</tr>';
        }
        ?>
      </tbody>
    </table>
  <?php endif; ?>

  <?php if($permissions['SHOPPLUS__ADMIN_VIEW_HIPAY_WALLET_HISTORY']): ?>
    <hr>

    <h3><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_HISTORIES') ?></h3>

    <table class="table table-bordered dataTable" id="histories_hipay_wallet">
      <thead>
        <tr>
          <th><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_HISTORIES_ID') ?></th>
          <th><?= $Lang->get('USER__USERNAME') ?></th>
          <th><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_HISTORIES_AMOUNT') ?></th>
          <th><?= ucfirst($Configuration->getMoneyName()) ?></th>
          <th><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_HISTORIES_TRANS_ID') ?></th>
          <th><?= $Lang->get('GLOBAL__CREATED') ?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <script type="text/javascript">
    $(document).ready(function() {
      $('#histories_hipay_wallet').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        'searching': true,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?= $this->Html->url(array('controller' => 'HipayWallet', 'action' => 'get_histories', 'plugin' => 'ShopPlus', 'admin' => true)) ?>",
        "aoColumns": [
            {mData:"HipayWalletHistory.id"},
            {mData:"User.pseudo"},
            {mData:"HipayWalletHistory.amount"},
            {mData:"HipayWalletHistory.credits"},
            {mData:"HipayWalletHistory.transaction_id"},
            {mData:"HipayWalletHistory.created"}
        ],
      });
    });
    </script>
    <hr>
  <?php endif; ?>

</div>
<div class="modal fade" tabindex="-1" id="addHipayWalletOfferModal" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_OFFERS_ADD') ?></h4>
      </div>
      <form action="<?= $this->Html->url(array('controller' => 'HipayWallet', 'action' => 'offer_add', 'plugin' => 'ShopPlus', 'admin' => true)) ?>" data-ajax="true" data-callback-function="onHipayWalletOfferAdded">
        <div class="modal-body">
          <div class="ajax-msg"></div>

          <div class="form-group">
            <label><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_HISTORIES_AMOUNT') ?></label>
            <input type="text" class="form-control" name="amount">
          </div>

          <div class="form-group">
            <label><?= ucfirst($Configuration->getMoneyName()) ?></label>
            <input type="text" class="form-control" name="credits">
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?= $Lang->get('GLOBAL__CLOSE') ?></button>
          <button type="submit" class="btn btn-success"><?= $Lang->get('GLOBAL__SUBMIT') ?></button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
  function onHipayWalletOfferAdded(req, res) {
    // hide modal
    $('#addHipayWalletOfferModal').modal('hide')
    // clear inputs & success msg
    $('#addHipayWalletOfferModal input').val('')
    $('#addHipayWalletOfferModal .ajax-msg').html('')
    // add into table
    var html = ''
    html += '<tr>'
      html += '<td>' + res.data.id + '</td>'
      html += '<td>' + req.amount + '</td>'
      html += '<td>' + req.credits + '</td>'
      html += '<td>' + res.data.created + '</td>'
      html += '<td>'
        var url = '<?= $this->Html->url(array('controller' => 'HipayWallet', 'action' => 'offer_delete', 'admin' => true, 'plugin' => 'ShopPlus', 'id' => '{ID}')) ?>'.replace('{ID}', res.data.id)
        html += '<a class="btn btn-danger" href="' + url + '"><?= $Lang->get('GLOBAL__DELETE') ?></a>'
      html += '</td>'
    html += '</tr>'
    $('table#hipayWalletOffers tbody').append(html);
  }
</script>
