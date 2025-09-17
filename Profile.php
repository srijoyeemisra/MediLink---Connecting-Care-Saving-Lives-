<?php
include('connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS Start -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap CSS End -->

    <!-- Google Font Start -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Google Font End -->

    <!-- Own CSS Start -->
    <link rel="stylesheet" href="./css/style.css"> 
    
    <style>
    form
    {
        display: inline-block;
    }
    </style>
    <!-- Own CSS End -->
    <title>Welcome To Our Website</title>
</head>
<body>
    <div class="form-div">
    <form action="register-user.php" method="POST">
    <h1>What Brings You To Us ?</h1>    
    <button class="btn1" type="submit" name="use">I want to Use Your Service</button>    
    </form> 
    <form action="register-admin.php" method="POST">  
    <button class="btn2" type="submit" name="give">I want to Provide Service</button>            
    </form>
    </div>   

    <!-- BootStrap JS Start -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- Bootstrap JS End -->
</body>
</html>