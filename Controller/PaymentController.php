<?php
class PaymentController extends ShopPlusAppController {

  private function __generateHipayForm($offer) {
    $xml = "<?xml version='1.0' encoding='utf-8' ?>"
      . "<order>"
        . "<userAccountId>{$offer['user_account_id']}</userAccountId>"
        . "<currency>EUR</currency>"
        . "<label>".$offer['credits']." ".$this->Configuration->getMoneyName()."</label>"
        . "<ageGroup>ALL</ageGroup>"
        . "<categoryId>251</categoryId>"
        . "<urlAcquital><![CDATA[".Router::url(array('controller' => 'hipay', 'action' => 'ipn'), true)."]]></urlAcquital>"
        . "<urlOk><![CDATA[".Router::url(array('controller' => 'hipay', 'action' => 'success'), true)."]]></urlOk>"
        . "<urlKo><![CDATA[".Router::url(array('controller' => 'hipay', 'action' => 'error'), true)."]]></urlKo>"
        . "<urlCancel><![CDATA[".Router::url(array('controller' => 'hipay', 'action' => 'cancel'), true)."]]></urlCancel>"
        . "<urlInstall><![CDATA[".Router::url(array('controller' => 'payment', 'action' => 'addCredit'), true)."]]></urlInstall>"
        //. "<urlLogo><![CDATA[".Router::url('', true)."]]></urlLogo>"
        . "<!-- optional -->"
        //. "<thirdPartySecurity>compatible</thirdPartySecurity>"
        . "<locale>fr_FR</locale>"
        //. "<issuerAccountLogin>".$offer['data']->issue_email."</issuerAccountLogin>"
        . "<data>"
            . "<user>"
              . "<id>{$this->User->getKey('id')}</id>"
            . "</user>"
            . "<offer>"
              . "<id>{$offer['id']}</id>"
            . "</offer>"
        . "</data>"
        . "<items>"
            . "<item id='1'>"
                . "<name>".$offer['credits']." ".$this->Configuration->getMoneyName()."</name>"
                . "<infos>Achat de ".$offer['credits']." ".$this->Configuration->getMoneyName()." sur BloodSymphony</infos>"
                . "<amount>".$offer['amount']."</amount>"
                . "<categoryId>251</categoryId>"
                . "<quantity>1</quantity>"
                . "<reference>REF1</reference>"
            . "</item>"
        . "</items>"
      . "</order>";
    $signKey = $offer['sign_key'];
    $encodedData = base64_encode($xml);
    $md5Sign = md5($encodedData.$signKey);
    return array($encodedData, $md5Sign);
  }

  public function addCredit() {
    if (!$this->isConnected)
      throw new ForbiddenException();
    $this->set('title_for_layout', $this->Lang->get('SHOPPLUS__ADD_CREDIT_TITLE', array('{MONEY_NAME}' => $this->Configuration->getMoneyName())));

    $this->loadModel('Shop.Paypal');
    $paypalOffers = $this->Paypal->find('all');

    $this->loadModel('Shop.Starpass');
    $starpassOffers = $this->Starpass->find('all');

    $this->loadModel('Shop.DedipassConfig');
    $findDedipassConfig = $this->DedipassConfig->find('first');
    $dedipassStatus = (!empty($findDedipassConfig) && isset($findDedipassConfig['DedipassConfig']['status']) && $findDedipassConfig['DedipassConfig']['status']) ? true : false;
    if ($dedipassStatus) {
      $this->loadModel('Shop.DedipassConfig');
      $search = $this->DedipassConfig->find('first');
      $this->set('dedipassPublicKey', $search['DedipassConfig']['public_key']);
    }

    if ($this->EyPlugin->isInstalled('eywek.paysafecard.-1')) {
      $this->loadModel('Paysafecard.Config'); // find config
      $getConfig = $this->Config->find('first');
      if (is_array($getConfig) && isset($getConfig['Config']) && !empty($getConfig['Config']['api_key'])) { // if psc is enabled and configured
        $currency = $getConfig['Config']['currency']; // setup configured currency
        $this->set('paysafecardCreditFor1', $getConfig['Config']['default_credits_gived_for_1_as_amount']);
      } else {
        $currency = false;
      }
      $this->set('paysafecardCurrency', $currency);
    }

    $this->loadModel('ShopPlus.HipayOffer');
    $hipayOffers = $this->HipayOffer->find('all');
    foreach ($hipayOffers as $key => $offer) {
      list($hipayOffers[$key]['data'], $hipayOffers[$key]['sign']) = $this->__generateHipayForm($offer['HipayOffer']);
    }

    $this->set(compact(
      'paypalOffers',
      'starpassOffers',
      'dedipassStatus',
      'hipayOffers'
    ));
  }

}
