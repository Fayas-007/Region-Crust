 
<!doctype html>
<html lang="en">

 <?php include("./components/head.php");?> 

<body class="">
   <div class="wrapper">
    <?php include("../Clerk/components/sidebarclk.php"); ?> 
    <?php include("../Database/db_connection.php"); ?> 

    <div class="main-panel" style="height: 100vh;">
 

      <div class="container">
       
        <h2 class="p-4 mt-5 text-center">ROOM CATEGORY</h2>
          
        <div class="row">
          <div class="col-md-8 offset-md-2">

            <?php
            // Ensure $id is defined
            if (isset($_GET['id'])) {
              $id = $_GET['id']; // or use $_POST depending on your context
              $sql = "SELECT * FROM room_cat WHERE idroom_cat = '$id'";
              $run = mysqli_query($con, $sql);

              if (mysqli_num_rows($run) > 0) {
                while ($row = mysqli_fetch_assoc($run)) {
            ?>

            <!-- Form starts here -->
            <form action="function.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="idroom_cat" value="<?= htmlspecialchars($row['idroom_cat']) ?>">
            


              <div class="form-group">
                <label for="room_cat">Room Category</label>
                <input type="text" class="form-control" name="room_cat" value="<?= htmlspecialchars($row['room_cat']) ?>" required>
              </div>

              <div class="form-group">
                <label for="bookingDuration">Booking Duration</label>
                <select class="form-control" id="bookingDuration" name="book_dur" required>
                  <option value="">-- Select Duration --</option>
                  <option value="Per Day" <?= $row['booking_due'] == 'Per Day' ? 'selected' : '' ?>>Per Day</option>
                  <option value="Per Week" <?= $row['booking_due'] == 'Per Week' ? 'selected' : '' ?>>Per Week</option>
                  <option value="Per Month" <?= $row['booking_due'] == 'Per Month' ? 'selected' : '' ?>>Per Month</option>
                </select>
              </div>

              <div class="form-group">
                <label for="r_price">Price</label>
                <input type="text" class="form-control" name="r_price" value="<?= htmlspecialchars($row['price_room']) ?>" required>
              </div>

              <div class="form-group">
                <label for="img_room">Room Image</label>
                <input type="file" class="form-control" name="img_room">
                <!-- Optional: show current image -->
                <img src="upload/<?= htmlspecialchars($row['img_room']) ?>" alt="Room Image" style="width: 100px; margin-top: 10px;">
              </div>

              <div class="form-group pt-3 text-center">
                <a href="roomcat.php?id" 
                      class="btn btn-sm btn-primary" 
                      style="display: inline-block;">Back</a>
                <button type="submit" class="btn btn-success btn-sm px-5" name="btn_rc_upload">Upload</button>
                <button type="reset" class="btn btn-warning btn-sm px-5" name = "btn_reset">Reset</button>
                
                
              </div>
            </form>

            <?php
                } // end while
              } else {
                echo "<p class='text-center'>No room found for the given ID.</p>";
              }
            } else {
              echo "<p class='text-center'>Room ID is not set.</p>";
            }
            ?>

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
</body>

</html>
