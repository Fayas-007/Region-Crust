<?php
session_start();
?>
<?php include("../Database/db_connection.php");?> 


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
      <?php include("../Database/db_connection.php");?> 
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
              <form class="form-control" action="function.php" method="POST" enctype="multipart/form-data">
                  <div class="form-group">
                    <h2 class="p-4 text-center">ROOM CATEGORY</h2>
                      <label for="">Room category</label>
                      <input type="text" class="form-control" name="room_cat" required>
                  </div> 

                  <div class="form-group">
                      <label for="bookingDuration">Booking Duration</label>
                      <select class="form-control" id="bookingDuration" name="book_dur" required>
                          <option value="">-- Select Duration --</option>
                          <option value="Per Day">Per Day</option>
                          <option value="Per Week">Per Week</option>
                          <option value="Per Month">Per Month</option>
                      </select>
                  </div> 

                  <div class="form-group">
                      <label for="">Price</label>
                      <input type="number" class="form-control" name="r_price" required>
                  </div>

                  <div class="form-group">
                      <label for="">Room Image</label>
                      <input type="file" class="form-control" name="img_room" required>
                  </div>

                  <div class="form-group pt-3 text-center">
                      <input type="submit" class="btn btn-success btn-sm px-5" name="btn_rc_add" value="Submit">
                      <input type="reset" class="btn btn-warning btn-sm px-5" value="Reset">
                  </div>
              </form>
          </div>
          </div>
          </div>

          <div class="col-lg-8 col-md-6 col-sm-12">
                 <!-- Table Panel -->
               <div class="card card-stats"> 
                  <div class = "card-body">
              <table class="table table-bordered table-hover  ">
               
                    <h2>View Table</h2>
                        <thead>
                          <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Room</th>
                            <th class="text-center">Rate type</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Image</th>
                            <th class="text-center">Action</th>
                          </tr>
                        </thead>
            
                <tbody>
                  <?php
                  $sql = "SELECT * FROM room_cat";
                  $run = mysqli_query($con, $sql);

                  if (mysqli_num_rows($run) > 0) {
                    foreach ($run as $row) {
                      ?>
                      <tr>
                        <td class="text-center"><?= htmlspecialchars($row['idroom_cat']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['room_cat']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['rate_type']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['price_room']) ?></td>
                        <td class="text-center">
                          <img width="50px" src="upload/<?= htmlspecialchars($row['img_room']) ?>" alt="Room Image">
                        </td>
                        <td class="text-center">
                       <!-- Button  style) -->
                          <div>
                            <!-- Edit Button -->
                            <a href="edit_r.php?id=<?= htmlspecialchars($row['idroom_cat']) ?>" 
                              class="btn btn-sm btn-success mr-2">Edit</a>

                            <!-- Delete Button -->
                            <form action="function.php" method="POST" style="display: inline;">
                              <button type="submit" 
                                      class="btn btn-sm btn-danger" 
                                      name="btn_delete_rc" 
                                      value="<?= htmlspecialchars($row['idroom_cat']) ?>">
                                Delete
                              </button>
                            </form>

                            
                          </div>
                          

                          </td>

                      </tr>
                      <?php
                    }
                  } else {
                    echo "<tr><td colspan='6' class='text-center'>No records found</td></tr>";
                  }
                  ?>
                        </div>
                  </div>
                </tbody>
              </table>
              <!-- Table Panel -->
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
  <!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
    <script>
  document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('sidebar-show');
  });
</script>
 
</body>

</html>
