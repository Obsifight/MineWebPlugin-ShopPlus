<?php if ($permissions['SHOPPLUS__ADMIN_CONFIG_STRIPE'] || $permissions['SHOPPLUS__ADMIN_VIEW_STRIPE_HISTORY']): ?>
  <li class=""><a href="#tab_stripe" data-toggle="tab" aria-expanded="false"><?= $Lang->get('SHOPPLUS__STRIPE_ADMIN') ?></a></li>
<?php endif; ?>
<?php if ($permissions['SHOPPLUS__ADMIN_CONFIG_PAYMILL'] || $permissions['SHOPPLUS__ADMIN_VIEW_PAYMILL_HISTORY']): ?>
  <li class=""><a href="#tab_paymill" data-toggle="tab" aria-expanded="false"><?= $Lang->get('SHOPPLUS__PAYMILL_ADMIN') ?></a></li>
<?php endif; ?>
<?php if ($permissions['SHOPPLUS__ADMIN_CONFIG_HIPAY_WALLET'] || $permissions['SHOPPLUS__ADMIN_VIEW_HIPAY_WALLET_HISTORY']): ?>
  <li class=""><a href="#tab_hipay_wallet" data-toggle="tab" aria-expanded="false"><?= $Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN') ?></a></li>
<?php endif; ?>
