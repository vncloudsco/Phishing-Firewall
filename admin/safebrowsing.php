<?php
include "../inc/config.php";
if(!isset($_SESSION['loggedIn'])) {
    header("Location: login");
    exit();
}
if (isset($_POST["api"])) {
    $api = htmlspecialchars($_POST["api"]);
    $insert = $pdo->prepare("UPDATE settings SET safebrowsing_key = :api");
    $insert->bindParam("api", $api);
    $insert->execute();
}


$get_settings = $pdo->query("SELECT * FROM settings");
$settings = $get_settings->fetch();

include "files/sidebar.php";

?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Settings</h1>
            </div>
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
                        <h3 class="card-title">Safebrowsing</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="api">Safebrowsing API Key (<a href="https://console.developers.google.com/">Generate it here</a>)</label>
                                <input type="text" name="api" class="form-control" id="api" placeholder="Google Safebrowsing API Key" value="<?= $settings["safebrowsing_key"] ?>" required>
                            </div>

                            <button class="btn btn-primary">Update Settings</button>
                        </form>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
    </div>
</section>
<?php
include "files/footer.php";
?>
