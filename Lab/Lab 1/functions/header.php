<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../style/style.css" rel="stylesheet" type="text/css" />    <script src="https://kit.fontawesome.com/f30e8be6f4.js" crossorigin="anonymous"></script>
    <title>Assignment2</title>
</head>
<body>
    <header>
        <nav>
            <div class="header">
                <a href="index.php">My Friend System</a>
            </div>
            <div class="page">
                <ul>
                    <li><a href="index.php"<?php echo ($page == "indexpage") ? "class='selected'" : ""; ?>>Home</a></li>
                    <li><a href="signup.php"<?php echo ($page == "signup") ? "class='selected'" : ""; ?>>Sign-Up</a></li>
                    <li><a href="login.php"<?php echo ($page == "login") ? "class='selected'" : ""; ?>> Log-In </a></li>
                    <li><a href="about.php"<?php echo ($page == "about") ? "class='selected'" : ""; ?>> About </a></li>
                </ul>
            </div>
        
        </nav>
    </header>
</body>
</html>