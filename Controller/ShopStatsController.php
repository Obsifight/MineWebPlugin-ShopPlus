<?php
class ShopStatsController extends ShopPlusAppController {

  public function beforeFilter() {
    parent::beforeFilter();
    if (!$this->isConnected || !$this->Permissions->can('OPENSHOP__ADMIN_VIEW_STATS'))
      throw new ForbiddenException();
    $this->layout = 'admin';
  }

  public function admin_index() {
    $this->set('title_for_layout', $this->Lang->get('SHOPPLUS__ADMIN_STATS'));

    $this->loadModel('Shop.DedipassHistory');
    $this->loadModel('Shop.PaypalHistory');
    $this->loadModel('ShopPlus.StripeHistory');
    $this->loadModel('Paysafecard.PaymentHistory');
    $this->loadModel('Shop.ItemsBuyHistory');
    $this->loadModel('Shop.Item');
    $db = $this->DedipassHistory->getDataSource();

    $this->set('typesName', array(
      'PAYSAFECARD' => 'Paysafecard',
      'STRIPE' => 'Carte de crédit',
      'DEDIPASS' => 'Dédipass',
      'PAYPAL' => 'PayPal'
    ));

    $this->set('months', array(
      intval(date('m', strtotime('-6 month'))),
      intval(date('m', strtotime('-5 month'))),
      intval(date('m', strtotime('-4 month'))),
      intval(date('m', strtotime('-3 month'))),
      intval(date('m', strtotime('-2 month'))),
      intval(date('m', strtotime('-1 month'))),
      intval(date('m'))
    ));
    $this->set('monthsName', array(
      1 => 'Janvier',
      2 => 'Février',
      3 => 'Mars',
      4 => 'Avril',
      5 => 'Mai',
      6 => 'Juin',
      7 => 'Juillet',
      8 => 'Août',
      9 => 'Septembre',
      10 => 'Octobre',
      11 => 'Novembre',
      12 => 'Décembre'
    ));
    // *******
    // Incomes
    // *******
    /* --- dailyIncomesAverage --- */
      $dailyIncomesAverage = 0; // in €
      $query = $db->fetchAll(
        "SELECT SUM(`total`) AS `total`, DAY(`created`) AS `day`, MONTH(`created`) AS `month` FROM "
        . "("
        . " SELECT SUM(`shopplus-payout`) AS `total`, `created` FROM  `shop__dedipass_histories` WHERE `shop__dedipass_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY DAY(`shop__dedipass_histories`.`created`), MONTH(`shop__dedipass_histories`.`created`), YEAR(`shop__dedipass_histories`.`created`)"
        . " UNION"
        . " SELECT SUM(`payment_amount`) AS `total`, `created` FROM `shop__paypal_histories` WHERE `shop__paypal_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY DAY(`shop__paypal_histories`.`created`), MONTH(`shop__paypal_histories`.`created`), YEAR(`shop__paypal_histories`.`created`)"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total`, `created` FROM `paysafecard__payment_histories` WHERE `paysafecard__payment_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY DAY(`paysafecard__payment_histories`.`created`), MONTH(`paysafecard__payment_histories`.`created`), YEAR(`paysafecard__payment_histories`.`created`)"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total`, `created` FROM `shopplus__stripe_histories` WHERE `shopplus__stripe_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY DAY(`shopplus__stripe_histories`.`created`), MONTH(`shopplus__stripe_histories`.`created`), YEAR(`shopplus__stripe_histories`.`created`)"
        . ") t"
        . " GROUP BY DAY(`created`), MONTH(`created`), YEAR(`created`)"
      );
      $datas = array();
      foreach ($query as $data) { // group into array values
        $datas[] = $data[0]['total'];
      }
      $dailyIncomesAverage = round(array_sum($datas) / count($datas)); // make average
      unset($query);
      unset($datas);
      unset($data);
      $this->set(compact('dailyIncomesAverage'));
    /* --- incomesThisWeekPercentage --- */
      $incomesThisWeekPercentage = 0; // percentage
      $query = $db->fetchAll(
        "SELECT SUM(`total`) AS `total` FROM "
        . "("
        . " SELECT SUM(`shopplus-payout`) AS `total` FROM  `shop__dedipass_histories` WHERE YEAR(`shop__dedipass_histories`.`created`) = YEAR(NOW()) AND WEEK(`shop__dedipass_histories`.`created`) = WEEK(NOW())"
        . " UNION"
        . " SELECT SUM(`payment_amount`) AS `total` FROM `shop__paypal_histories` WHERE YEAR(`shop__paypal_histories`.`created`) = YEAR(NOW()) AND WEEK(`shop__paypal_histories`.`created`) = WEEK(NOW())"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total` FROM `paysafecard__payment_histories` WHERE YEAR(`paysafecard__payment_histories`.`created`) = YEAR(NOW()) AND WEEK(`paysafecard__payment_histories`.`created`) = WEEK(NOW())"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total` FROM `shopplus__stripe_histories` WHERE YEAR(`shopplus__stripe_histories`.`created`) = YEAR(NOW()) AND WEEK(`shopplus__stripe_histories`.`created`) = WEEK(NOW())"
        . ") t"
      );
      $incomesThisWeekPercentage = ($query[0][0]['total'] - $dailyIncomesAverage) / $dailyIncomesAverage * 100;
      $incomesThisWeekPercentage = round($incomesThisWeekPercentage);
      if ($incomesThisWeekPercentage == -0)
        $incomesThisWeekPercentage = 0;
      unset($query);
      $this->set(compact('incomesThisWeekPercentage'));
    /* --- monthlyIncomesAverage --- */
      $monthlyIncomesAverage = 0; // in €
      $query = $db->fetchAll(
        "SELECT SUM(`total`) AS `total`, MONTH(`created`) AS `month` FROM "
        . "("
        . " SELECT SUM(`shopplus-payout`) AS `total`, `created` FROM  `shop__dedipass_histories` WHERE `shop__dedipass_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY MONTH(`shop__dedipass_histories`.`created`), YEAR(`shop__dedipass_histories`.`created`)"
        . " UNION"
        . " SELECT SUM(`payment_amount`) AS `total`, `created` FROM `shop__paypal_histories` WHERE `shop__paypal_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY MONTH(`shop__paypal_histories`.`created`), YEAR(`shop__paypal_histories`.`created`)"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total`, `created` FROM `paysafecard__payment_histories` WHERE `paysafecard__payment_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY MONTH(`paysafecard__payment_histories`.`created`), YEAR(`paysafecard__payment_histories`.`created`)"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total`, `created` FROM `shopplus__stripe_histories` WHERE `shopplus__stripe_histories`.`created` < DATE_FORMAT( CURRENT_DATE, '%Y/%m/31' ) GROUP BY MONTH(`shopplus__stripe_histories`.`created`), YEAR(`shopplus__stripe_histories`.`created`)"
        . ") t"
        . " GROUP BY MONTH(`created`), YEAR(`created`)"
      );
      $datas = array();
      foreach ($query as $data) { // group into array values
        $datas[] = $data[0]['total'];
      }
      $monthlyIncomesAverage = round(array_sum($datas) / count($datas)); // make average
      unset($query);
      unset($datas);
      unset($data);
      $this->set(compact('monthlyIncomesAverage'));
    /* --- incomesThisMonthPercentage && incomesThisMonth --- */
      $incomesThisMonth = 0; // in €
      $incomesThisMonthPercentage = 0; // this month
      $query = $db->fetchAll(
        "SELECT SUM(`total`) AS `total` FROM "
        . "("
        . " SELECT SUM(`shopplus-payout`) AS `total` FROM  `shop__dedipass_histories` WHERE YEAR(`shop__dedipass_histories`.`created`) = YEAR(NOW()) AND MONTH(`shop__dedipass_histories`.`created`) = MONTH(NOW())"
        . " UNION"
        . " SELECT SUM(`payment_amount`) AS `total` FROM `shop__paypal_histories` WHERE YEAR(`shop__paypal_histories`.`created`) = YEAR(NOW()) AND MONTH(`shop__paypal_histories`.`created`) = MONTH(NOW())"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total` FROM `paysafecard__payment_histories` WHERE YEAR(`paysafecard__payment_histories`.`created`) = YEAR(NOW()) AND MONTH(`paysafecard__payment_histories`.`created`) = MONTH(NOW())"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total` FROM `shopplus__stripe_histories` WHERE YEAR(`shopplus__stripe_histories`.`created`) = YEAR(NOW()) AND MONTH(`shopplus__stripe_histories`.`created`) = MONTH(NOW())"
        . ") t"
      );
      $incomesThisMonth = $query[0][0]['total'];
      $incomesThisMonthPercentage = ($incomesThisMonth - $monthlyIncomesAverage) / $monthlyIncomesAverage * 100;
      $incomesThisMonthPercentage = round($incomesThisMonthPercentage);
      if ($incomesThisMonthPercentage == -0)
        $incomesThisMonthPercentage = 0;
      unset($query);
      $this->set(compact('incomesThisMonthPercentage'));
      $this->set(compact('incomesThisMonth'));
    /* --- incomesToday --- */ // TODO
      $incomesToday = 0; // in €
      $query = $db->fetchAll(
        "SELECT SUM(`total`) AS `total` FROM "
        . "("
        . " SELECT SUM(`shopplus-payout`) AS `total` FROM  `shop__dedipass_histories` WHERE YEAR(`shop__dedipass_histories`.`created`) = YEAR(NOW()) AND MONTH(`shop__dedipass_histories`.`created`) = MONTH(NOW()) AND DAY(`shop__dedipass_histories`.`created`) = DAY(NOW())"
        . " UNION"
        . " SELECT SUM(`payment_amount`) AS `total` FROM `shop__paypal_histories` WHERE YEAR(`shop__paypal_histories`.`created`) = YEAR(NOW()) AND MONTH(`shop__paypal_histories`.`created`) = MONTH(NOW()) AND DAY(`shop__paypal_histories`.`created`) = DAY(NOW())"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total` FROM `paysafecard__payment_histories` WHERE YEAR(`paysafecard__payment_histories`.`created`) = YEAR(NOW()) AND MONTH(`paysafecard__payment_histories`.`created`) = MONTH(NOW()) AND DAY(`paysafecard__payment_histories`.`created`) = DAY(NOW())"
        . " UNION"
        . " SELECT SUM(`amount`) AS `total` FROM `shopplus__stripe_histories` WHERE YEAR(`shopplus__stripe_histories`.`created`) = YEAR(NOW()) AND MONTH(`shopplus__stripe_histories`.`created`) = MONTH(NOW()) AND DAY(`shopplus__stripe_histories`.`created`) = DAY(NOW())"
        . ") t"
      );
      $incomesToday = $query[0][0]['total'];
      unset($query);
      $this->set(compact('incomesToday'));
    /* --- creditsPurchasesByMonthByModes --- */
      $creditsPurchasesByMonthByModes = array();
      $modes = array(
        'PAYSAFECARD' => array('table' => 'paysafecard__payment_histories', 'amount_column' => 'amount'),
        'PAYPAL' => array('table' => 'shop__paypal_histories', 'amount_column' => 'payment_amount'),
        'DEDIPASS' => array('table' => 'shop__dedipass_histories', 'amount_column' => 'shopplus-payout'),
        'STRIPE' => array('table' => 'shopplus__stripe_histories', 'amount_column' => 'amount')
      );
      foreach ($modes as $key => $values) {
        // sample
        $creditsPurchasesByMonthByModes[$key] = array(
          intval(date('m', strtotime('-6 month'))) => 0,
          intval(date('m', strtotime('-5 month'))) => 0,
          intval(date('m', strtotime('-4 month'))) => 0,
          intval(date('m', strtotime('-3 month'))) => 0,
          intval(date('m', strtotime('-2 month'))) => 0,
          intval(date('m', strtotime('-1 month'))) => 0,
          intval(date('m')) => 0
        );
        // query
        $query = $db->fetchAll(
          "SELECT SUM(`{$values['amount_column']}`) AS `amount`, MONTH(`created`) AS `month`"
          . " FROM `{$values['table']}`"
          . " WHERE `created` > DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH), '%Y-%m-01 00:00:00' )"
          . " GROUP BY MONTH(`created`)"
        );
        foreach ($query as $row) {
          // edit
          $creditsPurchasesByMonthByModes[$key][$row[0]['month']] = $row[0]['amount'];
        }
      }
      unset($row);
      unset($key);
      unset($values);
      unset($query);
      $this->set(compact('creditsPurchasesByMonthByModes'));
    /* --- creditsPurchasesByModes --- */
      $creditsPurchasesByModes = array(
        'PAYSAFECARD' => array(),
        'STRIPE' => array(),
        'PAYPAL' => array(),
        'DEDIPASS' => array()
      ); // amount of purchases by mode (PayPal/Dédipass...) (on any servers)
      foreach ($creditsPurchasesByMonthByModes as $type => $datas) {
        // add all data from all 6 last months
        foreach ($datas as $month => $data) {
          if ($data > 0)
            $creditsPurchasesByModes[$type][] = $data;
        }
        // sum
        if (count($creditsPurchasesByModes[$type]) > 0)
          $creditsPurchasesByModes[$type] = array_sum($creditsPurchasesByModes[$type]);
        else
          $creditsPurchasesByModes[$type] = 0;
      }
      unset($datas);
      $this->set(compact('creditsPurchasesByModes'));
    // *********
    // Purchases
    // *********
    /* --- itemsPurchasesThisMonth --- */ // TODO
      $itemsPurchasesThisMonth = 0; // count items
      $itemsPurchasesThisMonth = $this->ItemsBuyHistory->find('count', array(
        'conditions' => 'MONTH(ItemsBuyHistory.created) = MONTH(NOW()) AND YEAR(ItemsBuyHistory.created) = YEAR(NOW())'
      ));
      $this->set(compact('itemsPurchasesThisMonth'));
    /* --- itemsPurchasesToday --- */
      $itemsPurchasesToday = 0; // count items
      $itemsPurchasesToday = $this->ItemsBuyHistory->find('count', array(
        'conditions' => 'DAY(ItemsBuyHistory.created) = DAY(NOW()) AND MONTH(ItemsBuyHistory.created) = MONTH(NOW()) AND YEAR(ItemsBuyHistory.created) = YEAR(NOW())'
      ));
      $this->set(compact('itemsPurchasesToday'));
    /* --- itemsPurchasesByMonthByServers --- */
      // find servers
      $this->loadModel('Server');
      $findServers = $this->Server->find('all');
      $servers = array();
      foreach ($findServers as $server) {
        $servers[$server['Server']['id']] = $server['Server']['name'];
      }
      $this->set(compact('servers'));
      // format sample data
      $itemsPurchasesByMonthByServers = array();
      foreach ($servers as $serverId => $serverName) {
        $itemsPurchasesByMonthByServers[$serverId] = array(
          intval(date('m', strtotime('-6 month'))) => 0,
          intval(date('m', strtotime('-5 month'))) => 0,
          intval(date('m', strtotime('-4 month'))) => 0,
          intval(date('m', strtotime('-3 month'))) => 0,
          intval(date('m', strtotime('-2 month'))) => 0,
          intval(date('m', strtotime('-1 month'))) => 0,
          intval(date('m')) => 0
        );
      }
      // query data
      $query = $this->ItemsBuyHistory->find('all', array(
        'fields' => array(
          'Item.servers', 'MONTH(`ItemsBuyHistory`.`created`) AS `month`'
        ),
        'recursive' => 1,
        'conditions' => "ItemsBuyHistory.created > DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH), '%Y-%m-01 00:00:00' )"
      ));
      // format data
      $formattedDatas = array();
      foreach ($query as $data) {
        $formattedDatas[] = array(
          'server' => unserialize($data['Item']['servers'])[0], // based on first server configured
          'month' => $data[0]['month']
        );
      }
      // group by servers & months
      $dataByServers = array();
      foreach ($formattedDatas as $data) {
        if (isset($dataByServers[$data['server']][$data['month']]))
          $dataByServers[$data['server']][$data['month']]++;
        else
          $dataByServers[$data['server']][$data['month']] = 1;
      }
      // merge
      foreach ($itemsPurchasesByMonthByServers as $server => $months) {
        if (isset($dataByServers[$server]))
          foreach ($dataByServers[$server] as $month => $data) {
            $itemsPurchasesByMonthByServers[$server][$month] = $data;
          }
      }
      unset($query);
      unset($formattedDatas);
      unset($dataByServers);
      $this->set(compact('itemsPurchasesByMonthByServers'));
    /* --- monthlyItemsPurchasesAverage --- */
      $monthlyItemsPurchasesAverage = 0; // percentage
      $query = $this->ItemsBuyHistory->find('all', array( // get total by day
        'fields' => 'COUNT(id)',
        'group' => 'MONTH(`ItemsBuyHistory`.`created`), YEAR(`ItemsBuyHistory`.`created`)',
        'conditions' => "ItemsBuyHistory.created < DATE_FORMAT( CURRENT_DATE, '%Y-%m-01 00:00:00' )" // not this month
      ));
      $datas = array();
      foreach ($query as $data) { // group into array values
        $datas[] = intval($data[0]['COUNT(id)']);
      }
      $monthlyItemsPurchasesAverage = (array_sum($datas) / count($datas)); // make average
      unset($query);
      unset($datas);
      unset($data);
      $this->set(compact('monthlyItemsPurchasesAverage'));
    /* --- itemsPurchasesByServers --- */
      // sample data
      $itemsPurchasesByServers = array(); // amount of purchases (sum of all items) by server
      foreach ($servers as $serverId => $serverName) {
        $itemsPurchasesByServers[$serverId] = array();
      }
      // calcul
      foreach ($itemsPurchasesByMonthByServers as $server => $datas) {
        // add all data from all 6 last months
        foreach ($datas as $month => $data) {
          if ($data > 0)
            $itemsPurchasesByServers[$server][] = $data;
        }
        // sum
        if (count($itemsPurchasesByServers[$server]) > 0)
          $itemsPurchasesByServers[$server] = array_sum($itemsPurchasesByServers[$server]);
        else
          $itemsPurchasesByServers[$server] = 0;
      }
      unset($datas);
      $this->set(compact('itemsPurchasesByServers'));
    /* --- itemsPurchases --- */ // TODO
      $itemsPurchases = array(); // amount of purchases by items (on any servers)
      $query = $this->ItemsBuyHistory->find('all', array(
        'recursive' => 1,
        'fields' => array(
          'Item.name', 'COUNT(ItemsBuyHistory.id) AS count'
        ),
        'group' => 'item_id',
        'order' => 'COUNT(ItemsBuyHistory.id) DESC',
        'limit' => '5'
      ));
      // format data
      foreach ($query as $key => $data) {
        $itemsPurchases[$data['Item']['name']] = $data[0]['count'];
      }
      unset($data);
      unset($query);
      $this->set(compact('itemsPurchases'));
    /* --- itemsPurchasesByItemByMonthByServers --- */
      // get all items name
      $items = $this->Item->find('all');
      $itemsNames = array();
      foreach ($items as $item) {
        $itemsNames[$item['Item']['name']] = 0;
      }
      // sample data
      $itemsPurchasesByItemByMonthByServers = array(); // list of items purchases last 3 months per server
      foreach ($servers as $serverId => $serverName) {
        $itemsPurchasesByItemByMonthByServers[$serverId] = array(
          intval(date('m', strtotime('-2 month'))) => $itemsNames,
          intval(date('m', strtotime('-1 month'))) => $itemsNames,
          intval(date('m')) => $itemsNames
        );
      }
      // query
      $query = $this->ItemsBuyHistory->find('all', array( // get total by day
        'fields' => array(
          'Item.name', 'Item.servers', 'MONTH(`ItemsBuyHistory`.`created`) AS `month`'
        ),
        'recursive' => 1,
        'conditions' => "ItemsBuyHistory.created > DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH), '%Y-%m-01 00:00:00' )"
      ));
      // format data
      $formattedDatas = array();
      foreach ($query as $datas) {
        $formattedDatas[] = array(
          'name' => $datas['Item']['name'],
          'server' => unserialize($datas['Item']['servers'])[0],
          'month' => $datas[0]['month']
        );
      }
      // group by servers
      $datasByServers = array();
      foreach ($formattedDatas as $history) {
        $datasByServers[$history['server']][] = array(
          'name' => $history['name'],
          'month' => $history['month']
        );
      }
      // group by month
      $datasByServersByMonths = array();
      foreach ($datasByServers as $server => $histories) {
        foreach ($histories as $history) {
          if (isset($datasByServersByMonths[$server][$history['month']][$history['name']]))
            $datasByServersByMonths[$server][$history['month']][$history['name']]++;
          else
            $datasByServersByMonths[$server][$history['month']][$history['name']] = 1;
        }
      }
      // merge
      foreach ($itemsPurchasesByItemByMonthByServers as $server => $months) {
        foreach ($months as $month => $items) {
          if (isset($datasByServersByMonths[$server][$month]))
            $itemsPurchasesByItemByMonthByServers[$server][$month] = array_merge($itemsPurchasesByItemByMonthByServers[$server][$month], $datasByServersByMonths[$server][$month]);
        }
      }
      unset($history);
      unset($histories);
      unset($datasByServersByMonths);
      unset($datasByServers);
      unset($formattedDatas);
      unset($query);
      unset($datas);
      $this->set(compact('itemsPurchasesByItemByMonthByServers'));
  }
}
