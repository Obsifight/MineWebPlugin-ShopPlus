<?php
class PaymentController extends ShopPlusAppController {

  public function addCredit() {
    if (!$this->isConnected)
      throw new ForbiddenException();
  }

}
