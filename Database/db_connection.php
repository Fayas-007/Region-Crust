<?php  

$_server = "localhost";
$_username = "root";
$_password = "";
$_database = "region_crust";

$con = mysqli_connect($_server,$_username,$_password, $_database);

if(!$con){
    die("Connection Failed !!!S". mysqli_connect_error());
}else
{ 
    echo"";
  }

  

?>