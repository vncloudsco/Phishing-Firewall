<?php
include "../inc/config.php";
if(!isset($_SESSION['loggedIn'])) {
    header("Location: login");
    exit();
}
if(isset($_GET['delete'])) {
    $export = $pdo->prepare("DELETE FROM domains WHERE id = :id");
    $export->bindParam("id", $_GET["delete"]);
    $export->execute();
}
if (isset($_POST["url"]) && isset($_POST["link"])) {
    $url = htmlspecialchars($_POST["url"]);
    $link_id = htmlspecialchars($_POST["link"]);

    $without = str_replace("http://", "", $url);
    $without = str_replace("https://", "", $without);
    $split = explode("/", $without);
    $domain = $split[0];

    $insert = $pdo->prepare("INSERT INTO domains (link, domain, link_id ,status) VALUES (:link, :domain, :link_id, 0)");
    $insert->bindParam("link", $url);
    $insert->bindParam("domain", $domain);
    $insert->bindParam("link_id", $link_id);
    $insert->execute();
}
include "files/sidebar.php";

?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Domains</h1>
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
                        <h3 class="card-title">Add Domain</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label>Link</label>
                                <select class="form-control custom-select" name="link" required>
                                    <option selected>Select Link...</option>
                                    <?php
                                    $getLinks = $pdo->query("SELECT name, id FROM links");
                                    foreach ($getLinks->fetchAll() as $row) {
                                        echo '<option value="'.$row["id"].'">'.$row["name"].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="url">Url</label>
                                <input type="url" name="url" class="form-control" id="url" placeholder="Phishing URL (eg https://example.com/phishing)" required>
                            </div>
                            <button class="btn btn-primary">Create</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Domains</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped table-responsive-sm">
                            <thead>
                            <tr>
                                <th>URL</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $logs = $pdo->query("SELECT domains.id, domains.link, domains.status, links.name  FROM domains JOIN links ON domains.link_id = links.id");
                            foreach ($logs->fetchAll() as $row) {
                                if($row["status"] == 0) {
                                    $status = '<span class="badge badge-success">Active</span>';
                                } else {
                                    $status = '<span class="badge badge-danger">Flagged</span>';
                                }
                                echo <<<TD
 <tr>
                                <td><a href="{$row['link']}" target="_blank">{$row['link']}</a></td>
                                <td>{$row['name']}</td>
                                <td>{$status}</td>
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
