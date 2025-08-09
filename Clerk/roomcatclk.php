<?php
    session_start();
    include("../Database/db_connection.php");
?> 

<?php
$editMode = false;
$editRoom = null;

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $query = "SELECT * FROM rooms WHERE idroom = ?";
    $stmt = mysqli_prepare($con, $query);

    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $editRoom = mysqli_fetch_assoc($result);
        $editMode = true;
    }
    mysqli_stmt_close($stmt);
}
?>
<!doctype html>
<html lang="en">
<?php include("./components/head.php");?>

<body>
    <div class="wrapper">
        <!-- Sidebar and other components -->
        <button id="sidebarToggle" class="navbar-toggler d-lg-none" type="button"
            style="position: fixed; top: 5px; left: 15px; z-index: 1050; background: white; border: none; padding: 8px 10px; border-radius: 4px;">
            <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>

        <?php include("./components/sidebarclk.php"); ?>
        <div class="main-panel">
            <div class="content">
                <div class="row">

                    <!-- Form Panel -->
                    <div class="col-lg-5 col-md-6 col-sm-12">
                        <div class="Card">
                            <div class="card-body">
                                                              <?php
                                if (isset($_SESSION['message'])) {
                                    echo "<div class='alert alert-info text-center'>" . $_SESSION['message'] . "</div>";
                                    unset($_SESSION['message']);
                                }
                                ?>
                                <form class="form-control" action="function_clk.php" method="POST" enctype="multipart/form-data">
                                    <h2 class="text-center"><?= $editMode ? 'Update Room Assignment' : 'Assign Room and Category' ?></h2>

                                    <input type="hidden" name="room_id" value="<?= $editRoom['idroom'] ?? '' ?>">

                                    <div class="form-group">
                                        <label for="room_no">Room Number</label>
                                        <input type="text" class="form-control" id="room_no" name="room_no" value="<?= htmlspecialchars($editRoom['room_no'] ?? '') ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="idroom_cat">Room Category</label>
                                        <select class="form-control" id="idroom_cat" name="idroom_cat" required>
                                            <option value="">-- Select Category --</option>
                                            <?php
                                            $sql = "SELECT idroom_cat, room_cat FROM room_cat";
                                            $result = $con->query($sql);
                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $selected = ($row['idroom_cat'] == $editRoom['idroom_cat']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($row['idroom_cat']) . "' $selected>" . htmlspecialchars($row['room_cat']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group pt-3 text-center">
                                        <input type="reset" class="btn btn-success btn-sm px-5" value="Reset">
                                        <input type="submit" class="btn <?= $editMode ? 'btn-warning' : 'btn-primary' ?> btn-sm px-5" name="<?= $editMode ? 'btn_update_room' : 'btn_assign_room' ?>" value="<?= $editMode ? 'Update Room' : 'Assign Room' ?>">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Table Panel -->
                    <div class="col-lg-7 col-md-6 col-sm-12">
                        <h2>View Room Assignments</h2>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Room Number</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM rooms";
                                $run = mysqli_query($con, $sql);
                                if (mysqli_num_rows($run) > 0) {
                                    while ($row = mysqli_fetch_assoc($run)) {
                                ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($row['idroom']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['room_no']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['idroom_cat']) ?></td>
                                            <td class="text-center">
                                                <a href="?edit_id=<?= htmlspecialchars($row['idroom']) ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                                                
                                                <!-- Delete Button -->
                                                <form action="function_clk.php" method="POST" style="display:inline;">
                                                    <button type="submit" class="btn btn-sm btn-danger" name="btn_delete_room" value="<?= htmlspecialchars($row['idroom']) ?>">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No rooms found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>       
        </div>
    </div>

      <!-- Core JS Files -->
    <script src="./assets/js/core/jquery.min.js"></script>
    <script src="./assets/js/core/popper.min.js"></script>
    <script src="./assets/js/core/bootstrap.min.js"></script>
    <script src="./assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
    <!-- Google Maps Plugin -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
    <!-- Chart JS -->
    <script src="./assets/js/plugins/chartjs.min.js"></script>
    <!-- Notifications Plugin -->
    <script src="./assets/js/plugins/bootstrap-notify.js"></script>
    <!-- Control Center for Now Ui Dashboard -->
    <script src="./assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
    <script src="../Manager/assets/js/custom.js"></script>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('sidebar-show');
        });
    </script>
</body>
</html>
