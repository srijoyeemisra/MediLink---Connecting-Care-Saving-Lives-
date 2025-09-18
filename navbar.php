<?php
include('connect.php');
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
    .btn1
    {
        border: none;
    } 
    .btn-div1 form, .medi-div, nav
    {
        display: inline-block !important;
    }    
    </style>
    <!-- Own CSS Ends -->

    <title>Document</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="about.php">About Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contact.php">Contact Us</a>
        </li>
        <li class="nav-item">
        <form action="Profile.php" method="POST">
          <button class="nav-link btn1" type=submit name="Profile"><?php echo $username ?></button>
        </form>                
        </li> 

        <?php if($_SESSION['username']!=="Profile"): ?>
        <li>
        <form action="logout.php" method="POST">
        <button class="btn btn-danger mx-1" type=submit>LogOut</button>
        </form>
        </li>
        <?php endif ?>

      </ul>
    </div>
    </div>
    </nav>

    <!-- Medical Facilities Start -->
            <div class="medi-div">
             <?php if($username=="Profile"): ?>
           <div class="btn-div" style="display: inline-block;">
            <form action="register-user.php" method="POST">
           <button class="btn btn-danger mx-3 mt-2" type="submit" name="bed-book">Book a Bed</button>
           <button class="btn btn-secondary mx-3 mt-2" type="submit" name="appt-schedule">Schedule Appointments</button>
           <button class="btn btn-warning mt-2 mx-3" type="submit" name="ambulance-call">Call Ambulance</button>
            </form>
           </div>        
            <?php endif ?>

            <?php if($username!=="Profile"): ?>
           <div class="btn-div1" style="display: inline-block;">
            <form action="bed-book.php" method="POST">
           <button class="btn btn-danger mx-1" type="submit" name="bed-book">Book a Bed</button>
            </form>
            <form action="appointment-schedule.php" method="POST">
           <button class="btn btn-secondary mx-1" type="submit" name="appt-schedule">Schedule Appointments</button>
            </form>
            <form action="ambulance-call.php" method="POST">
           <button class="btn btn-warning mt-1 mx-1" type="submit" name="ambulance-call">Call Ambulance</button>
            </form>
            <button onclick="window.location.href='appointments_history.php'" class="btn btn-primary">
             View Appointment History
            </button>
           </div>        
            <?php endif ?>
            </div>       
    <!-- Medical Faciliies End -->

    <!-- Bootstrap JS Starts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> 
    <!-- Bootstrap JS Ends -->
</body>
</html>
