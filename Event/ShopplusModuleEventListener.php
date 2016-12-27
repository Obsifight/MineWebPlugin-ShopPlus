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

    }
  }

}
