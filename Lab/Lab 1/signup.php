<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="style/style.css" rel="stylesheet" type="text/css" />
        <title>Assignment2 - Sign Up</title>
    </head>

    <body>
        <?php
        $page = 'signup';
        include('functions/header.php');
        ?>
        <div class="signup-form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" class="signup-form">
                <h2>Sign Up</h2>
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="input-group">
                    <label for="profile_name">Profile Name:</label>
                    <input type="text" name="profile_name" id="profile_name" required>
                </div>
                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="input-group">
                    <label for="conf_pass">Confirm Password:</label>
                    <input type="password" name="conf_pass" id="conf_pass" required>
                </div>
                <div class="button-group">
                    <input class="btn" type="submit" value="Register">
                    <input class="btn" type="reset" value="Clear">
                </div>
                <p><a href="index.php">Return to HomePage</a></p>
            </form>
        </div>
    </body>

</html>



<?php
$page = 'signup';
require_once("functions/function.php");
require_once("functions/settings.php");
function sanitize($data)
{
    $data = trim($data);
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    return $data;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $duplicated = false;
    $date_started = date('Y/m/d');
    $errMsg = "";
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : "";
    $profile_name = isset($_POST['profile_name']) ? sanitize($_POST['profile_name']) : "";
    $password = isset($_POST['password']) ? sanitize($_POST['password']) : "";
    $conf_pass = isset($_POST['conf_pass']) ? sanitize($_POST['conf_pass']) : "";


    //-------check duplicate email-----------//
    if ($email === "") {
        $errMsg .= '<p>Please enter your email</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errMsg .= '<p>Please enter a valid email</p>';
    } else {
        $query = "SELECT * FROM `friends2` WHERE `friend_email` = '$email'";
        $result = mysqli_query($conn, $query);
        if ($result === false) {
            die("Error: " . mysqli_error($conn));
        }
        while ($row = mysqli_fetch_array($result)) {
            if ($row['friend_email'] === $email) {
                $duplicated = true;
                $errMsg .= 'Email exists, please enter your email again';
                break;
            }
        }

    }
    //----check profile name -------//
    $num_of_friends = 0;
    if ($profile_name === '') {
        $errMsg .= '<p>Profile name cannot be empty</p> ';
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $profile_name)) {
        $errMsg .= '<p>Please enter a correct format, it is contain only letters</p> ';
    }
    //------check password -------//
    if ($password === '') {
        $errMsg .= '<p>Please enter password </p>';
    } elseif (!preg_match('/^[A-Za-z]+$/', $password)) {
        $errMsg .= '<p>Password must contain only letters</p> ';
    }

    if ($password !== $conf_pass) {
        $errMsg .= '<p>Password do not match</p>';
    }
    if (empty($errMsg)) {
        $sql = " INSERT INTO `friends2`
        (
            `friend_email` ,
            `password` ,
            `profile_name` ,
            `date_started` ,
            `num_of_friends`
        )
        VALUES
        (
            '$email' ,
            '$password' ,
            '$profile_name' ,
            '$date_started',
            '$num_of_friends'
        )";
        if ($conn->query($sql) === false) {
            die("Error : Insert fail!!!! \n" . mysqli_error($conn));
        } else {
            echo "Notice : Insert success!!!";
        }
        session_start();
        $_SESSION["errMsg"] = $errMsg;
        $_SESSION["email"] = $email;
        $_SESSION["profile_name"] = $profile_name;
        $_SESSION["password"] = $password;

        echo "<table width = '50%' border='1'>";
        echo "<tr>
        <th> Email: </th>
        <th>Profile Name: </th>
        <th>Date Start: </th>

        </tr>";
        echo "<tr>";
        echo "<td>$email</td>";
        echo "<td>$profile_name</td>";
        echo "<td>$date_started</td>";
        echo "</tr>";
        echo "</table>";
    }

    require_once('functions/nav.php');
}
mysqli_close($conn);
?>
</body>

</html>
