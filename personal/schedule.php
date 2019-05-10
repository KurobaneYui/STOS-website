<?php
session_start();
require("../frame/Person_Class_frame.php");

if ( isset( $_SESSION[ 'islogin' ] )and isset( $_SESSION[ 'username' ] ) ) { // 如果已经登陆
    $connection = new STOS_MySQL(); // 建立数据库连接
    $person = new person_all_info( $_SESSION[ "username" ] ); // 获取个人信息
}
else { // 没有登陆但是cookie中存有登陆信息
    //检查cookie
    if ( isset( $_COOKIE[ 'username' ] ) ) {
        $_SESSION[ 'username' ] = $_COKIE[ 'username' ];
        $_SESSION[ 'islogin' ] = 1;

        $connection = new STOS_MySQL();
        $person = new person_all_info( $_SESSION[ "username" ] );
    }
    else
        header( 'refresh:0; url=../log/login.php' ); // 返回登陆页面
}
?>

<!doctype html>
<html lang="zh, en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Personal Index Page">
    <meta name="author" content="Luo Yinsong">
    <link rel="icon" href="../source/icon/STOS.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>个人中心</title>

    <!-- 新 Bootstrap4 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.1.0/css/bootstrap.min.css">

    <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
    <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>

    <!-- popper.min.js 用于弹窗、提示、下拉菜单 -->
    <script src="https://cdn.staticfile.org/popper.js/1.12.5/umd/popper.min.js"></script>

    <!-- 最新的 Bootstrap4 核心 JavaScript 文件 -->
    <script src="../bootstrap-4.0.0/js/bootstrap.min.js"></script>

    <!-- Custom styles for this template -->
    <link href="../bootstrap-4.0.0/css/extra/dashboard.css" rel="stylesheet">
</head>

<body>
    <?php require('../frame/personal_head_frame.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <?php require('../frame/personal_side_frame.php'); ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">暂未开放</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <button class="btn btn-sm btn-outline-secondary">Share</button>
                            <button class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <span data-feather="calendar"></span>This week
                        </button>
                    
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        var s = document.createElement("span");
        var p = document.getElementById('P_schedule')
        p.appendChild(s)
        p.className = "nav-link active"
        s.innerHTML = "(current)"
        s.className = "sr-only"
    </script>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>
    <script>
        window.jQuery || document.write( '<script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"><\/script>' )
    </script>

    <!-- Icons -->
    <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
    <script>
        feather.replace()
    </script>

    <!-- Graphs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
    <script>
        var ctx = document.getElementById( "myChart" );
        var myChart = new Chart( ctx, {
            type: 'line',
            data: {
                labels: [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
                datasets: [ {
                    data: [ 15339, 21345, 18483, 24003, 23489, 24092, 12034 ],
                    lineTension: 0,
                    backgroundColor: 'transparent',
                    borderColor: '#007bff',
                    borderWidth: 4,
                    pointBackgroundColor: '#007bff'
                } ]
            },
            options: {
                scales: {
                    yAxes: [ {
                        ticks: {
                            beginAtZero: false
                        }
                    } ]
                },
                legend: {
                    display: false,
                }
            }
        } );
    </script>
</body>
</html>
<?php $conn.close(); ?>