<?php
Router::connect('/shop/credits/add', array('controller' => 'PaymentPage', 'action' => 'addCredit', 'plugin' => 'ShopPlus'));

Router::connect('/shop/credits/stripe/charge', array('controller' => 'stripe', 'action' => 'charge', 'plugin' => 'ShopPlus'));
Router::connect('/admin/shop/credits/stripe/config', array('controller' => 'stripe', 'action' => 'config', 'plugin' => 'ShopPlus', 'admin' => true));
Router::connect('/admin/shop/credits/stripe/history', array('controller' => 'stripe', 'action' => 'get_histories', 'plugin' => 'ShopPlus', 'admin' => true));

Router::connect('/shop/credits/paymill/payment/create', array('controller' => 'paymill', 'action' => 'createPayment', 'plugin' => 'ShopPlus'));
Router::connect('/admin/shop/credits/paymill/config', array('controller' => 'paymill', 'action' => 'config', 'plugin' => 'ShopPlus', 'admin' => true));
Router::connect('/admin/shop/credits/paymill/history', array('controller' => 'paymill', 'action' => 'get_histories', 'plugin' => 'ShopPlus', 'admin' => true));

Router::connect('/admin/shop/stats', array('controller' => 'ShopStats', 'action' => 'index', 'plugin' => 'ShopPlus', 'admin' => true));
