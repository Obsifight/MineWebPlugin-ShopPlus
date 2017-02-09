<?php
App::uses('CakeEventListener', 'Event');

class ShopplusModuleEventListener implements CakeEventListener {

  private $controller;

  public function __construct($request, $response, $controller) {
    $this->controller = $controller;
  }

  public function implementedEvents() {
    return array(
      'onLoadPage' => 'setupVarsForModule',
    );
  }

  public function setupVarsForModule($event) {
    if($this->controller->params['controller'] == "payment" && $this->controller->params['action'] == "admin_index") {

      $this->controller->loadModel('ShopPlus.StripeConfiguration');
      $stripeConfig = $this->controller->StripeConfiguration->find('first');
      ModuleComponent::$vars['stripeConfig'] = $stripeConfig;

      $this->controller->loadModel('ShopPlus.PaymillConfiguration');
      $paymill = $this->controller->PaymillConfiguration->find('first');
      ModuleComponent::$vars['paymillConfig'] = $paymill;

      ModuleComponent::$vars['permissions'] = array(
        'SHOPPLUS__ADMIN_CONFIG_STRIPE' => $this->controller->Permissions->can('SHOPPLUS__ADMIN_CONFIG_STRIPE'),
        'SHOPPLUS__ADMIN_VIEW_STRIPE_HISTORY' => $this->controller->Permissions->can('SHOPPLUS__ADMIN_VIEW_STRIPE_HISTORY'),
        'SHOPPLUS__ADMIN_CONFIG_PAYMILL' => $this->controller->Permissions->can('SHOPPLUS__ADMIN_CONFIG_PAYMILL'),
        'SHOPPLUS__ADMIN_VIEW_PAYMILL_HISTORY' => $this->controller->Permissions->can('SHOPPLUS__ADMIN_VIEW_PAYMILL_HISTORY')
      );

    }
  }

}
