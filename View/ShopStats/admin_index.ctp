<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script type="text/javascript">
Object.values = function (obj) {
  var vals = []
  for (var key in obj)
    if (obj.hasOwnProperty(key))
      vals.push(obj[key])
  return vals
}
</script>
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="row">

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-aqua">
            <span class="info-box-icon">
              <i class="fa fa-usd"></i>
            </span>

            <div class="info-box-content">
              <span class="info-box-text"><?= $Lang->get('SHOPPLUS__DAILY_INCOMES_AVERAGE') ?></span>
              <span class="info-box-number">~ <?= number_format($dailyIncomesAverage, 2, ',', ' ') ?> €</span>

              <div class="progress">
                <div class="progress-bar" style="width: <?= ($incomesThisWeekPercentage >= 0) ? $incomesThisWeekPercentage : '0' ?>%"></div>
              </div>
              <span class="progress-description">
                <?= ($incomesThisWeekPercentage >= 0) ? '+'.$incomesThisWeekPercentage : $incomesThisWeekPercentage ?>% <?= $Lang->get('SHOPPLUS__THIS_WEEK') ?>
              </span>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-green">
            <span class="info-box-icon">
              <i class="fa fa-usd"></i>
            </span>

            <div class="info-box-content">
              <span class="info-box-text"><?= $Lang->get('SHOPPLUS__MONTHLY_INCOMES_AVERAGE') ?></span>
              <span class="info-box-number">~ <?= number_format($monthlyIncomesAverage, 2, ',', ' ') ?> €</span>

              <div class="progress">
                <div class="progress-bar" style="width: <?= ($incomesThisMonthPercentage >= 0) ? $incomesThisMonthPercentage : '0' ?>%"></div>
              </div>
              <span class="progress-description">
                <?= ($incomesThisMonthPercentage >= 0) ? '+'.$incomesThisMonthPercentage : $incomesThisMonthPercentage ?>% <?= $Lang->get('SHOPPLUS__THIS_MONTH') ?>
              </span>
            </div>
          </div>
        </div>

        <div class="clearfix visible-sm-block"></div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-yellow">
            <span class="info-box-icon">
              <i class="fa fa-credit-card"></i>
            </span>

            <div class="info-box-content">
              <span class="info-box-text"><?= $Lang->get('SHOPPLUS__INCOMES_THIS_MONTH') ?></span>
              <span class="info-box-number"><?= number_format($incomesThisMonth, 2, ',', ' ') ?> €</span>

              <span class="progress-description">
                <em>+<?= number_format($incomesToday, 0, ',', ' ') ?> € <?= $Lang->get('SHOPPLUS__TODAY') ?></em>
              </span>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-red">
            <span class="info-box-icon">
              <i class="fa fa-shopping-cart"></i>
            </span>

            <div class="info-box-content">
              <span class="info-box-text"><?= $Lang->get('SHOPPLUS__ITEMS_PURCHASES_THIS_MONTH') ?></span>
              <span class="info-box-number"><?= number_format($itemsPurchasesThisMonth, 0, ',', ' ') ?></span>

              <span class="progress-description">
                <em>+<?= number_format($itemsPurchasesToday, 0, ',', ' ') ?> <?= $Lang->get('SHOPPLUS__TODAY') ?></em>
              </span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="col-md-12">
      <div class="row">

        <div class="col-md-9">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $Lang->get('SHOPPLUS__MONTHLY_ITEMS_PURCHASES_AVERAGE') ?></h3>
              <div class="box-tools pull-right">
                <span class="badge bg-blue">~ <?= number_format($monthlyItemsPurchasesAverage, 2, ',', ' ') ?> <?= $Lang->get('SHOPPLUS__PURCHASES_MONTH') ?></span>
              </div>
            </div>
            <div class="box-body">

              <center><canvas id="purchasesChart"></canvas></center>
              <script>
                var purchasesChartData = [
                  <?php
                    $rgbColors = array('75,192,192', '192,75,75', '75,98,192', '192,75,169');
                    $i = 0;
                    foreach ($itemsPurchasesByMonthByServers as $server => $datas) {
                      echo '{';
                        echo 'label: "'.$servers[$server].'",';
                        echo 'fill: false,';
                        echo 'lineTension: 0.1,';
                        echo 'backgroundColor: "rgba('.$rgbColors[$i].',0.4)",';
                        echo 'borderColor: "rgba('.$rgbColors[$i].',1)",';
                        echo 'borderCapStyle: "butt",';
                        echo 'borderDash: [],';
                        echo 'borderDashOffset: 0.0,';
                        echo 'borderJoinStyle: "miter",';
                        echo 'pointBorderColor: "rgba('.$rgbColors[$i].',1)",';
                        echo 'pointBackgroundColor: "#fff",';
                        echo 'pointBorderWidth: 1,';
                        echo 'pointHoverRadius: 5,';
                        echo 'pointHoverBackgroundColor: "rgba('.$rgbColors[$i].',1)",';
                        echo 'pointHoverBorderColor: "rgba(220,220,220,1)",';
                        echo 'pointHoverBorderWidth: 2,';
                        echo 'pointRadius: 1,';
                        echo 'pointHitRadius: 10,';
                        echo 'data: [';
                          echo implode(', ', array_map(function ($el) {
                            return str_replace(',', '.', $el);
                          }, array_values($datas)));
                        echo '],';
                        echo 'spanGaps: false';
                      echo '},';
                      $i++;
                    }
                  ?>
                ]

                // global
                var globalData = [0, 0, 0, 0, 0, 0, 0]
                for (var i = 0; i < purchasesChartData.length; i++) {
                  var datas = Object.values(purchasesChartData[i].data)
                  $.each(datas, function (index, data) {
                    globalData[index] += data
                  })
                }

                purchasesChartData.unshift({
                  hidden: true,
                  label: "Global",
                  fill: false,
                  lineTension: 0.1,
                  backgroundColor: "rgba(90,192,75,0.4)",
                  borderColor: "rgba(90,192,75,1)",
                  borderCapStyle: 'butt',
                  borderDash: [],
                  borderDashOffset: 0.0,
                  borderJoinStyle: 'miter',
                  pointBorderColor: "rgba(90,192,75,1)",
                  pointBackgroundColor: "#fff",
                  pointBorderWidth: 1,
                  pointHoverRadius: 5,
                  pointHoverBackgroundColor: "rgba(90,192,75,1)",
                  pointHoverBorderColor: "rgba(220,220,220,1)",
                  pointHoverBorderWidth: 2,
                  pointRadius: 1,
                  pointHitRadius: 10,
                  data: globalData,
                  spanGaps: false
                })

                var myPieChart = new Chart($("#purchasesChart"), {
                  type: 'line',
                  data: {
                    labels: [
                      <?php
                      echo '"'.implode('", "', array_map(function ($el) use ($monthsName) {
                        return $monthsName[$el];
                      }, $months)).'"'
                      ?>
                    ],
                    datasets: purchasesChartData
                  },
                  options: {
                    scales: {
                      yAxes: [{
                        scaleLabel: {
                          display: true,
                          labelString: 'Achats d\'articles'
                        }
                      }]
                    }
                  }
                })
              </script>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $Lang->get('SHOPPLUS__ITEMS_PURCHASES_BY_SERVERS') ?></h3>
            </div>
            <div class="box-body">

              <center><canvas id="serversTopChart"></canvas></center>
              <script>
                var myPieChart = new Chart($("#serversTopChart"), {
                  type: 'pie',
                  data: {
                    labels: [
                      <?php
                      echo '"'.implode('", "', array_map(function ($el) use ($servers) {
                        return $servers[$el];
                      }, array_keys($itemsPurchasesByServers))).'"';
                      ?>
                    ],
                    datasets: [
                      {
                        data: [
                          <?php
                          echo implode(', ', array_map(function ($el) {
                            return str_replace(',', '.', $el);
                          }, $itemsPurchasesByServers));
                          ?>
                        ],
                        backgroundColor: [
                          "#FF6384",
                          "#36A2EB",
                          "#FFCE56",
                          "#27ae60"
                        ],
                        hoverBackgroundColor: [
                          "#FF6384",
                          "#36A2EB",
                          "#FFCE56",
                          "#27ae60"
                        ]
                      }
                    ]
                  },
                  options: {}
                })
              </script>
            </div>
          </div>
        </div>

        <div class="col-md-3 pull-right">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $Lang->get('SHOPPLUS__ITEMS_PURCHASES') ?></h3>
            </div>
            <div class="box-body">

              <center><canvas id="itemsTopChart"></canvas></center>
              <script>
                var myPieChart = new Chart($("#itemsTopChart"), {
                  type: 'pie',
                  data: {
                    labels: [
                      <?php
                      echo '"'.implode('", "', array_keys($itemsPurchases)).'"';
                      ?>
                    ],
                    datasets: [
                      {
                        data: [
                          <?php
                          echo implode(', ', array_map(function ($el) {
                            return str_replace(',', '.', $el);
                          }, $itemsPurchases));
                          ?>
                        ],
                        backgroundColor: [
                          "#FF6384",
                          "#36A2EB",
                          "#FFCE56",
                          "#27ae60"
                        ],
                        hoverBackgroundColor: [
                          "#FF6384",
                          "#36A2EB",
                          "#FFCE56",
                          "#27ae60"
                        ]
                      }
                    ]
                  },
                  options: {}
                })
              </script>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="col-md-12">
      <div class="row">

        <div class="col-md-9">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $Lang->get('SHOPPLUS__MONTHLY_PURCHASES_AVERAGE') ?></h3>
              <div class="box-tools pull-right">
                <span class="badge bg-blue">~ <?= number_format($monthlyIncomesAverage, 2, ',', ' ') ?> € / <?= $Lang->get('SHOPPLUS__MONTH') ?></span>
              </div>
            </div>
            <div class="box-body">
              <center><canvas id="creditsPurchasesChart"></canvas></center>
              <script>
                var creditsPurchasesChartData = [
                  <?php
                    $rgbColors = array('75,192,192', '192,75,75', '75,98,192', '192,75,169', '6, 154, 28', '142, 68, 173');
                    $i = 0;
                    foreach ($creditsPurchasesByMonthByModes as $type => $datas) {
                      echo '{';
                        echo 'label: "'.$typesName[$type].'",';
                        echo 'fill: false,';
                        echo 'lineTension: 0.1,';
                        echo 'backgroundColor: "rgba('.$rgbColors[$i].',0.4)",';
                        echo 'borderColor: "rgba('.$rgbColors[$i].',1)",';
                        echo 'borderCapStyle: "butt",';
                        echo 'borderDash: [],';
                        echo 'borderDashOffset: 0.0,';
                        echo 'borderJoinStyle: "miter",';
                        echo 'pointBorderColor: "rgba('.$rgbColors[$i].',1)",';
                        echo 'pointBackgroundColor: "#fff",';
                        echo 'pointBorderWidth: 1,';
                        echo 'pointHoverRadius: 5,';
                        echo 'pointHoverBackgroundColor: "rgba('.$rgbColors[$i].',1)",';
                        echo 'pointHoverBorderColor: "rgba(220,220,220,1)",';
                        echo 'pointHoverBorderWidth: 2,';
                        echo 'pointRadius: 1,';
                        echo 'pointHitRadius: 10,';
                        echo 'data: [';
                          echo implode(', ', array_map(function ($el) {
                            return str_replace(',', '.', $el);
                          }, array_values($datas)));
                        echo '],';
                        echo 'spanGaps: false';
                      echo '},';
                      $i++;
                    }
                  ?>
                ]

                // global
                var globalData = [0, 0, 0, 0, 0, 0, 0]
                for (var i = 0; i < creditsPurchasesChartData.length; i++) {
                  var datas = Object.values(creditsPurchasesChartData[i].data)
                  $.each(datas, function (index, data) {
                    globalData[index] += data
                  })
                }

                creditsPurchasesChartData.unshift({
                  hidden: true,
                  label: "Global",
                  fill: false,
                  lineTension: 0.1,
                  backgroundColor: "rgba(90,192,75,0.4)",
                  borderColor: "rgba(90,192,75,1)",
                  borderCapStyle: 'butt',
                  borderDash: [],
                  borderDashOffset: 0.0,
                  borderJoinStyle: 'miter',
                  pointBorderColor: "rgba(90,192,75,1)",
                  pointBackgroundColor: "#fff",
                  pointBorderWidth: 1,
                  pointHoverRadius: 5,
                  pointHoverBackgroundColor: "rgba(90,192,75,1)",
                  pointHoverBorderColor: "rgba(220,220,220,1)",
                  pointHoverBorderWidth: 2,
                  pointRadius: 1,
                  pointHitRadius: 10,
                  data: globalData,
                  spanGaps: false
                })

                var myPieChart = new Chart($("#creditsPurchasesChart"), {
                  type: 'line',
                  data: {
                    labels: [
                      <?php
                      echo '"'.implode('", "', array_map(function ($el) use ($monthsName) {
                        return $monthsName[$el];
                      }, $months)).'"'
                      ?>
                    ],
                    datasets: creditsPurchasesChartData
                  },
                  options: {
                    scales: {
                      yAxes: [{
                        scaleLabel: {
                          display: true,
                          labelString: 'Revenus en euros'
                        }
                      }]
                    }
                  }
                })
              </script>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $Lang->get('SHOPPLUS__MONTHLY_MODE_PURCHASES') ?></h3>
            </div>
            <div class="box-body">

              <center><canvas id="creditsModesChart"></canvas></center>
              <script>
                var myPieChart = new Chart($("#creditsModesChart"), {
                  type: 'pie',
                  data: {
                    labels: [
                      <?php
                      echo '"'.implode('", "', array_map(function ($el) use ($typesName) {
                        return $typesName[$el];
                      }, array_keys($creditsPurchasesByModes))).'"';
                      ?>
                    ],
                    datasets: [
                      {
                        data: [
                          <?php
                          echo implode(', ', array_map(function ($el) {
                            return str_replace(',', '.', $el);
                          }, $creditsPurchasesByModes));
                          ?>
                        ],
                        backgroundColor: [
                          "#FF6384",
                          "#36A2EB",
                          "#FFCE56",
                          "#27ae60",
                          "#4bddfe",
                          "#8e44ad"
                        ],
                        hoverBackgroundColor: [
                          "#FF6384",
                          "#36A2EB",
                          "#FFCE56",
                          "#27ae60",
                          "#4bddfe",
                          "#8e44ad"
                        ]
                      }
                    ]
                  },
                  options: {}
                })
              </script>
            </div>
          </div>
        </div>

      </div>
    </div>


    <div class="col-md-12">
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs pull-right">
          <?php
          $i = 0;
          foreach (array_keys($itemsPurchasesByItemByMonthByServers) as $serverId) {
            $i++;
            echo '<li'.($i === 1 ? ' class="active"' : '').'><a href="#tab_server_'.$serverId.'" data-toggle="tab">'.$servers[$serverId].'</a></li>';
          }
          ?>
          <li class="pull-left header"><?= $Lang->get('SHOPPLUS__ITEMS_PURCHASES_BY_ITEM_BY_MONTH_BY_SERVERS') ?></li>
        </ul>
        <div class="tab-content">
          <?php
          $i = 0;
          $rgbColors = array(
            '255, 99, 132',
            '54, 162, 235',
            '255, 206, 86',
            '75, 192, 192',
            '153, 102, 255',
            '255, 159, 64'
          );
          foreach ($itemsPurchasesByItemByMonthByServers as $serverId => $datas) {
            $i++;
            echo '<div class="tab-pane'.($i == 1 ? ' active' : '').'" id="tab_server_'.$serverId.'">';
              echo '<center><canvas id="itemsBarChart'.$serverId.'"></canvas></center>';
          ?>
              <script>
                var myPieChart = new Chart($("#itemsBarChart<?= $serverId ?>"), {
                  type: 'bar',
                  data: {
                    labels: [
                      <?php
                      echo '"'.implode('", "', array_keys(end($datas))).'"';
                      ?>
                    ],
                    datasets: [
                      <?php
                      foreach ($datas as $month => $values) {
                        // add colors
                        if (count($rgbColors) < count($values)) {
                          $count = count($rgbColors);
                          $how = round(count($values) / $count);
                          while ($count < $how) {
                            $rgbColors = array_merge($rgbColors, $rgbColors);
                            $count += count($rgbColors);
                          }
                        }

                        // display
                        echo '{';
                          echo 'label: "'.$monthsName[$month].'",';
                          echo 'backgroundColor: [';
                            echo "'".implode('\', \'', array_map(function ($el) {
                              return "rgba({$el}, 0.5)";
                            }, array_values($rgbColors)))."'";
                          echo '],';
                          echo 'borderColor: [';
                            echo "'".implode('\', \'', array_map(function ($el) {
                              return "rgba({$el}, 1)";
                            }, array_values($rgbColors)))."'";
                          echo '],';
                          echo 'borderWidth: 1,';
                          echo 'data: [';
                            echo implode(', ', array_map(function ($el) {
                              return str_replace(',', '.', $el);
                            }, array_values($values)));
                          echo '],';
                        echo '},';
                      }
                      ?>
                    ]
                  },
                  options: {}
                })
              </script>
          <?php
            echo '</div>';
          }
          ?>
        </div>
        <!-- /.tab-content -->
      </div>
    </div>

  </div>
</section>
