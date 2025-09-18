<?php
include('connect.php');
$value=1;
$val=1;
try
{
if(isset($_POST['register-user']))
{
    // Function to remove leading and trailing whitespaces,'_','\', and prevent Cross Site Scripting (XSS) Attacks 
    function validateInput($data)
    {
        $data=trim($data,"_");
        $data=stripslashes($data);
        $data=htmlspecialchars($data);
        return $data;
    }

    // Function To Prevent Cross Site Scripting (XSS) Attacks
    function XSSPrevention($data)
    {
        $data=htmlspecialchars($data);
        return $data;
    }

    $fullname=validateInput($_POST['fullname']);
    $username=XSSPrevention(trim($_POST['username']));
    $email=validateInput($_POST['email']);
    $gender=validateInput($_POST['gender']);
    $phone=XSSPrevention($_POST['mobile']);

    // Hashing the Password before storing in Database 
    $password=password_hash($_POST['password'], PASSWORD_ARGON2ID); 
    $confirm_password=$_POST['cpassword'];

    // Checking if username or email exists
    $stmt=$conn->prepare("Select * from patient_master where username=? or email=?");
    $stmt->bindParam(1,$username);
    $stmt->bindParam(2,$email);
    $stmt->execute();
    if($stmt->rowCount()>0)
    {
        $val=0;
    }
    
    // Password Verification
    if(password_verify($confirm_password,$password)==FALSE)
    {
        $value=0;
    }

    if($value==1 && $val==1)
    {
    // Insert data into table patient_master and prevent SQL Injection Attacks
    $stmt=$conn->prepare("insert into patient_master (pname, username, email, password, gender, phone) values (?,?,?,?,?,?)");
    $stmt->bindParam(1, $fullname);
    $stmt->bindParam(2, $username);
    $stmt->bindParam(3, $email);
    $stmt->bindParam(4, $password);
    $stmt->bindParam(5, $gender);
    $stmt->bindParam(6, $phone);
    $stmt->execute();
    $records=$stmt->fetch(PDO::FETCH_ASSOC);
    $id=$conn->lastInsertId();
    echo "<h1>Registered Successfully ! Your ID is : $id . Redirecting to LogIn Page...</h1>";
    header("refresh:4;url=login-user.php");
    exit();
    }
    else if($value==0)
    {
        echo "<h1>Passwords Do Not Match. Please Enter Correct Password</h1>";
    }
    else
    {
        echo "<h1>Username or Email exists.</h1>";
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
    <link rel="stylesheet" href="style.css">
    <style>
    form input
    {
        display: block;
        margin: 9px;
        width: 75%;
    }  
    .gender-div
    {
        width: 75%;
        margin-left: 9px;        
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
        width: 75%;
        text-align: center;
    }
    .return-home, .return-home:hover
    {
        color: white;
        text-decoration: none;
    }
    </style>
    <!-- Own CSS End -->
    <title>Register</title>
</head>
<body>
    <?php if(isset($_POST['use']) || isset($_POST['bed-book']) || isset($_POST['appt-schedule']) || isset($_POST['ambulance-call']) || isset($_POST['register']) || isset($_POST['Profile'])): ?>

    <div class="form-div">
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
    <h1>Register to Get Started</h1> 
    <input type="text" placeholder="Enter your fullname" name="fullname" required>
    <input type="text" placeholder="Enter your username" name="username" required>
    <input type="email" placeholder="Enter your email" name="email" required>
    <input type="password" placeholder="Enter your password" name="password" required>
    <input type="password" placeholder="Confirm password" name="cpassword" required>
    <div class="gender-div">
    <select name="gender" required>
        <option value="male">Male</option>
        <option value="female">Female</option>
        <option value="other">Other</option>
    </select> 
    </div>    
    <input type="number" placeholder="Enter your phone number" name="mobile" required>
    <h5 style="margin-bottom: 1vw !important;">Already have an account ? <a href="login-user.php">Sign In</a></h5>
    <div class="btn-div">
        <button class="btn btn-success mt-3" type="submit" name="register-user">Register</button>
        <button class="btn btn-primary mt-3"><a href="index.php" class="return-home">Go To Home Page</a></button>
    </div>
    </form> 
    </div>    

    <?php endif ?>

    <!-- BootStrap JS Start -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- Bootstrap JS End -->
</body>
</html>