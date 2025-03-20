<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style/style.css" rel="stylesheet" type="text/css" />
    <title>Assignment2</title>
</head>
<?php
     $page = 'indexpage';
     include('functions/header.php');
?>
<body>
    <div class="index-all">
    <div class="index">
        <h2>Assignment Home Page</h2>
        <p><strong>Name:</strong>Nguyen Quoc Thang</p>
        <p><strong>Student ID:</strong>104193360</p>
        <p><strong>Email:</strong><a href="104193360@student.swin.edu.au">104193360@student.swin.edu.au</a></p>
        <p><i>I declare that this assignment is my individual work. I have not worked collaboratively nor have I copied from any other student’s work or from any other source.</i></p>
    </div>
    <?php
       
        require_once('functions/function.php');
        require_once('functions/settings.php');
        check_exist_table($conn);
    ?>
    <div class="link_file">
        <a href="login.php">Log-In</a>
        <a href="signup.php">Sign-Up</a>
        <a href="about.php">About</a>
    </div>
    </div>
</body>
</html>