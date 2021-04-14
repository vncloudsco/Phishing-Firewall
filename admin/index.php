<?php
include "../inc/config.php";
if(!isset($_SESSION['loggedIn'])) {
    header("Location: login");
    exit();
}

$logs = $pdo->query("SELECT COUNT(id) FROM logs")->fetch()[0];
$blocked = $pdo->query("SELECT COUNT(id) FROM logs WHERE status = 1")->fetch()[0];
$logs_today = $pdo->query("SELECT COUNT(id) FROM logs WHERE DATE(date) = DATE(NOW())")->fetch()[0];
$domains = $pdo->query("SELECT COUNT(id) FROM domains")->fetch()[0];
$rate = intval(100-($blocked / $logs)*100);
include "files/sidebar.php";
?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= $logs ?></h3>

                <p>Total clicks</p>
              </div>
              <div class="icon">
                  <i class="fas fa-chart-bar"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $rate ?><sup style="font-size: 20px">%</sup></h3>

                <p>Redirect rate</p>
              </div>
              <div class="icon">
                  <i class="fas fa-check-square"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= $domains ?></h3>
                <p>Total domains</p>
              </div>
              <div class="icon">
                  <i class="fas fa-server"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?= $blocked ?></h3>

                <p>Blocked visitors</p>
              </div>
              <div class="icon">
                  <i class="fas fa-times-circle"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Visitors by path</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Visitors in the last week</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
<?php
include "files/footer.php";
?>
<script>
    $(function () {
        /* ChartJS
         * -------
         * Here we will create a few charts using ChartJS
         */

        //--------------
        //- AREA CHART -
        //--------------

        // Get context with jQuery - using jQuery's .get() method.
        /*var areaChartCanvas = $('#areaChart').get(0).getContext('2d')
        */
        <?php
            $country = $pdo->query("SELECT COUNT(id), path FROM logs GROUP BY path");
            $country_arr = array();
            $data_arr = array();
            foreach ($country->fetchAll() as $row) {
                $country_arr[] = $row["path"];
                $data_arr[] = intval($row[0]);
            }
        ?>
        var areaChartData = {
            labels  : <?= json_encode($country_arr) ?>,
            datasets: [
                {
                    label               : 'Visitors',
                    backgroundColor     : 'rgba(60,141,188,0.9)',
                    borderColor         : 'rgba(60,141,188,0.8)',
                    pointRadius          : true,
                    pointColor          : '#3b8bba',
                    pointStrokeColor    : 'rgba(60,141,188,1)',
                    pointHighlightFill  : '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data                : <?= json_encode($data_arr) ?>
                }
            ]
        }


        <?php
        //First calculate last 7 days dates and add them to an array
        $today = date("d.m.Y");
        $date_arr = array();

        for ($i = 7; $i > 0; $i--) {
            $date_arr[] = date("d.m.Y", strtotime("-".$i." days"));
        }

        $date_arr[] = $today;

        //Then select logs from last week;
        $logs_arr = array();
        foreach ($date_arr as $date) {
            $date = date("Y-m-d", strtotime($date));
            $get_logs = $pdo->prepare("SELECT COUNT(id) FROM logs WHERE DATE(date) = DATE(:d)");
            $get_logs->bindParam("d", $date);
            $get_logs->execute();
            $logs_arr[] = $get_logs->fetch()[0];
        }
        ?>

        var lineChartData = {
            labels  : <?= json_encode($date_arr) ?>,
            datasets: [
                {
                    label               : 'Logs',
                    backgroundColor     : 'rgba(60,141,188,0.9)',
                    borderColor         : 'rgba(60,141,188,0.8)',
                    pointRadius          : true,
                    pointColor          : '#3b8bba',
                    pointStrokeColor    : 'rgba(60,141,188,1)',
                    pointHighlightFill  : '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data                : <?= json_encode($logs_arr) ?>
                }
            ]
        }



        var areaChartOptions = {
            maintainAspectRatio : false,
            responsive : true,
            legend: {
                display: false
            },
            scales: {
                xAxes: [{
                    gridLines : {
                        display : false,
                    }
                }],
                yAxes: [{
                    gridLines : {
                        display : false,
                    }
                }]
            }
        }

        /*

        // This will get the first returned node in the jQuery collection.
        var areaChart       = new Chart(areaChartCanvas, {
            type: 'line',
            data: areaChartData,
            options: areaChartOptions
        })*/

        //-------------
        //- LINE CHART -
        //--------------
        var lineChartCanvas = $('#lineChart').get(0).getContext('2d')
        var lineChartOptions = jQuery.extend(true, {}, areaChartOptions)
        var lineChartData = jQuery.extend(true, {}, lineChartData)
        lineChartData.datasets[0].fill = false;
        lineChartOptions.datasetFill = false

        var lineChart = new Chart(lineChartCanvas, {
            type: 'line',
            data: lineChartData,
            options: lineChartOptions
        })

        /*

        //-------------
        //- DONUT CHART -
        //-------------
        // Get context with jQuery - using jQuery's .get() method.
        var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
        var donutData        = {
            labels: [
                'Chrome',
                'IE',
                'FireFox',
                'Safari',
                'Opera',
                'Navigator',
            ],
            datasets: [
                {
                    data: [700,500,400,600,300,100],
                    backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                }
            ]
        }
        var donutOptions     = {
            maintainAspectRatio : false,
            responsive : true,
        }
        //Create pie or douhnut chart
        // You can switch between pie and douhnut using the method below.
        var donutChart = new Chart(donutChartCanvas, {
            type: 'doughnut',
            data: donutData,
            options: donutOptions
        })

        //-------------
        //- PIE CHART -
        //-------------
        // Get context with jQuery - using jQuery's .get() method.
        var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
        var pieData        = donutData;
        var pieOptions     = {
            maintainAspectRatio : false,
            responsive : true,
        }
        //Create pie or douhnut chart
        // You can switch between pie and douhnut using the method below.
        var pieChart = new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        })*/

        //-------------
        //- BAR CHART -
        //-------------
        var barChartCanvas = $('#barChart').get(0).getContext('2d')
        var barChartData = jQuery.extend(true, {}, areaChartData)
        var temp0 = areaChartData.datasets[0]
        barChartData.datasets[0] = temp0

        var barChartOptions = {
            legend: {
                display: false
            },
            tooltips: {enabled: false},
            hover: {mode: null},
            responsive              : true,
            maintainAspectRatio     : false,
            datasetFill             : false,
            scales: {
                xAxes: [{
                    stacked: true,
                    gridLines : {
                        display : false,
                    }
                }],
                yAxes: [{
                    stacked: true,
                    gridLines : {
                        display : false,
                    }
                }]
            }
        }

        var barChart = new Chart(barChartCanvas, {
            type: 'bar',
            data: barChartData,
            options: barChartOptions
        })

        //---------------------
        //- STACKED BAR CHART -
        //---------------------
        /*var stackedBarChartCanvas = $('#stackedBarChart').get(0).getContext('2d')
        var stackedBarChartData = jQuery.extend(true, {}, barChartData)

        var stackedBarChartOptions = {
            responsive              : true,
            maintainAspectRatio     : false,
            scales: {
                xAxes: [{
                    stacked: true,
                }],
                yAxes: [{
                    stacked: true
                }]
            }
        }

        var stackedBarChart = new Chart(stackedBarChartCanvas, {
            type: 'bar',
            data: stackedBarChartData,
            options: stackedBarChartOptions
        })*/
    })
</script>