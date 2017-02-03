<?php
class StripeController extends ShopPlusAppController {

  public function charge() {
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
    $this->loadModel('ShopPlus.StripeConfiguration');
    $stripeConfig = $this->StripeConfiguration->find('first');
    if (empty($stripeConfig) || !$stripeConfig['StripeConfiguration']['status'])
      throw new InternalErrorException('Stripe is disabled');
    $stripeApiPrivateKey = $stripeConfig['StripeConfiguration']['secret_key'];
    $stripeCreditFor1 = floatval($stripeConfig['StripeConfiguration']['credits_for_1']);

    // check if not already stored in db
    $this->loadModel('ShopPlus.StripeHistory');
    if ($this->StripeHistory->find('count', array('conditions' => array('stripe_token' => $token))) > 0)
      return $this->response->body(json_encode(array('status' => false, 'msg' => $this->Lang->get('SHOPPLUS__STRIPE_ALREADY_STORED'))));;

    // Stripe API
    require ROOT.DS.'app'.DS.'Plugin'.DS.'ShopPlus'.DS.'Vendors'.DS.'stripe-php-4.3.0'.DS.'init.php';
    \Stripe\Stripe::setApiKey($stripeApiPrivateKey);

    // credits calcul
    $credits = $stripeCreditFor1 * $amount;

    // Create a charge: this will charge the user's card
    try {
      $charge = \Stripe\Charge::create(array(
        'amount' => number_format($amount, 2, '', ''), // Amount in cents
        'currency' => 'eur',
        'source' => $token,
        'description' => "{$credits} {$this->Configuration->getMoneyName()}"
      ));

      // Calculate new sold
      $findUser = $this->User->find('first', array('conditions' => array('id' => $this->User->getKey('id'))));
      $newSold = floatval($findUser['User']['money']) + floatval($credits);

      // Set new sold
      $this->User->id = $this->User->getKey('id');
      $this->User->saveField('money', $newSold);

      // save into history
      $this->StripeHistory->create();
      $this->StripeHistory->set(array(
        'user_id' => $this->User->getKey('id'),
        'amount' => $amount,
        'credits' => $credits,
        'stripe_token' => $token,
        'charge_id' => $charge->id
      ));
      $this->StripeHistory->save();

      $this->HistoryC = $this->Components->load('History');
      $this->HistoryC->set('BUY_MONEY', 'shop');

      // send to user
      $this->response->body(json_encode(array('status' => true, 'msg' => $this->Lang->get('SHOPPLUS__STRIPE_SUCCESS', array('{AMOUNT}' => $amount, '{CREDITS}' => $credits, '{MONEY_NAME}' => $this->Configuration->getMoneyName())))));
    } catch(\Stripe\Error\Card $e) {
      $body = $e->getJsonBody();
      $err = $body['error'];
      // send to user
      $this->response->body(json_encode(array('status' => false, 'msg' => $err['message'])));
    }
  }

  public function admin_config() {
    $this->autoRender = false;
    $this->response->type('json');

    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_CONFIG_STRIPE'))
      throw new ForbiddenException();
    if(!$this->request->is('ajax'))
      throw new NotFoundException();

    if(empty($this->request->data['secret_key']) || empty($this->request->data['publishable_key']) || empty($this->request->data['credits_for_1']))
      return $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));

    // Save
    $this->loadModel('ShopPlus.StripeConfiguration');
    $this->StripeConfiguration->read(null, 1);
    $this->StripeConfiguration->set(array(
      'secret_key' => $this->request->data['secret_key'],
      'publishable_key' => $this->request->data['publishable_key'],
      'credits_for_1' => $this->request->data['credits_for_1']
    ));
    $this->StripeConfiguration->save();

    $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SHOPPLUS__STRIPE_ADMIN_CONFIG_SAVED'))));
  }

  public function admin_get_histories() {
    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_VIEW_STRIPE_HISTORY'))
      throw new ForbiddenException();

    $this->autoRender = false;
    $this->response->type('json');

    $this->DataTable = $this->Components->load('DataTable');
    $this->modelClass = 'StripeHistory';
    $this->DataTable->initialize($this);
    $this->paginate = array(
      'fields' => array($this->modelClass.'.id',$this->modelClass.'.amount','User.pseudo',$this->modelClass.'.credits',$this->modelClass.'.stripe_token',$this->modelClass.'.charge_id',$this->modelClass.'.created'),
      'recursive' => 1
    );
    $this->DataTable->mDataProp = true;

    $response = $this->DataTable->getResponse();

    foreach ($response['aaData'] as $key => $value) {
      $response['aaData'][$key]['StripeHistory']['charge_id'] = '<a href="https://dashboard.stripe.com/payments/' . $value['StripeHistory']['charge_id'] . '" target="_blank">' . $value['StripeHistory']['charge_id'] . '</a>';
    }

    $this->response->body(json_encode($response));
  }

}
