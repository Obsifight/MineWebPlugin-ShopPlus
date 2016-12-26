<?php
class HipayController extends ShopPlusAppController {

  public function success() {
  }

  public function error() {
  }

  public function cancel() {
  }

  public function ipn() {
    $this->autoRender = false;
    $this->log(json_encode($this->request->data, JSON_PRETTY_PRINT));
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
    $this->loadModel('ShopPlus.HipayOffer');
    $offer = $this->HipayOffer->find('first', array('conditions' => array('id' => floatval($info->result[0]->merchantDatas->_aKey_offer))));
    if (empty($offer))
      throw new NotFoundException('Offer not found with this amount');
    $offer = $offer['HipayOffer'];

    // find user
    $findUser = $this->User->find('first', array('conditions' => array('id' => $info->result[0]->merchantDatas->_aKey_user)));
    if (empty($findUser) || !isset($findUser['User']))
      throw new NotFoundException('User not found');

    // check payment in history
    $this->loadModel('OpenShop.CreditsHistory');
    if ($this->CreditsHistory->find('count', array('conditions' => array('history_datas' => serialize($xml)))) > 0)
      return; // already credited

    // add credit and save into history
      // TODO

    // send 200
    $this->response->statusCode(200);
  }

}
