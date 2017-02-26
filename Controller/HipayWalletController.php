<?php
class HipayWalletController extends ShopPlusAppController {

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Security->unlockedActions = array('ipn');
  }

  public function ipn() {
    $this->autoRender = false;
    // check request
    if (!$this->request->is('post'))
      throw new BadRequestException('Not post');
    if (!isset($this->request->data['xml']) || empty($this->request->data['xml']))
      throw new BadRequestException('Xml ressource not found');

    // parse request
    $xml = $this->request->data['xml'];
    $info = new SimpleXMLElement($xml);
    $operation = $info->result[0]->operation;
    $statusOperation = $info->result[0]->status;

    // invalid capture request
    if ($operation != "capture" || $statusOperation != "ok")
      throw new BadRequestException('Invalid operation or status');
    // invalid currency
    if ($info->result[0]->origCurrency != "EUR")
      throw new BadRequestException('Invalid currency');

    // find offer
    $this->loadModel('ShopPlus.HipayWalletOffer');
    $offer = $this->HipayWalletOffer->find('first', array('conditions' => array('amount' => floatval($info->result[0]->origAmount), 'id' => (int)$info->result[0]->merchantDatas->_aKey_offer)));
    if (empty($offer))
      throw new NotFoundException('Offer not found with this amount');
    $offer = $offer['HipayWalletOffer'];

    // find user
    if (!$this->User->exist($info->result[0]->merchantDatas->_aKey_user))
      throw new NotFoundException('User not found');
    $userId = $info->result[0]->merchantDatas->_aKey_user;
    // check payment in history
    $this->loadModel('ShopPlus.HipayWalletHistory');
    if ($this->HipayWalletHistory->find('count', array('conditions' => array('transaction_id' => $info->result[0]->transid))) > 0)
      return; // already credited

    // Calculate new sold
    $findUser = $this->User->find('first', array('conditions' => array('id' => $userId)));
    $newSold = floatval($findUser['User']['money']) + floatval($offer['credits']);

    // Set new sold
    $this->User->id = $userId;
    $this->User->saveField('money', $newSold);

    // add credit and save into history
    $this->HipayWalletHistory->create();
    $this->HipayWalletHistory->set(array(
      'user_id' => $userId,
      'offer_id' => $offer['id'],
      'amount' => floatval($info->result[0]->origAmount),
      'credits' => $offer['credits'],
      'transaction_id' => $info->result[0]->transid
    ));
    $this->HipayWalletHistory->save();

    // send 200
    $this->response->statusCode(200);
  }

  public function success() {
    $this->set('title_for_layout', $this->Lang->get('SHOPPLUS__HIPAY_WALLET_SUCCESS_TITLE'));
  }

  public function cancel() {
    $this->set('title_for_layout', $this->Lang->get('SHOPPLUS__HIPAY_WALLET_CANCEL_TITLE'));
  }

  public function error() {
    $this->set('title_for_layout', $this->Lang->get('SHOPPLUS__HIPAY_WALLET_ERROR_TITLE'));
  }

  public function admin_config() {
    $this->autoRender = false;
    $this->response->type('json');

    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_CONFIG_HIPAY_WALLET'))
      throw new ForbiddenException();
    if(!$this->request->is('ajax'))
      throw new NotFoundException();

    if(empty($this->request->data['user_account_id']) || empty($this->request->data['website_id']) || empty($this->request->data['private_key']))
      return $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));

    // Save
    $this->loadModel('ShopPlus.HipayWalletConfiguration');
    $findConfig = $this->HipayWalletConfiguration->find('first');
    $id = (!empty($findConfig)) ? $findConfig['HipayWalletConfiguration']['id'] : null;
    $this->HipayWalletConfiguration->read(null, $id);
    $this->HipayWalletConfiguration->set(array(
      'user_account_id' => intval($this->request->data['user_account_id']),
      'website_id' => intval($this->request->data['website_id']),
      'private_key' => $this->request->data['private_key'],
      'test' => $this->request->data['test'],
      'status' => $this->request->data['status']
    ));
    $this->HipayWalletConfiguration->save();

    $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_CONFIG_SAVED'))));
  }

  public function admin_get_histories() {
    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_VIEW_HIPAY_WALLET_HISTORY'))
      throw new ForbiddenException();

    $this->autoRender = false;
    $this->response->type('json');

    $this->DataTable = $this->Components->load('DataTable');
    $this->modelClass = 'HipayWalletHistory';
    $this->DataTable->initialize($this);
    $this->paginate = array(
      'fields' => array(
        $this->modelClass.'.id',
        $this->modelClass.'.amount',
        'User.pseudo',
        $this->modelClass.'.credits',
        $this->modelClass.'.transaction_id',
        $this->modelClass.'.created'
      ),
      'recursive' => 1
    );
    $this->DataTable->mDataProp = true;

    $response = $this->DataTable->getResponse();

    /*foreach ($response['aaData'] as $key => $value) {
      $response['aaData'][$key]['StripeHistory']['charge_id'] = '<a href="https://dashboard.stripe.com/payments/' . $value['StripeHistory']['charge_id'] . '" target="_blank">' . $value['StripeHistory']['charge_id'] . '</a>';
    }*/

    $this->response->body(json_encode($response));
  }

  public function admin_offer_add() {
    $this->autoRender = false;
    $this->response->type('json');

    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_CONFIG_HIPAY_WALLET_OFFERS'))
      throw new ForbiddenException();
    if(!$this->request->is('ajax'))
      throw new NotFoundException();

    if(empty($this->request->data['amount']) || empty($this->request->data['credits']))
      return $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));

    // Save
    $this->loadModel('ShopPlus.HipayWalletOffer');
    $this->HipayWalletOffer->create();
    $this->HipayWalletOffer->set(array(
      'amount' => floatval($this->request->data['amount']),
      'credits' => floatval($this->request->data['credits'])
    ));
    $this->HipayWalletOffer->save();

    $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_ADD_OFFER_SUCCESS'), 'data' => array('id' => $this->HipayWalletOffer->getLastInsertId(), 'created' => date('Y-m-d H:i:s')))));
  }

  public function admin_offer_delete() {
    $this->autoRender = false;

    if(!$this->isConnected || !$this->Permissions->can('SHOPPLUS__ADMIN_CONFIG_HIPAY_WALLET_OFFERS'))
      throw new ForbiddenException();
    if (!isset($this->request->params) || !isset($this->request->params['id']))
      throw new BadRequestException();

    // delete offer
    $this->loadModel('ShopPlus.HipayWalletOffer');
    $this->HipayWalletOffer->delete($this->request->params['id']);

    $this->Session->setFlash($this->Lang->get('SHOPPLUS__HIPAY_WALLET_ADMIN_DELETE_OFFER_SUCCESS'), 'default.success');
    $this->redirect(array('controller' => 'payment', 'action' => 'index', 'plugin' => 'shop', 'admin' => true));
  }

}
