<?php
class HipayController extends ShopPlusAppController {

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Security->unlockedActions = array('ipn', 'success', 'error', 'cancel');
  }

  public function success() {}

  public function error() {}

  public function cancel() {}

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
      throw new BadRequestException('Invalid operation or status ('.$operation.' - '.$statusOperation.')');
    // invalid currency
    if ($info->result[0]->origCurrency != "EUR")
      throw new BadRequestException('Invalid currency');
    // check

    // find offer
    $this->loadModel('ShopPlus.HipayOffer');
    $offer = $this->HipayOffer->find('first', array('conditions' => array('id' => floatval($info->result[0]->merchantDatas->_aKey_offer))));
    if (empty($offer))
      throw new NotFoundException('Offer not found with this amount');
    $offer = $offer['HipayOffer'];
    if (floatval($info->result[0]->origAmount) !== floatval($offer['amount']))
      throw new BadRequestException('Invalid amount for this offer');

    // find user
    $findUser = $this->User->find('first', array('conditions' => array('id' => $info->result[0]->merchantDatas->_aKey_user)));
    if (empty($findUser) || !isset($findUser['User']))
      throw new NotFoundException('User not found');
    $user = $findUser['User'];

    // check payment in history
    $this->loadModel('ShopPlus.HipayHistory');
    if ($this->HipayHistory->find('count', array('conditions' => array('payment_id' => $info->result[0]->transid))) > 0)
      return $this->response->statusCode(200); // already credited

    // Calculate new sold
    $newSold = floatval($user['money']) + floatval($offer['credits']);

    // Set new sold
    $this->User->id = $user['id'];
    $this->User->saveField('money', $newSold);

    // set into history
    $this->HistoryC = $this->Components->load('History');
    $this->HistoryC->set('BUY_MONEY', 'shop', null, $user['id']);

    $this->loadModel('ShopPlus.HipayHistory');
    $this->HipayHistory->create();
    $this->HipayHistory->set(array(
      'payment_id' => $info->result[0]->transid,
      'user_id' => $user['id'],
      'client_id' => $info->result[0]->idClient,
      'client_email' => $info->result[0]->emailClient,
      'card_country' => $info->result[0]->cardCountry,
      'ip_country' => $info->result[0]->ipCountry,
      'amount' => $info->result[0]->origAmount,
      'credits' => floatval($offer['credits']),
      'offer_id' => $offer['id'],
      'payment_method' => $info->result[0]->paymentMethod
    ));
    $this->HipayHistory->save();

    // notify user
    $this->loadModel('Notification');
    $this->Notification->setToUser($this->Lang->get('NOTIFICATION__HIPAY_CREDITED', array('{CREDITS}' => $offer['credits'], '{MONEY_NAME}' => $this->Configuration->getMoneyName(), '{AMOUNT}' => $info->result[0]->origAmount)), $user['id']);

    // send 200
    $this->response->statusCode(200);
  }

}
