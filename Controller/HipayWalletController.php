<?php
class HipayWalletController extends ShopPlusAppController {

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Security->unlockedActions = array('ipn');
  }

  public function ipn() {

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
