  <?php include_once('../Database/db_connection.php');
  
    if(isset($_POST['btn_rc_add']) ) {
        $_room_cat = mysqli_real_escape_string($con,$_POST['room_cat']);
        $_book_dur = mysqli_real_escape_string($con,$_POST['book_dur']);
        $_r_price = mysqli_real_escape_string($con,$_POST['r_price']);
       
        
        $file_name = $_FILES ['img_room']['name'];
        $tmp_name = $_FILES [ 'img_room']['tmp_name'];
        $folder = 'upload/'.$file_name;

        $sql =  "INSERT INTO room_cat(room_cat,booking_due,price_room,img_room)
         VALUES('$_room_cat','$_book_dur', '$_r_price','$file_name') " ; 
         
         $run = mysqli_query($con,$sql);

         if($run) {
          move_uploaded_file($_FILES['img_room']['tmp_name'],"$folder");
          echo("Successfully Inserted!!");
    }else{
      echo("Failed");
    }
    }
///upload room_cat
 if (isset($_POST['btn_rc_upload'])) {
    // Sanitize input
    $id= mysqli_real_escape_string($con, $_POST['idroom_cat']);
    $_room_cat = mysqli_real_escape_string($con, $_POST['room_cat']);
    $_book_dur = mysqli_real_escape_string($con, $_POST['book_dur']);
    $_r_price = mysqli_real_escape_string($con, $_POST['r_price']);

    $file_name = $_FILES['img_room']['name'];
    $tmp_name = $_FILES['img_room']['tmp_name'];

    // Check if a new image is uploaded
    if (!empty($file_name)) {
        $folder = 'upload/' . $file_name;

        $sql = "UPDATE room_cat SET 
                    room_cat = '$_room_cat', 
                    booking_due = '$_book_dur', 
                    price_room = '$_r_price', 
                    img_room = '$file_name'
                WHERE idroom_cat = '$id'";

        $run = mysqli_query($con, $sql);

        if ($run) {
            move_uploaded_file($tmp_name, $folder);
            echo "<script>alert('Room category updated successfully with image!'); window.location.href='roomcat.php';</script>";
        } else {
            echo "<script>alert('Update failed.'); window.history.back();</script>";
        }
    } else {
        // Update without changing the image
        $sql = "UPDATE room_cat SET 
                    room_cat = '$_room_cat', 
                    booking_due = '$_book_dur', 
                    price_room = '$_r_price'
                WHERE idroom_cat = '$id'";

        $run = mysqli_query($con, $sql);

        if ($run) {
            echo "<script>alert('Room category updated successfully without image change!'); window.location.href='roomcat.php';</script>";
        } else {
            echo "<script>alert('Update failed.'); window.history.back();</script>";
        }
    }
  }

// Delete room cat
  if (isset($_POST['btn_delete_rc'])) {
    $id_to_delete = mysqli_real_escape_string($con, $_POST['btn_delete_rc']);

    // Delete query
    $sql = "DELETE FROM room_cat WHERE idroom_cat = '$id_to_delete'";

    if (mysqli_query($con, $sql)) {
        // Success: Redirect or show message
        header("Location: roomcat.php?msg=deleted");
        exit;
    } else {
        echo "Error deleting record: " . mysqli_error($con);
    }
} else {
    // No ID passed, redirect or show error
    header("Location: roomcat.php?msg=error");
    exit;
}
?>


 











?>

    