<?php
class PaymillController extends ShopPlusAppController {

  public function createPayment() {
    $this->autoRender = false;
    $this->response->type('json');

    if (!$this->isConnected)
      throw new ForbiddenException('Not logged');
    if (!$this->request->is('post'))
      throw new NotFoundException('Not post');
    if (!isset($this->request->data['token']) || empty($this->request->data['token']))
      throw new NotFoundException('Missing token');
    if (!isset($this->request->data['amount']) || empty($this->request->data['amount']))
      throw new NotFoundException('Missing amount');
    $amount = floatval($this->request->data['amount']);
    $token = $this->request->data['token'];

    // config
    $this->loadModel('ShopPlus.PaymillConfiguration');
    $paymillConfig = $this->PaymillConfiguration->find('first');
    if (empty($paymillConfig) || !$paymillConfig['PaymillConfiguration']['status'])
      throw new InternalErrorException('Paymill is disabled');
    $paymillApiPrivateKey = $paymillConfig['PaymillConfiguration']['secret_key'];
    $paymillCreditFor1 = floatval($paymillConfig['PaymillConfiguration']['credits_for_1']);

    // check if not already stored in db
    $this->loadModel('ShopPlus.PaymillHistory');
    if ($this->PaymillHistory->find('count', array('conditions' => array('paymill_token' => $token))) > 0)
      return $this->response->body(json_encode(array('status' => false, 'msg' => $this->Lang->get('SHOPPLUS__PAYMILL_ALREADY_STORED'))));;

    // credits calcul
    $credits = $paymillCreditFor1 * $amount;

    // paymill API
    require ROOT.DS.'app'.DS.'Plugin'.DS.'ShopPlus'.DS.'Vendors'.DS.'paymill-php-master'.DS.'autoload.php';
    $request = new Paymill\Request($paymillApiPrivateKey);

    // init payment
    $payment = new \Paymill\Models\Request\Payment();
    $payment->setToken($token);

    // pay
    try {
      // create payment
      $response  = $request->create($payment);
      $paymentId = $response->getId();

      // create transaction
      $transaction = new Paymill\Models\Request\Transaction();
      $transaction->setAmount(intval($amount * 100)) // e.g. "4200" for 42.00 EUR
                  ->setCurrency('EUR')
                  ->setPayment($paymentId)
                  ->setDescription($this->Lang->get('SHOPPLUS__PAYMILL_OFFER_DESC', array('{CREDITS}' => $credits, '{MONEY_NAME}' => $this->Configuration->getMoneyName(), '{SITE_NAME}' => $this->Configuration->getKey('name'))));
      $response = $request->create($transaction);
      $transactionId = $response->getId();

      // Calculate new sold
      $findUser = $this->User->find('first', array('conditions' => array('id' => $this->User->getKey('id'))));
      $newSold = floatval($findUser['User']['money']) + floatval($credits);

      // Set new sold
      $this->User->id = $this->User->getKey('id');
      $this->User->saveField('money', $newSold);

      // save into history
      $this->PaymillHistory->create();
      $this->PaymillHistory->set(array(
        'user_id' => $this->User->getKey('id'),
        'amount' => $amount,
        'credits' => $credits,
        'paymill_token' => $token,
        'payment_id' => $paymentId,
        'transaction_id' => $transactionId
      ));
      $this->PaymillHistory->save();

      $this->HistoryC = $this->Components->load('History');
      $this->HistoryC->set('BUY_MONEY', 'shop');

      // send to user
      $this->response->body(json_encode(array('status' => true, 'msg' => $this->Lang->get('SHOPPLUS__PAYMILL_SUCCESS', array('{AMOUNT}' => $amount, '{CREDITS}' => $credits, '{MONEY_NAME}' => $this->Configuration->getMoneyName())))));
    } catch(\Paymill\Services\PaymillException $e) {
      // send to user
      $this->response->body(json_encode(array('status' => false, 'msg' => $e->getErrorMessage())));
    }
  }

  public function admin_config() {
    $this->autoRender = false;
    $this->response->type('json');

    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_CONFIG_PAYMILL'))
      throw new ForbiddenException();
    if(!$this->request->is('ajax'))
      throw new NotFoundException();

    if(empty($this->request->data['secret_key']) || empty($this->request->data['public_key']) || empty($this->request->data['credits_for_1']))
      return $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));

    // Save
    $this->loadModel('ShopPlus.PaymillConfiguration');
    $findConfig = $this->PaymillConfiguration->find('first');
    $id = (!empty($findConfig)) ? $findConfig['PaymillConfiguration']['id'] : null;
    $this->PaymillConfiguration->read(null, $id);
    $this->PaymillConfiguration->set(array(
      'secret_key' => $this->request->data['secret_key'],
      'public' => $this->request->data['public_key'],
      'credits_for_1' => $this->request->data['credits_for_1'],
      'status' => $this->request->data['status']
    ));
    $this->PaymillConfiguration->save();

    $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SHOPPLUS__PAYMILL_ADMIN_CONFIG_SAVED'))));
  }

  public function admin_get_histories() {
    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_VIEW_PAYMILL_HISTORY'))
      throw new ForbiddenException();

    $this->autoRender = false;
    $this->response->type('json');

    $this->DataTable = $this->Components->load('DataTable');
    $this->modelClass = 'PaymillHistory';
    $this->DataTable->initialize($this);
    $this->paginate = array(
      'fields' => array($this->modelClass.'.id',$this->modelClass.'.amount','User.pseudo',$this->modelClass.'.credits',$this->modelClass.'.paymill_token',$this->modelClass.'.payment_id',$this->modelClass.'.transaction_id',$this->modelClass.'.created'),
      'recursive' => 1
    );
    $this->DataTable->mDataProp = true;

    $response = $this->DataTable->getResponse();

    foreach ($response['aaData'] as $key => $value) {
      $response['aaData'][$key]['PaymillHistory']['payment_id'] = '<a href="https://app.paymill.com/payments/' . $value['PaymillHistory']['payment_id'] . '" target="_blank">' . $value['PaymillHistory']['payment_id'] . '</a>';
      $response['aaData'][$key]['PaymillHistory']['transaction_id'] = '<a href="https://app.paymill.com/transactions/' . $value['PaymillHistory']['transaction_id'] . '" target="_blank">' . $value['PaymillHistory']['transaction_id'] . '</a>';
    }

    $this->response->body(json_encode($response));
  }

}
