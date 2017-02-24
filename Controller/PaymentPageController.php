<?php
class PaymentPageController extends ShopPlusAppController {

  private function __encryptHipayWallet($source, $privateKey) {
    $maxLength = 117;
    $output = "";
    while ($source) {
      $slice = substr($source, 0, $maxLength);
      $source = substr($source, $maxLength);
      openssl_private_encrypt($slice, $encrypted, $privateKey);
      $output .= $encrypted;
    }
    return $output;
  }
  private function __signHipayWallet($data, $privateKey) {
    $output = "";
    openssl_private_encrypt(sha1($data), $output, $privateKey);
    return $output;
  }

  /*
    $offer = array('user_account_id', 'private_key', 'credits', 'id', 'amount')
  */
  private function __generateHipayWalletForm($offer) {
    $xml = "<?xml version='1.0' encoding='utf-8' ?>"
      . "<order>"
        . "<userAccountId>".$offer['user_account_id']."</userAccountId>"
        . "<currency>EUR</currency>"
        . "<label>".$offer['credits']." ".$this->Configuration->getMoneyName()."</label>"
        . "<ageGroup>ALL</ageGroup>"
        . "<categoryId>251</categoryId>"
        . "<urlAcquital><![CDATA[".Router::url(array('controller' => 'HipayWallet', 'action' => 'ipn', 'plugin' => 'ShopPlus'), true)."]]></urlAcquital>"
        . "<urlOk><![CDATA[".Router::url(array('controller' => 'HipayWallet', 'action' => 'success', 'plugin' => 'ShopPlus'), true)."]]></urlOk>"
        . "<urlKo><![CDATA[".Router::url(array('controller' => 'HipayWallet', 'action' => 'error', 'plugin' => 'ShopPlus'), true)."]]></urlKo>"
        . "<urlCancel><![CDATA[".Router::url(array('controller' => 'HipayWallet', 'action' => 'cancel', 'plugin' => 'ShopPlus'), true)."]]></urlCancel>"
        . "<urlInstall><![CDATA[".Router::url($this->here, true)."]]></urlInstall>"
        . "<urlLogo><![CDATA[".Router::url('/theme/Obsifight/img/logo.png', true)."]]></urlLogo>"
        . "<!-- optional -->"
        //. "<thirdPartySecurity>compatible</thirdPartySecurity>"
        . "<locale>fr_FR</locale>"
        //. "<issuerAccountLogin>".$offer['data']->issue_email."</issuerAccountLogin>"
        . "<data>"
            . "<user>"
              . "<username>{$this->User->getKey('pseudo')}</username>"
              . "<id>{$this->User->getKey('id')}</id>"
            . "</user>"
            . "<offer>"
              . "<id>{$offer['id']}</id>"
            . "</offer>"
        . "</data>"
        . "<items>"
            . "<item id='1'>"
                . "<name>{$offer['credits']} {$this->Configuration->getMoneyName()}</name>"
                . "<infos>Achat de {$offer['credits']} {$this->Configuration->getMoneyName()} sur {$this->Configuration->getKey('name')}</infos>"
                . "<amount>{$offer['amount']}</amount>"
                . "<categoryId>251</categoryId>"
                . "<quantity>1</quantity>"
                . "<reference>REF1</reference>"
            . "</item>"
        . "</items>"
      . "</order>";
    $xml = trim($xml);
    $privateKey = $offer['private_key'];
    $encodedData = base64_encode($this->__encryptHipayWallet(base64_encode($xml), $privateKey));
    $md5Sign = base64_encode($this->__signHipayWallet(base64_encode($xml), $privateKey));
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

    $this->loadModel('ShopPlus.StripeConfiguration');
    $stripeConfig = $this->StripeConfiguration->find('first');
    if (!empty($stripeConfig) && $stripeConfig['StripeConfiguration']['status']) {
      $this->set('stripe', true);
      $this->set('stripeCreditFor1', $stripeConfig['StripeConfiguration']['credits_for_1']);
      $this->set('stripePublishableKey', $stripeConfig['StripeConfiguration']['publishable_key']);
    } else {
      $this->set('stripe', false);
    }

    $this->loadModel('ShopPlus.PaymillConfiguration');
    $paymillConfig = $this->PaymillConfiguration->find('first');
    if (!empty($paymillConfig) && $paymillConfig['PaymillConfiguration']['status']) {
      $this->set('paymill', true);
      $this->set('paymillCreditFor1', $paymillConfig['PaymillConfiguration']['credits_for_1']);
      $this->set('paymillPublicKey', $paymillConfig['PaymillConfiguration']['public_key']);
    } else {
      $this->set('paymill', false);
    }

    $this->loadModel('ShopPlus.HipayWalletConfiguration');
    $hipayWalletConfig = $this->HipayWalletConfiguration->find('first');
    if (!empty($hipayWalletConfig) && $hipayWalletConfig['HipayWalletConfiguration']['status']) {
      $this->set('hipayWallet', true);
      $this->set('hipayWalletWebsiteId', $hipayWalletConfig['HipayWalletConfiguration']['website_id']);
      $this->set('hipayWalletTestMode', $hipayWalletConfig['HipayWalletConfiguration']['test']);
      // offers
      $this->loadModel('ShopPlus.HipayWalletOffer');
      $hipayWalletOffers = $this->HipayWalletOffer->find('all', array('order' => 'amount DESC'));
      $this->set('hipayWalletOffers', array_map(function ($offer) use ($hipayWalletConfig) {
        list($encodedData, $md5Sign) = $this->__generateHipayWalletForm(array(
          'user_account_id' => $hipayWalletConfig['HipayWalletConfiguration']['user_account_id'],
          'private_key' => $hipayWalletConfig['HipayWalletConfiguration']['private_key'],
          'credits' => $offer['HipayWalletOffer']['credits'],
          'id' => $offer['HipayWalletOffer']['id'],
          'amount' => $offer['HipayWalletOffer']['amount']
        ));
        return array('encodedData' => $encodedData, 'md5Sign' => $md5Sign, 'credits' => $offer['HipayWalletOffer']['credits'], 'amount' => $offer['HipayWalletOffer']['amount']);
      }, $hipayWalletOffers));
    } else {
      $this->set('paymill', false);
    }

    $this->set(compact(
      'paypalOffers',
      'starpassOffers',
      'dedipassStatus'
    ));
  }

}
