<?php
session_start();
include("../Database/db_connection.php");
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        show_toast(" . json_encode($toast['message']) . ", " . json_encode($toast['type']) . ");
      });
    </script>";
    unset($_SESSION['toast']);
}


?>

<!doctype html>
<html lang="en">
<?php include("./components/head.php"); ?>
<body>

<div class="wrapper">
  <div id="toast-container" style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;"></div>

  <button id="sidebarToggle" class="navbar-toggler d-lg-none" type="button" style="position: fixed; top: 5px; left: 15px; z-index: 1050; background: white; border: none; padding: 8px 10px; border-radius: 4px;">
    <i class="bi bi-list" style="font-size: 1.5rem;"></i>
  </button>

  <?php include("./components/sidebarclk.php"); ?>

  <div class="main-panel">
    <div class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h2 class="text-center">Reservations</h2>
              <div class="d-flex justify-content-end mb-3">
                <a href="MakeReservation.php" class="btn btn-success">Make Reservation</a>
              </div>
              <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Reserved By</th>
                    <th>User Type</th>
                    <th>Room Category</th>
                    <th>Reservation Date</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Rooms</th>
                    <th>Occupants</th>
                    <th>Status</th>
                    <th>Special Requests</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $sql = "
                      SELECT 
                        r.*,
                        rc.room_cat AS room_category,
                        COALESCE(c.cus_name, tc.travel_name) AS reserved_by,
                        CASE 
                          WHEN r.idcus IS NOT NULL THEN 'Customer'
                          WHEN r.travel_company_id IS NOT NULL THEN 'Travel Company'
                          ELSE 'Guest'
                        END AS user_type,
                        c.credit_cardnum AS customer_card,
                        tc.travel_credit_cardnum AS travel_card
                      FROM reservation r
                      LEFT JOIN customer c ON r.idcus = c.idcus
                      LEFT JOIN travel_company tc ON r.travel_company_id = tc.travel_company_id
                      JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat
                      ORDER BY r.reservation_date DESC
                    ";

                    $run = mysqli_query($con, $sql);

                    if (mysqli_num_rows($run) > 0) {
                      foreach ($run as $row) {
                        $status = strtolower(trim($row['status']));
                        $hasCard = !empty($row['customer_card']) || !empty($row['travel_card']);
                        $checkInDate = strtotime($row['check_in_date']);
                        $today = strtotime(date('Y-m-d'));
                        $isResidential = isset($row['is_residential_suite']) && $row['is_residential_suite'] == 1;
                  ?>
                  <tr>
                    <td class="text-center"><?= htmlspecialchars($row['reservation_id']) ?></td>
                    <td><?= htmlspecialchars($row['reserved_by']) ?></td>
                    <td><?= htmlspecialchars($row['user_type']) ?></td>
                    <td><?= htmlspecialchars($row['room_category']) ?></td>
                    <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                    <td><?= htmlspecialchars($row['check_in_date']) ?></td>
                    <td><?= htmlspecialchars($row['check_out_date']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['num_rooms']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['num_occupants']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['special_requests']) ?></td>
                    <td class="text-center" style="min-width: 160px;">
                      <?php
                        $id = $row['reservation_id'];

                        // Convert status to lowercase for consistent comparison
                        $status = strtolower(trim($row['status']));
                        $checkInDate = strtotime($row['check_in_date']);
                        $today = strtotime(date('Y-m-d'));
                        $isResidential = isset($row['is_residential_suite']) && $row['is_residential_suite'] == 1;

                        // Show Edit, Check In, Mark No-Show, Delete buttons for tentative or confirmed reservations
                        if (in_array($status, ['tentative', 'confirmed'])) {
                            echo '<a href="edit_reservation.php?reservation_id=' . $id . '" class="btn btn-sm btn-primary w-100 mb-2">Edit</a>';

                            if ($status === 'confirmed') {
                                // Show "Mark No-Show" if today is after check-in date (for both residential and non-residential)
                                if ($checkInDate < $today) {
                                    echo '<a href="send_billing_emails.php?reservation_id=' . $id . '" class="btn btn-sm btn-warning w-100 mb-2">Mark No-Show</a>';
                                }

                                echo '<a href="checkin.php?reservation_id=' . $id . '" class="btn btn-sm btn-success w-100 mb-2">Check In</a>';
                            }

                            echo '<a href="delete_reservation.php?reservation_id=' . $id . '" class="btn btn-sm btn-danger w-100 mb-2">Cancel</a>';

                        } elseif ($status === 'checked-in') {
                            echo '<a href="checkout.php?reservation_id=' . $id . '" class="btn btn-sm btn-success w-100 mb-2">Check Out</a>';
                            echo '<a href="delete_reservation.php?reservation_id=' . $id . '" class="btn btn-sm btn-danger w-100 mb-2">Cancel</a>';
                        } elseif ($status === 'no-show') {
                            // If already marked no-show, no Mark No-Show button; only show Delete
                            echo '<a href="delete_reservation.php?reservation_id=' . $id . '" class="btn btn-sm btn-danger w-100">Cancel</a>';
                        } else {
                            echo '<a href="delete_reservation.php?reservation_id=' . $id . '" class="btn btn-sm btn-danger w-100">Cancel</a>';
                        }

                        // Handling billing buttons for residential suites after check-in (unchanged)
                      // Handling billing buttons for residential suites after check-in (FOR TESTING)
                      if ($isResidential && $status === 'checked-in') {

                          $checkIn = strtotime($row['check_in_date']);
                          $checkOut = strtotime($row['check_out_date']);
                          $totalDays = ceil(($checkOut - $checkIn) / 86400);

                          $billingFrequency = $totalDays >= 28 ? 'monthly' : 'weekly';

                          $lastBillingQuery = $con->prepare("
                            SELECT MAX(billing_date) AS last_billed
                            FROM billing
                            WHERE reservation_id = ?
                          ");
                          $lastBillingQuery->bind_param("i", $id);
                          $lastBillingQuery->execute();
                          $billingResult = $lastBillingQuery->get_result()->fetch_assoc();
                          $lastBillingQuery->close();

                          $lastBillingDate = $billingResult['last_billed'] ?? $row['check_in_date'];

                          // ✅ SIMULATION FOR TESTING PURPOSES: Force 7 days passed
                          $daysSinceLastBilling = 7;

                          // Original line (restore this after testing)
                          // $daysSinceLastBilling = floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($lastBillingDate)))) / 86400);

                          if (($billingFrequency === 'weekly' && $daysSinceLastBilling >= 6) ||
                              ($billingFrequency === 'monthly' && $daysSinceLastBilling >= 29)) {
                            echo '<a href="Send Bill Suite.php?reservation_id=' . $id . '" class="btn btn-sm btn-warning w-100 mt-2">Send Bill</a>';
                          } else {
                            $remainingDays = ($billingFrequency === 'weekly') ? (6 - $daysSinceLastBilling) : (29 - $daysSinceLastBilling);
                            echo '<button class="btn btn-sm btn-secondary w-100 mt-2" disabled>Bill Sent (Wait ' . $remainingDays . ' days)</button>';
                          }
                      }

                        ?>

                  </td>
                  </tr>
                  <?php
                      }
                    } else {
                      echo "<tr><td colspan='12' class='text-center'>No reservations found</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Script -->
<script>
  
function show_toast(message, type) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');

    let bgColor = '#333';
    if (type === 'success') bgColor = '#28a745';
    else if (type === 'error') bgColor = '#dc3545';
    else if (type === 'invalid') bgColor = '#ffc107';

    toast.style.background = bgColor;
    toast.style.color = 'white';
    toast.style.padding = '16px 25px';
    toast.style.marginTop = '12px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 0 15px rgba(0,0,0,0.4)';
    toast.style.fontSize = '17px';
    toast.style.maxWidth = '400px';
    toast.style.wordWrap = 'break-word';
    toast.style.transition = 'opacity 0.5s ease';
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => container.removeChild(toast), 500);
    }, 4000);
}
</script>

<!-- JS Files -->
<script src="./assets/js/core/jquery.min.js"></script>
<script src="./assets/js/core/popper.min.js"></script>
<script src="./assets/js/core/bootstrap.min.js"></script>
<script src="./assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
<script src="./assets/js/plugins/chartjs.min.js"></script>
<script src="./assets/js/plugins/bootstrap-notify.js"></script>
<script src="./assets/js/paper-dashboard.min.js?v=2.0.1"></script>

</body>
</html>
