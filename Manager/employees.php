<?php
session_start();
?>
  <?php include("../Database/db_connection.php");?> 
<?php
 

$editMode = false;
$editEmployee = null;

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $query = "SELECT * FROM employee WHERE employee_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $editEmployee = mysqli_fetch_assoc($result);
        $editMode = true;
    }
    mysqli_stmt_close($stmt);
}
?>

<!doctype html>
<html lang="en">
<?php include("./mdcomponends/head.php");?> 


<body class="">
  <div class="wrapper ">
    <!-- Hamburger Toggle Button -->
    <button id="sidebarToggle" class="navbar-toggler d-lg-none" type="button" style="position: fixed; top: 15px; left: 10px; z-index: 1050;  background: transparent; border: none; padding: 1px 0px; border-radius: 1px;">
      <i class=" " style="font-size: 1.5rem;"></i>
    </button>
     <?php include("./mdcomponends/sidebar.php");?> 
    
    <div class="main-panel" >
         <?php include ("./mdcomponends/topnavbar.php");?>
      <div class="content">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card">
                  <div class="card-body">
                        <?php
                          if (isset($_SESSION['message'])) {
                              echo "<div class='alert alert-info text-center'>" . $_SESSION['message'] . "</div>";
                              unset($_SESSION['message']);
                          }
                        ?>
                  <div class="card">
                  <div class="card-body">
                     <form class="form-control" action="function.php" method="POST">
                        <h2 class="text-center">Register Employee</h2>
                      <input type="hidden" name="employee_id" value="<?= $editEmployee['employee_id'] ?? '' ?>">

                          <div class="form-group " >
                                <label>Name:</label>
                               <input type="text" class="form-control" name="emp_name" value="<?= $editEmployee['emp_name'] ?? '' ?>" required>
                          </div>
                          <div class="form-group" >
                             <label>Phone:</label>
                          <input type="text"  class="form-control"name="emp_phone" value="<?= $editEmployee['emp_phone'] ?? '' ?>" required>
                          </div>
                          <div class="form-group">
                                                        <label>Email:</label>
                          <input type="email" class="form-control"name="emp_email" value="<?= $editEmployee['emp_email'] ?? '' ?>" required>
                          </div>
                          <div class="form-group">
                             <label>Phone:</label>
                          <input type="text" class="form-control" name="emp_phone" value="<?= $editEmployee['emp_phone'] ?? '' ?>" required>
                          </div>
                          <div class="form-group">
                              <label>Username:</label>
                          <input type="text" class="form-control"name="emp_username" value="<?= $editEmployee['emp_username'] ?? '' ?>" required>
                          </div>
                          <div class="form-group">
                            <label>Password:</label>
                          <input type="text" class="form-control" name="emp_password" value="<?= $editEmployee['emp_password'] ?? '' ?>" required>
                          </div>
                          <div class="form-group">
                              <label>Role:</label>
                          <input type="text" class="form-control" name="emp_role" value="<?= $editEmployee['emp_role'] ?? '' ?>" required>
                          </div>
                                <div class="form-group pt-3 text-center">
                                  
                                  <input type="reset" class="btn btn-success btn-sm px-5 " onclick="clearForm()" value="Reset">
                             
                                
                          <input type="submit" 
                                class="btn <?= $editMode ? 'btn-warning' : 'btn-primary' ?> btn-sm px-5" 
                                name="<?= $editMode ? 'btn_update_employee' : 'btn_add_employee' ?>" 
                                value="<?= $editMode ? 'Update Employee' : 'Add Employee' ?>">
                         </div>
                     </form>
                      </div>
                    </div>
                  </div>
              </div>
            </div>
            <div class="col-lg7 col-md-6 col-sm-12">
              <div class="card">
                <div class="card-body">
              

      

                <table class="table table-bordered table-hover">
                      <h2>Employees</h2>
                    
                    <?php
                // Fetch all employees
                $query = "SELECT * FROM employee";
                $result = mysqli_query($con, $query);
                ?>
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Username</th>
                            <th class="text-center">Password</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($row['employee_id']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['emp_name']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['emp_phone']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['emp_email']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['emp_username']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['emp_password']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['emp_role']) ?></td>
                                    <td class="text-center">
                                                    <!-- Action Buttons -->
                                                    <div>
                                                      <!-- Edit -->
                                                
                                            <!-- Edit Button -->
                                            <a href="?edit_id=<?= htmlspecialchars($row['employee_id']) ?>" 
                                              class="btn btn-sm btn-success mr-2">
                                              Edit
                                            </a>

                                                      <!-- Delete -->
                                                      <form action="function.php" method="POST" style="display: inline;">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-danger" 
                                                                name="btn_delete_employee" 
                                                                value="<?= htmlspecialchars($row['employee_id']) ?>">
                                                          Delete
                                                        </button>
                                                      </form>
                                                    </div>
                                                  </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No Employees found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
    </div>
</div>

                      </div>
                    </div>
                  </div>
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
  <script src="./assets/js/manager.js"></script>
  <!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
  <script src="./assets/js/custom.js"></script>

    <script>
  document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('sidebar-show');
  });
</script>
 
</body>

</html>
