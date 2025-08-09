<?php
session_start();
include("../Database/db_connection.php");

$editMode = false;
$editTravel = null;

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $query = "SELECT * FROM travel_company WHERE travel_company_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $editTravel = mysqli_fetch_assoc($result);
        $editMode = true;
    }
    mysqli_stmt_close($stmt);
}
?>

<?php include("components/head.php"); ?>

<body>
    <div class="wrapper">
        <!-- Hamburger Toggle Button -->
        <button id="sidebarToggle" class="navbar-toggler d-lg-none" type="button"
            style="position: fixed; top: 5px; left: 15px; z-index: 1050; background: white; border: none; padding: 8px 10px; border-radius: 4px;">
            <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>

        <?php include("./components/sidebarclk.php"); ?>
        <div class="main-panel">
            <div class="content">
                <div class="row">

                    <!-- Form Column -->
                    <div class="col-lg-5 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                if (isset($_SESSION['message'])) {
                                    $msgType = $_SESSION['msg_type'] ?? 'info';
                                    echo "<div class='alert alert-{$msgType} text-center'>{$_SESSION['message']}</div>";
                                    unset($_SESSION['message']);
                                    unset($_SESSION['msg_type']);
                                }
                                ?>
                                <form class="form-control" action="function_clk.php" method="POST">
                                    <h2 class="text-center"><?= $editMode ? 'Edit Travel Company' : 'Register Travel Company' ?></h2>

                                    <input type="hidden" name="travel_company_id" value="<?= $editTravel['travel_company_id'] ?? '' ?>">

                                    <div class="form-group">
                                        <label for="travel_name">Company Name:</label>
                                        <input type="text" class="form-control" id="travel_name" name="travel_name"
                                            value="<?= htmlspecialchars($editTravel['travel_name'] ?? '') ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="travel_contact_person">Contact Person:</label>
                                        <input type="text" class="form-control" id="travel_contact_person" name="travel_contact_person"
                                            value="<?= htmlspecialchars($editTravel['travel_contact_person'] ?? '') ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="travel_contact_email">Email:</label>
                                        <input type="email" class="form-control" id="travel_contact_email" name="travel_contact_email"
                                            value="<?= htmlspecialchars($editTravel['travel_contact_email'] ?? '') ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="travel_phone">Phone:</label>
                                        <input type="text" class="form-control" id="travel_phone" name="travel_phone"
                                            value="<?= htmlspecialchars($editTravel['travel_phone'] ?? '') ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="travel_address">Address:</label>
                                        <input type="text" class="form-control" id="travel_address" name="travel_address"
                                            value="<?= htmlspecialchars($editTravel['travel_address'] ?? '') ?>" required>
                                    </div>


                                    <div class="form-group">
                                        <label for="travel_credit_cardnum">Credit Card Number:</label>
                                        <input type="password" class="form-control" id="travel_credit_cardnum" placeholder="Please enter valid credit card number..." name="travel_credit_cardnum"
                                            value="<?= htmlspecialchars($editTravel['travel_credit_cardnum'] ?? '') ?>" pattern="\d{13,19}">
                                        <?php if ($editMode): ?>
                                            <small class="form-text text-muted">Leave blank to keep current credit card.</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="travel_password">Password:</label>
                                        <input type="password" class="form-control" id="travel_password" name="travel_password" minlength="4" <?= $editMode ? '' : 'required' ?>>
                                        <?php if ($editMode): ?>
                                            <small class="form-text text-muted">Leave blank to keep current password.</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group pt-3 text-center">
                                        <input type="reset" class="btn btn-success btn-sm px-5" value="Reset">
                                        <input type="submit"
                                            class="btn <?= $editMode ? 'btn-warning' : 'btn-primary' ?> btn-sm px-5"
                                            name="<?= $editMode ? 'btn_update_travel' : 'btn_add_travel' ?>"
                                            value="<?= $editMode ? 'Update Travel Company' : 'Add Travel Company' ?>">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Table Column -->
                    <div class="col-lg-7 col-md-6 col-sm-12">
                        <h2>Travel Companies</h2>
                        <?php
                        if (isset($_SESSION['message_table'])) {
                            $msgTypeTable = $_SESSION['msg_type_table'] ?? 'info';
                            echo "<div class='alert alert-{$msgTypeTable} text-center'>{$_SESSION['message_table']}</div>";
                            unset($_SESSION['message_table']);
                            unset($_SESSION['msg_type_table']);
                        }

                        $query = "SELECT * FROM travel_company";
                        $result = mysqli_query($con, $query);
                        ?>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Contact Person</th>
                                    <th class="text-center">Phone</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Credit Card</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($row['travel_company_id']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['travel_name']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['travel_contact_person']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['travel_phone']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['travel_contact_email']) ?></td>
                                            <td class="text-center"><?= !empty($row['travel_credit_cardnum']) ? '✅' : '❌' ?></td>
                                            <td class="text-center">
                                                <a href="?edit_id=<?= $row['travel_company_id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                                                <form action="function_clk.php" method="POST" style="display:inline;">
                                                    <button type="submit" class="btn btn-sm btn-danger" name="btn_delete_travel" value="<?= $row['travel_company_id'] ?>">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">No travel companies found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('sidebar-show');
        });
    </script>
</body>
</html>
