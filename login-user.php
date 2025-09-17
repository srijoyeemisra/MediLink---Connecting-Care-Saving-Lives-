<?php
include('connect.php');
session_start();
try
{
if(isset($_POST['login-user']))
{
   $username=$_POST['username'];
   $password=$_POST['password'];
   
   $stmt=$conn->prepare("Select * from patient_master where username=?");
   $stmt->bindParam(1,$username);
   $stmt->execute();

   $user=$stmt->fetch(PDO::FETCH_ASSOC);

   if($user && password_verify($password,$user['password']))
   {
     $_SESSION['username']=$user['username'];
     echo "LogIn Successful";
     header("refresh:3;url=index.php");
     exit();
   }
   else
   {
     echo "Invalid username or password";
   }
}
} catch(PDOException $e)
{
    echo "Error : " . $e->getMessage();
}
$conn=null;
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
    form input
    {
        display: block;
        margin: 9px;
        width: 75%;
    }     
    h5 a, h5 a:hover
    {
        text-decoration: solid;
        color: black;
        background-color: skyblue;
        padding: 5px;
        border-radius: 5px;
        border-color: blue;
        border-width: 2px;
        border-style: solid;
    }
    .btn-div
    {
        text-align: center;
    }   
    .return-home, .return-home:hover
    {
        color: white;
        text-decoration: none;        
    } 
    </style>
    <!-- Own CSS End -->
    <title>User Login</title>
</head>
<body>
    <div class="form-div">
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
    <h2>Welcome Back ! Please Sign In to Continue</h2> 
    <input type="text" placeholder="Enter your username" name="username" required>
    <input type="password" placeholder="Enter your password" name="password" required>    
    <div class="btn-div">
        <button class="btn btn-success mt-3" type="submit" name="login-user">Sign In</button>
    </div>
    </form> 
    <div style="text-align:center;">
     <h5>Don't have an account ? 
        <form action="register-user.php" method="POST">
            <button class="btn btn-warning" type="submit" name="register">Register</button>
        </form>
    </h5>
    </div>      
    </div>

    
     
    <div style="text-align: center; width: 75%;">
     <button class="btn btn-primary mt-2"><a href="index.php" class="return-home">Go To Home Page</a></button>
    </div>
    
    
    <!-- BootStrap JS Start -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- Bootstrap JS End -->
</body>
</html>