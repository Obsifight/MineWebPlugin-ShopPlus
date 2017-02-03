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
