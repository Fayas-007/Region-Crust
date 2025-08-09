<?php
session_start();
?>
 <?php include("../Database/db_connection.php");?> 
<!doctype html>
<html lang="en">

<?php
$editMode = false;
$editCustomer = null;

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $query = "SELECT * FROM customer WHERE idcus = ?";
    $stmt = mysqli_prepare($con, $query);

     mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $editCustomer = mysqli_fetch_assoc($result);
        $editMode = true;
    }
    mysqli_stmt_close($stmt);
 
}
?>

<?php include("components/head.php"); ?>

<body class="">
    <div class="wrapper">
        <!-- Hamburger Toggle Button -->
        <button id="sidebarToggle" class="navbar-toggler d-lg-none" type="button"
            style="position: fixed; top: 5px; left: 15px; z-index: 1050; background: white; border: none; padding: 8px 10px; border-radius: 4px;">
            <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>

        <?php include("./components/sidebarclk.php"); ?>
        <?php include("../Database/db_connection.php"); ?>

        <div class="main-panel">
            <div class="content">
                <div class="row">

                    <!-- Form Column -->
                    <div class="col-lg-5 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                 <?php
                                    if (isset($_SESSION['message'])) {
                                            $msgType = $_SESSION['msg_type'] ?? 'info'; // fallback to info
                                            echo "<div class='alert alert-{$msgType} text-center'>" . $_SESSION['message'] . "</div>";
                                            unset($_SESSION['message']);
                                            unset($_SESSION['msg_type']);
                                        }
                                     ?>
                                        <form class="form-control" action="function_clk.php" method="POST">
                                            <h2 class="text-center"><?= $editMode ? 'Edit Customer' : 'Register Customer' ?></h2>

                                            <input type="hidden" name="customer_id" value="<?= $editCustomer['idcus'] ?? '' ?>">

                                            <div class="form-group">
                                                <label for="cus_name">Customer Name:</label>
                                                <input type="text" class="form-control" id="cus_name" name="cus_name"
                                                    value="<?= htmlspecialchars($editCustomer['cus_name'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="address">Address:</label>
                                                <input type="text" class="form-control" id="address" name="address"
                                                    value="<?= htmlspecialchars($editCustomer['address'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="cus_contact">Contact Number:</label>
                                                <input type="text" class="form-control" id="cus_contact" name="cus_contact"
                                                    value="<?= htmlspecialchars($editCustomer['cus_contact'] ?? '') ?>" required>
                                            </div>


                                            <div class="form-group">
                                                <label for="cus_email">Email:</label>
                                                <input type="email" class="form-control" id="cus_email" name="cus_email"
                                                    value="<?= htmlspecialchars($editCustomer['cus_email'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="cus_password">Password:</label>
                                                <input type="password" class="form-control" minlength="4" id="cus_password" name="cus_password" <?= $editMode ? '' : 'required' ?>>
                                                <?php if ($editMode): ?>
                                                    <small class="form-text text-muted">Leave blank to keep current password.</small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="form-group">
                                                <label for="cus_email">Credit Card Number:</label>
                                                <input type="password" m class="form-control" id="credit_cardnum" name="credit_cardnum" placeholder="enter valid credit card number.."
                                                    value="<?= htmlspecialchars($editCustomer['credit_cardnum'] ?? '') ?>" pattern="^\d{13,19}$" minlength="10"  >
                                                    <?php if ($editMode): ?>
                                                    <small class="form-text text-muted">Leave blank to keep current password.</small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="form-group pt-3 text-center">
                                                <input type="reset" class="btn btn-success btn-sm px-5" onclick="clearForm()" value="Reset">
                                                <input type="submit"
                                                    class="btn <?= $editMode ? 'btn-warning' : 'btn-primary' ?> btn-sm px-5"
                                                    name="<?= $editMode ? 'btn_update_customer' : 'btn_add_customer' ?>"
                                                    value="<?= $editMode ? 'Update customer' : 'Add customer' ?>">
                                            </div>
                                        </form>
                            </div>
                        </div>
                    </div>

                    <!-- Table Column -->
                    <div class="col-lg-7 col-md-6 col-sm-12">
                                <h2>Customers</h2>

                                <?php
                                $query = "SELECT * FROM customer";
                                $result = mysqli_query($con, $query);
                                ?>

                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Address</th>
                                            <th class="text-center">Contact</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Credit Card</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td class="text-center"><?= htmlspecialchars($row['idcus']) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($row['cus_name']) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($row['address']) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($row['cus_contact']) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($row['cus_email']) ?></td>
                                                    <td class="text-center">
                                                        <?= !empty($row['credit_cardnum']) ? '✅' : '❌' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="?edit_id=<?= htmlspecialchars($row['idcus']) ?>"
                                                            class="btn btn-sm btn-warning mb-1">Edit</a>
                                                        <form action="function_clk.php" method="POST" style="display:inline;">
                                                            <button type="submit"
                                                                class="btn btn-sm btn-danger"
                                                                name="btn_delete_customer"
                                                                value="<?= htmlspecialchars($row['idcus']) ?>">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No customers found</td>
                                            </tr>
                                        <?php endif; ?>
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
