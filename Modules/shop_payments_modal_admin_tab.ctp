<?php if ($Permissions->can('SHOPPLUS__ADMIN_CONFIG_STRIPE') || $Permissions->can('SHOPPLUS__ADMIN_VIEW_STRIPE_HISTORY')): ?>
  <li class=""><a href="#tab_stripe" data-toggle="tab" aria-expanded="false"><?= $Lang->get('SHOPPLUS__STRIPE_ADMIN') ?></a></li>
<?php endif; ?>
