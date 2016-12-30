<?php
class StatsController extends ShopPlusAppController {

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

    $this->set('typesName', array(
      'PAYSAFECARD' => 'Paysafecard',
      'STRIPE' => 'Carte de crédit',
      'PHONE' => 'Dédipass',
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
      $query = $this->CreditsHistory->find('all', array( // get total by day
        'fields' => 'SUM(Offer.amount)',
        'recursive' => 1,
        'group' => 'DAY(`CreditsHistory`.`history_date`), MONTH(`CreditsHistory`.`history_date`), YEAR(`CreditsHistory`.`history_date`)',
        'conditions' => "CreditsHistory.history_date < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01' )" // not this month
      ));
      $datas = array();
      foreach ($query as $data) { // group into array values
        $datas[] = $data[0]['SUM(`Offer`.`amount`)'];
      }
      $dailyIncomesAverage = round(array_sum($datas) / count($datas)); // make average
      unset($query);
      unset($datas);
      unset($data);
      $this->set(compact('dailyIncomesAverage'));
    /* --- incomesThisWeekPercentage --- */
      $incomesThisWeekPercentage = 0; // percentage
      $query = $this->CreditsHistory->find('first', array( // get total by day
        'fields' => 'SUM(Offer.amount)',
        'recursive' => 1,
        'conditions' => 'YEAR(CreditsHistory.history_date) = YEAR(NOW()) AND WEEK(CreditsHistory.history_date) = WEEK(NOW())'
      ));
      $incomesThisWeekPercentage = ($query[0]['SUM(`Offer`.`amount`)'] - $dailyIncomesAverage) / $dailyIncomesAverage * 100;
      $incomesThisWeekPercentage = round($incomesThisWeekPercentage);
      if ($incomesThisWeekPercentage == -0)
        $incomesThisWeekPercentage = 0;
      unset($query);
      $this->set(compact('incomesThisWeekPercentage'));
    /* --- monthlyIncomesAverage --- */
      $monthlyIncomesAverage = 0; // in €
      $query = $this->CreditsHistory->find('all', array( // get total by day
        'fields' => 'SUM(Offer.amount)',
        'recursive' => 1,
        'group' => 'MONTH(`CreditsHistory`.`history_date`), YEAR(`CreditsHistory`.`history_date`)',
        'conditions' => "CreditsHistory.history_date < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01' )" // not this month
      ));
      $datas = array();
      foreach ($query as $data) { // group into array values
        $datas[] = $data[0]['SUM(`Offer`.`amount`)'];
      }
      $monthlyIncomesAverage = round(array_sum($datas) / count($datas)); // make average
      unset($query);
      unset($datas);
      unset($data);
      $this->set(compact('monthlyIncomesAverage'));
    /* --- incomesThisMonthPercentage && incomesThisMonth --- */
      $incomesThisMonth = 0; // in €
      $incomesThisMonthPercentage = 0; // this month
      $query = $this->CreditsHistory->find('first', array( // get total by day
        'fields' => 'SUM(Offer.amount)',
        'recursive' => 1,
        'conditions' => 'YEAR(CreditsHistory.history_date) = YEAR(NOW()) AND MONTH(CreditsHistory.history_date) = MONTH(NOW())'
      ));
      $incomesThisMonth = $query[0]['SUM(`Offer`.`amount`)'];
      $incomesThisMonthPercentage = ($query[0]['SUM(`Offer`.`amount`)'] - $monthlyIncomesAverage) / $monthlyIncomesAverage * 100;
      $incomesThisMonthPercentage = round($incomesThisMonthPercentage);
      if ($incomesThisMonthPercentage == -0)
        $incomesThisMonthPercentage = 0;
      unset($query);
      $this->set(compact('incomesThisMonthPercentage'));
      $this->set(compact('incomesThisMonth'));
    /* --- incomesToday --- */
      $incomesToday = 0; // in €
      $query = $this->CreditsHistory->find('first', array( // get total by day
        'fields' => 'SUM(Offer.amount)',
        'recursive' => 1,
        'conditions' => 'YEAR(CreditsHistory.history_date) = YEAR(NOW()) AND MONTH(CreditsHistory.history_date) = MONTH(NOW()) AND DAY(CreditsHistory.history_date) = DAY(NOW())'
      ));
      $incomesToday = $query[0]['SUM(`Offer`.`amount`)'];
      unset($query);
      $this->set(compact('incomesToday'));
    /* --- creditsPurchasesByMonthByModes --- */
      $creditsPurchasesByMonthByModes = array(
        'CREDIT_CARD' => array(
          intval(date('m', strtotime('-6 month'))) => 0,
          intval(date('m', strtotime('-5 month'))) => 0,
          intval(date('m', strtotime('-4 month'))) => 0,
          intval(date('m', strtotime('-3 month'))) => 0,
          intval(date('m', strtotime('-2 month'))) => 0,
          intval(date('m', strtotime('-1 month'))) => 0,
          intval(date('m')) => 0
        ),
        'PHONE' => array(
          intval(date('m', strtotime('-6 month'))) => 0,
          intval(date('m', strtotime('-5 month'))) => 0,
          intval(date('m', strtotime('-4 month'))) => 0,
          intval(date('m', strtotime('-3 month'))) => 0,
          intval(date('m', strtotime('-2 month'))) => 0,
          intval(date('m', strtotime('-1 month'))) => 0,
          intval(date('m')) => 0
        ),
        'PAYPAL' => array(
          intval(date('m', strtotime('-6 month'))) => 0,
          intval(date('m', strtotime('-5 month'))) => 0,
          intval(date('m', strtotime('-4 month'))) => 0,
          intval(date('m', strtotime('-3 month'))) => 0,
          intval(date('m', strtotime('-2 month'))) => 0,
          intval(date('m', strtotime('-1 month'))) => 0,
          intval(date('m')) => 0
        ),
        'PAYSAFECARD' => array(
          intval(date('m', strtotime('-6 month'))) => 0,
          intval(date('m', strtotime('-5 month'))) => 0,
          intval(date('m', strtotime('-4 month'))) => 0,
          intval(date('m', strtotime('-3 month'))) => 0,
          intval(date('m', strtotime('-2 month'))) => 0,
          intval(date('m', strtotime('-1 month'))) => 0,
          intval(date('m')) => 0
        )
      ); // last 6 months
      $query = $this->CreditsHistory->find('all', array( // get total by day
        'fields' => array(
          'Offer.amount', 'Offer.type', 'MONTH(`CreditsHistory`.`history_date`) AS `month`'
        ),
        'recursive' => 1,
        'conditions' => "CreditsHistory.history_date > DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH), '%Y/%m/01' )"
      ));
      // format data
      $formattedDatas = array();
      foreach ($query as $data) {
        $formattedDatas[] = array(
          'amount' => $data['Offer']['amount'],
          'type' => $data['Offer']['type'],
          'month' => $data[0]['month']
        );
      }
      // group by modes
      $datasByModes = array();
      foreach ($formattedDatas as $data) {
        $datasByModes[$data['type']][] = array(
          'month' => $data['month'],
          'amount' => $data['amount']
        );
      }
      // group by month
      $datasByModesByMonth = array();
      foreach ($datasByModes as $type => $datas) {
        foreach ($datas as $data) {
          if (isset($datasByModesByMonth[$type][$data['month']]))
            $datasByModesByMonth[$type][$data['month']] += floatval($data['amount']);
          else
            $datasByModesByMonth[$type][$data['month']] = floatval($data['amount']);
        }
      }
      // merge
      foreach ($creditsPurchasesByMonthByModes as $type => $datas) {
        foreach ($datas as $month => $data) {
          if (isset($datasByModesByMonth[$type][$month]))
            $creditsPurchasesByMonthByModes[$type][$month] = $datasByModesByMonth[$type][$month];
        }
      }
      unset($query);
      unset($data);
      unset($formattedDatas);
      unset($datasByModesByMonth);
      unset($datasByModes);
      $this->set(compact('creditsPurchasesByMonthByModes'));
    /* --- creditsPurchasesByModes --- */
      $creditsPurchasesByModes = array(
        'PAYSAFECARD' => array(),
        'CREDIT_CARD' => array(),
        'PAYPAL' => array(),
        'PHONE' => array()
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
    /* --- itemsPurchasesThisMonth --- */
      $itemsPurchasesThisMonth = 0; // count items
      $itemsPurchasesThisMonth = $this->ItemsHistory->find('count', array(
        'conditions' => 'MONTH(ItemsHistory.created) = MONTH(NOW()) AND YEAR(ItemsHistory.created) = YEAR(NOW())'
      ));
      $this->set(compact('itemsPurchasesThisMonth'));
    /* --- itemsPurchasesToday --- */
      $itemsPurchasesToday = 0; // count items
      $itemsPurchasesToday = $this->ItemsHistory->find('count', array(
        'conditions' => 'DAY(ItemsHistory.created) = DAY(NOW()) AND MONTH(ItemsHistory.created) = MONTH(NOW()) AND YEAR(ItemsHistory.created) = YEAR(NOW())'
      ));
      $this->set(compact('itemsPurchasesToday'));
    /* --- itemsPurchasesByMonthByServers --- */
      // find servers
      $this->loadModel('OpenShop.RconServer');
      $findServers = $this->RconServer->find('all');
      $servers = array();
      foreach ($findServers as $server) {
        $servers[$server['RconServer']['id']] = $server['RconServer']['name'];
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
      $query = $this->ItemsHistory->find('all', array(
        'fields' => array(
          'Item.servers', 'MONTH(`ItemsHistory`.`created`) AS `month`'
        ),
        'recursive' => 1,
        'conditions' => "ItemsHistory.created > DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH), '%Y/%m/01' )"
      ));
      // format data
      $formattedDatas = array();
      foreach ($query as $data) {
        $formattedDatas[] = array(
          'server' => json_decode($data['Item']['servers'], true)[0], // based on first server configured
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
      $query = $this->ItemsHistory->find('all', array( // get total by day
        'fields' => 'COUNT(id)',
        'group' => 'MONTH(`ItemsHistory`.`created`), YEAR(`ItemsHistory`.`created`)',
        'conditions' => "ItemsHistory.created < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01' )" // not this month
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
    /* --- itemsPurchases --- */
      $itemsPurchases = array(); // amount of purchases by items (on any servers)
      $query = $this->ItemsHistory->find('all', array(
        'recursive' => 1,
        'fields' => array(
          'Item.name', 'COUNT(ItemsHistory.id) AS count'
        ),
        'group' => 'item_id'
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
      $this->loadModel('OpenShop.Item');
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
      $query = $this->ItemsHistory->find('all', array( // get total by day
        'fields' => array(
          'Item.name', 'Item.servers', 'MONTH(`ItemsHistory`.`created`) AS `month`'
        ),
        'recursive' => 1,
        'conditions' => "ItemsHistory.created > DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH), '%Y/%m/01' )"
      ));
      // format data
      $formattedDatas = array();
      foreach ($query as $datas) {
        $formattedDatas[] = array(
          'name' => $datas['Item']['name'],
          'server' => json_decode($datas['Item']['servers'], true)[0],
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
