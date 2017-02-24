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
    $offer = $this->HipayWalletOffer->find('first', array('conditions' => array('amount' => floatval($info->result[0]->origAmount), 'id' => (int)$info->result[0]->merchantDatas->_aKey_offer_id)));
    if (empty($offer))
      throw new NotFoundException('Offer not found with this amount');
    $offer = $offer['HipayWalletOffer'];

    // find user
    if (!$this->User->exist((int)$info->result[0]->merchantDatas->_aKey_user_id))
      throw new NotFoundException('User not found');
    $userId = (int)$info->result[0]->merchantDatas->_aKey_user_id;
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

    $this->HistoryC = $this->Components->load('History');
    $this->HistoryC->set('BUY_MONEY', 'shop', $userId);

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

}
