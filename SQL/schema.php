<?php
class ShopPlusAppSchema extends CakeSchema {

  public $file = 'schema.php';

  public function before($event = array()) {
      return true;
  }

  public function after($event = array()) {}

  public $shopplus__stripe_configurations = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'status' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
    'secret_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'publishable_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'credits_for_1' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1)
    ),
    'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
  );

  public $shopplus__paymill_configurations = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'status' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
    'secret_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'public_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'credits_for_1' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1)
    ),
    'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
  );

  /*public $shop__dedipass_histories = array(
    'shopplus-payout' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false)
  );*/

  public $shopplus__stripe_histories = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
    'amount' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'credits' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'stripe_token' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'charge_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1)
    ),
    'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
  );

  public $shopplus__paymill_histories = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
    'amount' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'credits' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'paymill_token' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'payment_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'transaction_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1)
    ),
    'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
  );

  public $shopplus__hipay_wallet_configurations = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'user_account_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
    'website_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
    'private_key' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'status' => array('type' => 'boolean', 'null' => false, 'default' => null),
    'test' => array('type' => 'boolean', 'null' => false, 'default' => null),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1)
    ),
    'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
  );

  public $shopplus__hipay_wallet_histories = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
    'offer_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
    'amount' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'credits' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'transaction_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
    'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1)
    ),
    'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
  );

  public $shopplus__hipay_wallet_offers = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'credits' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'amount' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
    'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1)
    ),
    'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
  );
}
