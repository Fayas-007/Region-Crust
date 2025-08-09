<?php
session_start();
?>
 <?php include("../Database/db_connection.php");?> 
  <?php
 


$editMode = false;
$editTravel = null;

 

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $query = "SELECT * FROM travelcompany WHERE travel_company_id = ?";
    $stmt = mysqli_prepare($con, $query); // Use $conn if that's your DB variable
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
<?php include("./components/topnavbarclk.php");?> 

<!doctype html>
<html lang="en">

<?php include("./components/head.php");?> 

 

<body class="">
    <div class="wrapper ">
        <!-- Hamburger Toggle Button -->
        <button id="sidebarToggle" class="navbar-toggler d-lg-none" type="button" style="position: fixed; top: 5px; left: 15px; z-index: 1050; background: white; border: none; padding: 8px 10px; border-radius: 4px;">
        <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>
        <?php include("./components/sidebarclk.php");?> 
       
        <div class="main-panel" >
            <?php include ("./components/topnavbarclk.php");?>
            <div class="content">
                    <div class="row">
          <div class="col-lg-5 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-body">
                <?php
                  if (isset($_SESSION['message'])) {
                      echo "<div class='alert alert-info text-center'>" . $_SESSION['message'] . "</div>";
                      unset($_SESSION['message']);
                  }
                ?>
              <div class="card mt-4">
  <div class="card-body">
    <form class="form-control" action="function_clk.php" method="POST">
      <h2 class="text-center">Check Room Availability</h2>

      <div class="form-group">
        <label>Check-in Date:</label>
        <input type="date" class="form-control" name="check_in_date" required>
      </div>

      <div class="form-group">
        <label>Check-out Date:</label>
        <input type="date" class="form-control" name="check_out_date" required>
      </div>

      <div class="form-group">
        <label>Room Category:</label>
        <select class="form-control" name="idroom_cat" required>
          <option value="">Select Room Category</option>
          <!-- Example categories -->
          <option value="1">Deluxe</option>
          <option value="2">Suite</option>
          <option value="3">Standard</option>
        </select>
      </div>

      <div class="form-group">
        <label>Number of Rooms:</label>
        <input type="number" class="form-control" name="num_rooms" min="1" value="1" required>
      </div>

      <div class="form-group pt-3 text-center">
        <input type="submit" class="btn btn-primary px-5" name="check_availability" value="Check Availability">
      </div>
    </form>
  </div>
</div>

              </div>
            </div>
          </div>
          <div class="col-lg-7 col-md-6 col-sm-12">
  <div class="card">
    <div class="card-body">
  

      <table class="table table-bordered table-hover">
            <h2>Travel Companies</h2>
             <?php
                // Fetch all employees
                $query = "SELECT * FROM travelcompany";
                $result = mysqli_query($con, $query);
                ?>
        <thead>
          <tr>
            <th class="text-center">ID</th>
            <th class="text-center">Company Name</th>
            <th class="text-center">Contact Person</th>
            <th class="text-center">Email</th>
            <th class="text-center">Phone</th>
            <th class="text-center">Address</th>
            <th class="text-center">Registered Date</th>
            <th class="text-center">Credit Card #</th>
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
              <td class="text-center"><?= htmlspecialchars($row['travel_contact_email']) ?></td>
              <td class="text-center"><?= htmlspecialchars($row['travel_phone']) ?></td>
              <td class="text-center"><?= htmlspecialchars($row['travel_address']) ?></td>
              <td class="text-center"><?= htmlspecialchars($row['travel_registered_date']) ?></td>
              <td class="text-center"><?= htmlspecialchars($row['travel_credit_cardnum']) ?></td>
              <td class="text-center">
                <!-- Edit Button -->
                <a href="?edit_id=<?= htmlspecialchars($row['travel_company_id']) ?>" 
                   class="btn btn-sm btn-success mb-1">Edit</a>
                
                <!-- Delete Form -->
                <form action="function_clk.php" method="POST" style="display:inline;">
                  <button type="submit"
                          class="btn btn-sm btn-danger"
                          name="btn_delete_travel"
                          value="<?= htmlspecialchars($row['travel_company_id']) ?>">
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          <?php
            endwhile;
          else:
          ?>
            <tr>
              <td colspan="9" class="text-center">No Travel Companies found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

 

             
    </div>

  <!--   Core JS Files   -->
  <script src="./assets/js/core/jquery.min.js"></script>
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <!--  Google Maps Plugin    -->
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
  <!-- Chart JS -->
  <script src="./assets/js/plugins/chartjs.min.js"></script>
  <!--  Notifications Plugin    -->
  <script src="./assets/js/plugins/bootstrap-notify.js"></script>
  <!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
    <script src="../Manager/assets/js/custom.js"></script>
  <script>
  document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('sidebar-show');
  });
</script>

</body>

</html>
