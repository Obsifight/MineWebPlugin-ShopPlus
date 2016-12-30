<?php
App::uses('CakeEventListener', 'Event');

class ShopplusDedipassEventListener implements CakeEventListener {

  private $controller;

  public function __construct($request, $response, $controller) {
    $this->controller = $controller;
  }

  public function implementedEvents() {
    return array(
      'requestPage' => 'dedipassIpn',
    );
  }

  public function dedipassIpn($event) {
    if($this->controller->params['controller'] != "payment" || $this->controller->params['action'] != "dedipass_ipn")
      return;
      $this->autoRender = false;
    if($this->controller->request->is('post') && $this->controller->Permissions->can('CREDIT_ACCOUNT')) {
      $this->controller->loadModel('Shop.DedipassConfig');
      $search = $this->controller->DedipassConfig->find('first');
      $public_key = $search['DedipassConfig']['public_key'];
      $code = isset($this->controller->request->data['code']) ? preg_replace('/[^a-zA-Z0-9]+/', '', $this->controller->request->data['code']) : '';
      $rate = isset($this->controller->request->data['rate']) ? preg_replace('/[^a-zA-Z0-9\-]+/', '', $this->controller->request->data['rate']) : '';
      // Validation des champs
      if(empty($code)) {
        $this->controller->Session->setFlash($this->controller->Lang->get('SHOP__DEDIPASS_PAYMENT_ERROR_EMPTY_CODE'), 'default.error');
        $this->controller->redirect(array('action' => 'dedipass'));
      } elseif (empty($rate)) {
        $this->controller->Session->setFlash($this->controller->Lang->get('SHOP__DEDIPASS_PAYMENT_ERROR_EMPTY_RATE'), 'default.error');
        $this->controller->redirect(array('action' => 'dedipass'));
      } else {

        if($this->controller->isConnected) {

            $dedipass = file_get_contents('http://api.dedipass.com/v1/pay/?public_key='.$public_key.'&private_key='.Configure::read('Shopplus.dedipass.private_key').'&rate='.$rate.'&code='.$code);
            $dedipass = json_decode($dedipass);

            $code = $dedipass->code; // Le code
            $rate = $dedipass->rate; // Le palier

            if($dedipass->status == 'success') {
              // Le code est valide
              $virtual_currency = $dedipass->virtual_currency; // Nombre de points à créditer à l'utilisateur

              $user_money = $this->controller->User->getKey('money');
              $new_money = $user_money + floatval($virtual_currency);
              $this->controller->User->setKey('money', $new_money);

              $this->controller->History->set('BUY_MONEY_DEDIPASS', 'buy');

              $this->controller->loadModel('Shop.DedipassHistory');
              $this->controller->DedipassHistory->create();
              $this->controller->DedipassHistory->set(array(
                'user_id' => $this->controller->User->getKey('id'),
                'code' => $code,
                'rate' => $rate,
                'credits_gived' => $virtual_currency,
                'shopplus-payout' => $dedipass->payout
              ));
              $this->controller->DedipassHistory->save();

              $this->controller->Session->setFlash($this->controller->Lang->get('SHOP__DEDIPASS_PAYMENT_SUCCESS', array('{MONEY}' => $virtual_currency, '{MONEY_NAME}' => $this->controller->Configuration->getMoneyName())), 'default.success');
              $this->controller->redirect(array('controller' => 'shop', 'action' => 'index'));

            } else {
              $this->controller->Session->setFlash($this->controller->Lang->get('SHOP__DEDIPASS_PAYMENT_ERROR_INVAID_CODE'), 'default.error');
              $this->controller->redirect(array('action' => 'dedipass'));
            }

        } else {
          $this->controller->Session->setFlash($this->controller->Lang->get('SHOP__DEDIPASS_PAYMENT_ERROR_NOT_CONNECTED', array('{CODE}' => $code)), 'default.error');
          $this->controller->redirect(array('controller' => 'shop', 'action' => 'index'));
        }
      }
    }
    throw new NotFoundException();
  }

}
