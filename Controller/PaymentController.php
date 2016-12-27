<?php
class PaymentController extends ShopPlusAppController {

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

    $this->set(compact(
      'paypalOffers',
      'starpassOffers',
      'dedipassStatus'
    ));
  }

}
