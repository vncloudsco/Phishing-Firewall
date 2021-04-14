<?php
include "../inc/config.php";
if(!isset($_SESSION['loggedIn'])) {
    header("Location: login");
    exit();
}
if(isset($_POST['smart'])) {
    $smart = $pdo->prepare("UPDATE settings SET smartban = :ban");
    $smart->bindParam("ban", htmlspecialchars($_POST["smart"]));
    $smart->execute();
}

$get_settings = $pdo->query("SELECT * FROM settings");
$settings = $get_settings->fetch();

if(isset($_GET["page"])) {
    $page = $_GET["page"];
} else {
    $page = 1;
}

$gettotal = $pdo->query("SELECT COUNT(id) FROM logs");
$total = $gettotal->fetch()[0];

include "files/sidebar.php";
?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Firewall</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Firewall Settings</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="smart">Smart Ban (<a href="#whatsthat" data-toggle="modal" data-target="#whatsthat">What is that?</a>)</label>
                                <select class="custom-select" name="smart" id="smart">
                                    <option value="0" <?= ($settings["smartban"] == 0) ? 'selected=""' : '' ?>>Disabled</option>
                                    <option value="1" <?= ($settings["smartban"] == 1) ? 'selected=""' : '' ?>>Enabled</option>
                                </select>
                            </div>

                            <button class="btn btn-primary">Update Settings</button>
                        </form>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Firewall logs</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-responsive-sm table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>User-Agent</th>
                                <th>IP</th>
                                <th>Date</th>
                                <th>Path</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $start = ( $page - 1 ) * 25;
                            $logs = $pdo->prepare("SELECT * FROM logs ORDER BY date DESC LIMIT :s ,25");
                            $logs->bindParam("s", $start, PDO::PARAM_INT);
                            $logs->execute();
                            foreach ($logs->fetchAll() as $row) {
                                if($row["status"] == 0) {
                                    $status = '<span class="badge badge-success">Accepted</span>';
                                } else {
                                    $status = '<span class="badge badge-danger">Blocked</span>';
                                }
                                echo <<<TD
 <tr>
                                <td>{$row['user_agent']}</td>
                                <td>{$row['ip']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['path']}</td>
                                <td>{$status}</td>
                            </tr>
TD;

                            }
                            ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>User-Agent</th>
                                <th>IP</th>
                                <th>Date</th>
                                <th>Link</th>
                                <th>Status</th>
                            </tr>
                            </tfoot>
                        </table><br>
                        <?php
                        $last       = ceil( $total / 25 );

                        $start      = ( ( $page - 7 ) > 0 ) ? $page - 7 : 1;
                        $end        = ( ( $page + 7 ) < $last ) ? $page + 7 : $last;

                        $html       = '<ul class="pagination pagination-sm">';

                        $class      = ( $page == 1 ) ? "page-item disabled" : "page-item";
                        $html       .= '<li class="' . $class . '"><a class="page-link" href="?page=' . ( $page- 1 ) . '">&laquo;</a></li>';

                        if ( $start > 1 ) {
                            $html   .= '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                            $html   .= '<li class="page-item disabled"><span>...</span></li>';
                        }

                        for ( $i = $start ; $i <= $end; $i++ ) {
                            $class  = ( $page== $i ) ? "active" : "";
                            $html   .= '<li class="' . $class . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                        }

                        if ( $end < $last ) {
                            $html   .= '<li class="page-item disabled"><span>...</span></li>';
                            $html   .= '<li class="page-item"><a class="page-link" href="?page=' . $last . '">' . $last . '</a></li>';
                        }

                        $class      = ( $page >= $last ) ? "page-item disabled" : "page-item";
                        $html       .= '<li class="' . $class . '"><a class="page-link" href="?page=' . ( $page + 1 ) . '">&raquo;</a></li>';

                        $html       .= '</ul>';

                        echo $html;
                        ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
    </div>
</section>
<div class="modal fade" id="whatsthat" tabindex="-1" aria-labelledby="whatsthat" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">What is SmartBan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                SmartBan is a special technique which will allow you to expand your bot protection. It will ban add all unauthorized requests to the blacklist to prevent them from accessing the page again
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
<?php
include "files/footer.php";
?>
