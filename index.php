<?php
include('connect.php');
session_start();
if(!isset($_SESSION['username']))
{
    $_SESSION['username']="Profile";
}
$username=$_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- BootStrap CSS Starts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- BootStrap CSS Ends -->

    <!-- Google Font Starts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Google Font Ends -->

    <!-- Font Awesome Starts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Font Awesome Ends -->

    <!-- Own CSS Starts -->     
    <style>
    .logo-div, .navbar-div
    {
        display: inline-block;
    }
    .logo-div
    {
        color: skyblue;
        font-size: 30px;
    }
    </style>
    <!-- Own CSS Ends -->
     
    <title>Document</title>
</head>
<body>    
    <!-- Logo Start -->
     <div class="logo-div">
        Logo
     </div>
     <!-- Logo End -->

     <!-- NavBar Start -->
     <div class="navbar-div">
     <?php 
      include('navbar.php');
     ?>  
     </div>
     <!-- NavBar End -->

     <!-- Main Body Start -->
     
     <!-- Main Body End -->

    <!-- Bootstrap JS Starts -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>  -->
    <!-- Bootstrap JS Ends -->
</body>
</html>

