<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="style/style.css" rel="stylesheet" type="text/css" />
        <title>Assignment 2</title>
    </head>

    <body>
        <?php
        session_start();
        include('functions/function.php');
        include('functions/settings.php');


        if (!isset($_SESSION['login'])) {
            $_SESSION['num_of_friends'] = 0;
            header("Location:index.php");
            exit();
        }
        //$numoffriends = $_SESSION['num_of_friends'];
        if (isset($_SESSION['num_of_friends'])) {
            $numoffriends = $_SESSION['num_of_friends'];
        } else {
            $numoffriends = 0;
        }
        $email = $_SESSION['email'];
        $friend_id = $_SESSION['friend_id'];
        echo '<div class= "friend-sys">';
        echo "<h2>My Friend System </h2> ";
        echo "<h3>Kangaroo's Friend List Page </h3> ";
        echo '<p>Total number of friends2 is ' . $numoffriends . '</p>';
        echo '</div>';
        echo '<div class = "btn-list" >';
        echo '<a href="friendadd.php">Add Friends</a>';
        echo '<a href="logout.php">Logout</a>';
        echo '</div>';


        $sql = "SELECT * FROM `myfriends2` WHERE `friend_id1` = '$friend_id'";
        $query = mysqli_query($conn, $sql);
        if (mysqli_num_rows($query) > 0) {
            echo '<div class="table">';
            echo '<table style="width:30%" border = 1 >';
            echo '<tr>';
            echo '<th>Profile Name</th>';
            echo '<th></th>';
            echo '</td>';
            while ($row = mysqli_fetch_assoc($query)) {
                $_SESSION['friend_id2'] = $row['friend_id2'];
                $friend_id2 = $_SESSION['friend_id2'];
                $sql1 = "SELECT * FROM friends2 WHERE friend_id = '$friend_id2' ";
                $query1 = mysqli_query($conn, $sql1);
                if (mysqli_num_rows($query1) > 0) {
                    while ($row1 = mysqli_fetch_assoc($query1)) {
                        $profile_name = $row1['profile_name'];
                        if (isset($profile_name)) {
                            echo "<tr>";
                            echo "<td>" . $profile_name . "</td>";
                            echo '<td>';
                            echo '<form method="POST">';
                            echo '<input type="hidden" name="friend_id" value="' . $friend_id . '">';
                            echo '<input type="hidden" name="friend_id2" value="' . $friend_id2 . '">';
                            echo '<input type="submit" class="content-button" name="delete_submit" value="Unfriend">';
                            echo '</form>';
                            echo '</td>';
                            echo "</tr>";

                        } else {
                            echo "<tr>";
                            echo "<td>Cannot find profile name you want </td>";
                            echo "</tr>";
                        }
                    }
                }
            }
            echo "</table>";
            echo "</div>";

        }

        //-------- remove friend from list when press unfriend button ---------------------//
        if (isset($_POST["delete_submit"])) {
            $friend_id = $_SESSION['friend_id'];
            $friend_id2 = $_POST["friend_id2"];
            $sql3 = "DELETE FROM `myfriends2` WHERE `friend_id1` = '$friend_id' AND `friend_id2` = '$friend_id2'";
            $result3 = mysqli_query($conn, $sql3);
            if ($result3 !== false) {
                $sql = "SELECT profile_name FROM friends2 WHERE friend_id = $friend_id2 ";
                $result_pr = mysqli_query($conn, $sql);
                $rows = mysqli_fetch_assoc($result_pr);
                $profile_name1 = $rows['profile_name'];
                $update_sql = "UPDATE `friends2` SET `num_of_friends` = (SELECT COUNT(*) FROM `myfriends2` WHERE `friend_id1` = '$friend_id') WHERE `friend_id` = '$friend_id'";
                $result4 = mysqli_query($conn, $update_sql);
                if ($result4 !== false) {
                    echo '<p style="color: rgb(255, 0, 255)"><strong>Notice: </strong> Now ' . $profile_name1 . ' is not your friend </p>';
                    $sql6 = "SELECT * FROM `friends2` WHERE `friend_id` = '$friend_id'";
                    $result8 = mysqli_query($conn, $sql6);
                    $row1 = mysqli_fetch_assoc($result8);
                    $_SESSION['num_of_friends'] = $row1['num_of_friends'];
                    $numoffriends = $row1['num_of_friends'];
                } else {
                    echo '<p style="color: rgb(255, 0, 255)"><strong>Notice: </strong> This is no longer your friend </p>';
                }
            }
        }



        ?>

    </body>

</html>
