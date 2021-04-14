<?php
include "../inc/config.php";
if(!isset($_SESSION['loggedIn'])) {
    header("Location: login");
    exit();
}
if(isset($_GET['delete'])) {
    $export = $pdo->prepare("DELETE FROM links WHERE id = :id");
    $export->bindParam("id", $_GET["delete"]);
    $export->execute();


    $del = $pdo->prepare("DELETE FROM domains WHERE link_sid = :id");
    $del->bindParam("id", $_GET["delete"]);
    $del->execute();
}
if (isset($_POST["name"])) {
    $name = htmlspecialchars($_POST["name"]);
    if(!empty($_POST["id"])) {
        $id = htmlspecialchars($_POST["id"]);
    } else {
        $id = substr(md5(microtime()), 0, 6);
    }
    $insert = $pdo->prepare("INSERT INTO links (uniq_id, name) VALUES (:id, :name)");
    $insert->bindParam("id", $id);
    $insert->bindParam("name", $name);
    $insert->execute();
}
include "files/sidebar.php";

?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Links</h1>
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
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Create link</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" name="name" class="form-control" id="name" placeholder="Name for the link type" required>
                            </div>
                            <div class="form-group">
                                <label for="id">ID (optional)</label>
                                <input type="text" maxlength="6" name="id" class="form-control" id="id" placeholder="The ID of the link. It must be 6 digits long (optional)">
                            </div>
                            <button class="btn btn-primary">Create</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Links</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped table-responsive-sm">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>URL</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $logs = $pdo->query("SELECT * FROM links");
                            foreach ($logs->fetchAll() as $row) {
                                echo <<<TD
 <tr>
                                <td>{$row['uniq_id']}</td>
                                <td>{$row['name']}</td>
                                <td><a href="http://{$_SERVER['HTTP_HOST']}/{$row["uniq_id"]}">http://{$_SERVER['HTTP_HOST']}/{$row["uniq_id"]}</a></td>
                                <td><a href="?delete={$row['id']}" class="badge badge-danger">Delete</a></td>
                            </tr>
TD;

                            }
                            ?>
                            </tbody>

                        </table>
                    </div>
                    <!-- /.card-body -->
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
