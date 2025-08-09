<?php
session_start();
include_once('../Database/db_connection.php');

// ===========================
// ADD ROOM CATEGORY
// ===========================
if (isset($_POST['btn_rc_add'])) {
    $_room_cat = mysqli_real_escape_string($con, $_POST['room_cat']);
    $_book_dur = mysqli_real_escape_string($con, $_POST['book_dur']);
    $_r_price = mysqli_real_escape_string($con, $_POST['r_price']);
    
    $file_name = $_FILES['img_room']['name'];
    $tmp_name = $_FILES['img_room']['tmp_name'];
    $folder = 'upload/' . $file_name;

    $sql = "INSERT INTO room_cat(room_cat, rate_type, price_room, img_room)
            VALUES ('$_room_cat', '$_book_dur', '$_r_price', '$file_name')";

    $run = mysqli_query($con, $sql);

    if ($run) {
        move_uploaded_file($tmp_name, $folder);
        $_SESSION['message'] = "✅ Room category added successfully!";
    } else {
        $_SESSION['message'] = "❌ Failed to add room category: " . mysqli_error($con);
    }

    header("Location: roomcat.php");
    exit;
}

// ===========================
// UPDATE ROOM CATEGORY
// ===========================
if (isset($_POST['btn_rc_upload'])) {
    $id = mysqli_real_escape_string($con, $_POST['idroom_cat']);
    $_room_cat = mysqli_real_escape_string($con, $_POST['room_cat']);
    $_book_dur = mysqli_real_escape_string($con, $_POST['book_dur']);
    $_r_price = mysqli_real_escape_string($con, $_POST['r_price']);

    $file_name = $_FILES['img_room']['name'];
    $tmp_name = $_FILES['img_room']['tmp_name'];

    if (!empty($file_name)) {
        $folder = 'upload/' . $file_name;

        $sql = "UPDATE room_cat SET 
                    room_cat = '$_room_cat', 
                    rate_type = '$_book_dur', 
                    price_room = '$_r_price', 
                    img_room = '$file_name'
                WHERE idroom_cat = '$id'";

        $run = mysqli_query($con, $sql);

        if ($run) {
            move_uploaded_file($tmp_name, $folder);
            $_SESSION['message'] = "✅ Room category updated successfully with new image!";
        } else {
            $_SESSION['message'] = "❌ Failed to update: " . mysqli_error($con);
        }
    } else {
        $sql = "UPDATE room_cat SET 
                    room_cat = '$_room_cat', 
                    rate_type = '$_book_dur', 
                    price_room = '$_r_price'
                WHERE idroom_cat = '$id'";

        $run = mysqli_query($con, $sql);

        if ($run) {
            $_SESSION['message'] = "✅ Room category updated successfully (no image change).";
        } else {
            $_SESSION['message'] = "❌ Update failed: " . mysqli_error($con);
        }
    }

    header("Location: roomcat.php");
    exit;
}

// ===========================
// DELETE ROOM CATEGORY
// ===========================
if (isset($_POST['btn_delete_rc'])) {
    $id_to_delete = mysqli_real_escape_string($con, $_POST['btn_delete_rc']);

    $sql = "DELETE FROM room_cat WHERE idroom_cat = '$id_to_delete'";

    if (mysqli_query($con, $sql)) {
        $_SESSION['message'] = "✅ Room category deleted successfully.";
    } else {
        $_SESSION['message'] = "❌ Error deleting record: " . mysqli_error($con);
    }

    header("Location: roomcat.php");
    exit;
}

// ===========================
// ADD EMPLOYEE with username uniqueness check
// ===========================
if (isset($_POST['btn_add_employee'])) {
    $emp_name = mysqli_real_escape_string($con, $_POST['emp_name']);
    $emp_email = mysqli_real_escape_string($con, $_POST['emp_email']);
    $emp_phone = mysqli_real_escape_string($con, $_POST['emp_contact']);
    $emp_username = mysqli_real_escape_string($con, $_POST['emp_username']);
    $emp_password = mysqli_real_escape_string($con, $_POST['emp_password']);
    $emp_role = mysqli_real_escape_string($con, $_POST['emp_role']);

    // Optional: hash password (recommended)
    // $emp_password = password_hash($emp_password, PASSWORD_DEFAULT);

    // Check if username already exists
    $checkQuery = "SELECT emp_username FROM employee WHERE emp_username = '$emp_username' LIMIT 1";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // Username exists, set error message
        $_SESSION['message'] = "❌ Username already taken. Please choose another.";
    } else {
        // Username doesn't exist, proceed with insert
        $query = "INSERT INTO employee (emp_name, emp_email, emp_phone, emp_username, emp_password, emp_role) 
                  VALUES ('$emp_name', '$emp_email', '$emp_phone', '$emp_username', '$emp_password', '$emp_role')";
        
        $result = mysqli_query($con, $query);

        if ($result) {
            $_SESSION['message'] = "✅ Employee Registered Successfully!";
        } else {
            $_SESSION['message'] = "❌ Error: " . mysqli_error($con);
        }
    }

    header("Location: employees.php");
    exit;
}

// ===========================
// DELETE EMPLOYEE 
// ===========================
// Include database connection

    if (isset($_POST['btn_delete_employee'])) {
        // Sanitize the employee ID
        $employeeId = intval($_POST['btn_delete_employee']);

        // Delete query
        $query = "DELETE FROM employee WHERE employee_id = ?";
        
        // Use prepared statements for security
        $stmt = mysqli_prepare($con, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $employeeId);
            if (mysqli_stmt_execute($stmt)) {
                // Success - redirect or show message
                header("Location: employees.php?message=Employee+deleted+successfully");
                exit();
            } else {
                // Error executing query
                echo "Error deleting employee: " . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Error preparing statement
            echo "Error preparing statement: " . mysqli_error($con);
        }
    }

// ===========================
// UPDATE EMPLOYEE 
// ===========================
 

if (isset($_POST['btn_update_employee'])) {
    $id = intval($_POST['employee_id']);
    $name = $_POST['emp_name'];
    $phone = $_POST['emp_phone'];
    $email = $_POST['emp_email'];
    $username = $_POST['emp_username'];
    $password = $_POST['emp_password'];
    $role = $_POST['emp_role'];

    $query = "UPDATE employee SET emp_name=?, emp_phone=?, emp_email=?, emp_username=?, emp_password=?, emp_role=? WHERE employee_id=?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssssi", $name, $phone, $email, $username, $password, $role, $id);
    mysqli_stmt_execute($stmt);

    header("Location: employees.php?message=Employee+updated+successfully");
    exit();
}

?>



