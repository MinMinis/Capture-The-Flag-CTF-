<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="style/style.css" rel="stylesheet" type="text/css" />
        <script src="https://kit.fontawesome.com/f30e8be6f4.js" crossorigin="anonymous"></script>
        <title>Assignment 2</title>
    </head>

    <body>

        <?php
        session_start();
        include('functions/function.php');
        include('functions/settings.php');


        if (isset($_SESSION['email']) && isset($_SESSION['friend_id'])) {
            $email = $_SESSION['email'];
            $friend_id = $_SESSION['friend_id'];


        } else {

            header("Location: index.php");
            exit();
        }
        $numoffriends = $_SESSION['num_of_friends'];
        $email = $_SESSION['email'];
        $friend_id = $_SESSION['friend_id'];

        if ($conn) {
            $nmb = "SELECT COUNT(*) AS friendid FROM friends2 ";
            $result_nmb = mysqli_query($conn, $nmb);
            if (mysqli_num_rows($result_nmb) > 0) {
                $row = mysqli_fetch_assoc($result_nmb);
                $total_friendid = $row['friendid'];

                $sub_nof = intval($total_friendid - $numoffriends - 1);
                echo '<div class= "friend-sys">';
                echo "<h2>My Friend system</h2> ";
                echo "<h3>Kangaroo's Friend Add Page </h3> ";
                echo '<p>Total number of friends2 is ' . ((int) $sub_nof) . '</p>';
                echo '</div>';
                echo '<div class = "btn-list" >';
                echo '<a href="friendlist.php">Friend Lists</a>';
                echo '<a href="logout.php">Logout</a>';
                echo '</div>';
                var_dump($sub_nof);
            }
            // $query9 = "SELECT * FROM myfriends2 ";
            // $result9 = mysqli_query($conn, $query9);

            // if($result9)
            // {
            //     while ($row = mysqli_fetch_assoc($result9))
            //     {
            //         $friend_id2 = $row['friend_id2'];
            //     }
            // }


            //------display friend not exist in your list friend -----//
            $query = "SELECT * FROM friends2 ";
            $result = mysqli_query($conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if (isset($_SESSION['profile_name']) && $_SESSION['profile_name'] == $row['profile_name']) {
                        $_SESSION['friend_id'] = $row['friend_id'];
                    }
                }
            }
            //----- get current page -----//
            $current_page = isset($_GET['page']) && $_GET['page'] ? (int) $_GET['page'] : 1;
            if (is_numeric($current_page)) {
                //
                $friend_each_page = 5;
                $page_start = (($current_page - 1) * $friend_each_page);
                $total_page = ceil(($sub_nof) / $friend_each_page);
                // var_dump($current_page);
                // var_dump($page_start);
            }
            $sql = "SELECT friend_id, profile_name FROM friends2
        WHERE friend_id NOT IN (SELECT friend_id2 FROM myfriends2 where friend_id1= '$friend_id')  AND friend_id != '$friend_id' LIMIT $page_start , $friend_each_page";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                echo '<div class="table">';
                echo '<table style="width:30%" border = 1 >';
                echo '<tr>';
                echo '<th>Profile Name</th>';
                echo '<th></th>';
                echo '</tr>';
                while ($row = mysqli_fetch_assoc($result)) {
                    $profile_name = $row['profile_name'];
                    if (isset($profile_name)) {
                        echo "<tr>";
                        echo "<td>" . $profile_name . "</td>";
                        echo '<td>';
                        echo '<form method="POST">';
                        echo '<input type="hidden" name="friend_id" value="' . $friend_id . '">';
                        echo '<input type="hidden" name="friend_id2" value="' . $row['friend_id'] . '">';
                        echo '<input type="submit" class="content-button" name="add_submit" value="Addfriend">';
                        echo '</form>';
                        echo '</td>';
                        echo "</tr>";
                    } else {
                        echo "<tr>";
                        echo "<td>Cannot find profile name you want </td>";
                        echo "</tr>";
                    }
                }
                echo "</table>";
                echo "</div>";
                //----- prev - next (pagination) ---------//
                if ($total_page > 0) {
                    echo '<div class="pagination">';
                    if ($current_page > 1) {
                        echo '<a href="friendadd.php?page=' . ($current_page - 1) . '"><i class="fa-solid fa-angles-right fa-rotate-180"></i> Prev </a>';
                    }
                    if ($current_page < $total_page) {
                        echo '<a href="friendadd.php?page=' . ($current_page + 1) . '">Next <i class="fa-solid fa-angles-right"></i></a>';
                    }
                    echo '</div>';
                }

            }

        }
        //--------add friend into list friend when enter addfriend button----------//
        if (isset($_POST["add_submit"])) {
            $friend_id = $_POST["friend_id"];
            $friend_id2 = $_POST["friend_id2"];

            $check_query = "SELECT * FROM myfriends2 WHERE friend_id1 = '$friend_id' AND friend_id2 = '$friend_id2'";
            $check_result = mysqli_query($conn, $check_query);
            if (mysqli_num_rows($check_result) > 0) {
                echo '<p style="color: rgb(255, 0, 255)"><strong>Notice: </strong> The friendship is already in your friend list. </p>';
            } else {
                $sql3 = "INSERT INTO `myfriends2`(`friend_id1`, `friend_id2`) VALUE ('$friend_id', '$friend_id2')";
                $result3 = mysqli_query($conn, query: $sql3);
                if ($result3 !== false) {
                    $sql = "SELECT profile_name FROM friends2 WHERE friend_id = $friend_id2 ";
                    $result_pr = mysqli_query($conn, query: $sql);
                    $rows = mysqli_fetch_assoc($result_pr);
                    $profile_name1 = $rows['profile_name'];
                    $update_sql = "UPDATE friends2 SET num_of_friends = (SELECT COUNT(*) FROM myfriends2 WHERE friend_id1 = '$friend_id') WHERE friend_id = '$friend_id'";
                    $result4 = mysqli_query($conn, $update_sql);
                    if ($result4 !== false) {
                        echo '<p style="color: rgb(255, 0, 255)"><strong>Notice: </strong> Now ' . $profile_name1 . ' is your friend </p>';
                        $sql6 = "SELECT num_of_friends FROM friends2 WHERE friend_id = '$friend_id'";
                        $result8 = mysqli_query($conn, $sql6);
                        $row1 = mysqli_fetch_assoc($result8);
                        $_SESSION['num_of_friends'] = $row1['num_of_friends'];
                        $numoffriends = $row1['num_of_friends'];
                        // $row1['num_of_friends'] ++;
                    }
                }
            }
        }
        ?>
    </body>

</html>
