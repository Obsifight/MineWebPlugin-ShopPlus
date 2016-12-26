<?php
Router::connect('/shop/credits/add', array('controller' => 'payment', 'action' => 'addCredit', 'plugin' => 'ShopPlus'));

Router::connect('/shop/credits/hipay/success', array('controller' => 'hipay', 'action' => 'success', 'plugin' => 'ShopPlus'));
Router::connect('/shop/credits/hipay/error', array('controller' => 'hipay', 'action' => 'error', 'plugin' => 'ShopPlus'));
Router::connect('/shop/credits/hipay/cancel', array('controller' => 'hipay', 'action' => 'cancel', 'plugin' => 'ShopPlus'));
Router::connect('/shop/credits/hipay/ipn', array('controller' => 'hipay', 'action' => 'ipn', 'plugin' => 'ShopPlus'));
