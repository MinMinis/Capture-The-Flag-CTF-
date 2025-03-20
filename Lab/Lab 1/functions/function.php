<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Assignment2</title>
    </head>

    <body>
        <?php
        require_once("settings.php");
        function check_exist_table($conn)
        {

            $check = "SHOW TABLES LIKE 'myfriends2'";
            $check_result = mysqli_query($conn, $check);
            if ($check_result === FALSE || mysqli_num_rows($check_result) == 0) {
                create_table($conn);
                return false;
            } else {
                echo '<div class ="warn">';
                echo "<p><strong>Warning: </strong>Table already exist<p>";
                echo '</div>';
                return true;
            }

        }
        function create_table($conn)
        {
            $date_started = date('Y/m/d');
            $table_success = true;
            require_once("settings.php");
            if ($conn) {
                $sql = "CREATE TABLE IF NOT EXISTS `friends2` (
                `friend_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `friend_email` VARCHAR(50) NOT NULL,
                `password` VARCHAR(20) NOT NULL,
                `profile_name` VARCHAR(30) NOT NULL,
                `date_started` DATE NOT NULL,
                `num_of_friends` INT(10) UNSIGNED NOT NULL
            )";
                if ($conn->query($sql) === false) {
                    $table_success = false;
                    die("Error creating table: " . mysqli_error($conn));
                }

                $sql = "CREATE TABLE IF NOT EXISTS `myfriends2` (
                `friend_id1` INT(10) UNSIGNED NOT NULL ,
                `friend_id2` INT(10) UNSIGNED NOT NULL,
                CONSTRAINT both_friend PRIMARY KEY (friend_id1, friend_id2),
                CONSTRAINT conn_friend_id1 FOREIGN KEY (friend_id1) REFERENCES `friends2`(friend_id),
                CONSTRAINT conn_friend_id2 FOREIGN KEY (friend_id2) REFERENCES `friends2`(friend_id),
                CONSTRAINT diff_id CHECK (friend_id1 <> friend_id2)
            )";
                if ($conn->query($sql) === false) {
                    $table_success = false;
                    die("Error creating table: " . mysqli_error($conn));
                }
                if ($table_success) {
                    echo "<p><strong>Note: </strong>Tables successfully created and populated.</p>";
                }
                for ($i = 0; $i < 20; $i++) {
                    $email = create_friend_email();
                    $password = create_pass();
                    $profile_name = profile_name();

                    $sql = " INSERT INTO `friends2`
                (
                    `friend_email` ,
                    `password` ,
                    `profile_name` ,
                    `date_started`,
                    `num_of_friends`
                )
                VALUES
                (
                    '$email' ,
                    '$password' ,
                    '$profile_name' ,
                    '$date_started',
                    0
                )";
                    if ($conn->query($sql) === false) {
                        echo "Error inserting data: " . mysqli_error($conn);
                    }
                }

                my_friend_id($conn);
                number_of_friends($conn);
            }
        }
        ?>

        <?php
        //---------create profile name to insert into the 'friends2' table-------//
        function profile_name()
        {
            $firstname = ['thang', 'tuan', 'tien', 'bao', 'thinh', 'huy', 'trung', 'minh', 'khang', 'nam', 'xuan', 'uyen', 'hien', 'tung', 'tho', 'kha', 'hung', 'nam', 'dat', 'nhu', 'khanh', 'cuong', 'long', 'do', 'manh'];
            $lastname = ['quoc', 'anh', 'vi', 'gia', 'tuong', 'viet', 'dat', 'i', 'ngo', 'huu', 'van', 'thuc', 'thanh', 'do', 'nhat', 'tien', 'dai', 'ngoc', 'la', 'kieu', 'duy', 'tran', 'thanh', 'duy'];
            $firstname1 = $firstname[array_rand($firstname)];
            $lastname1 = $lastname[array_rand($lastname)];
            $fullname = $lastname1 . " " . $firstname1;
            return $fullname;
        }

        //---------create email to insert into the 'friends2' table-------//

        function create_friend_email()
        {
            $email_syntax = ['gmail', 'yahoo'];
            $profile_name = profile_name();
            $email_syntax1 = $email_syntax[array_rand($email_syntax)];
            $name_of_email = explode(' ', $profile_name);

            $character = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'o', 's', 'y', 'n', 'z', 'th', 't', 'u', 'v', 'w', 'x', 'tr'];
            $letter = $character[array_rand($character)];
            $number = range(0, 20);
            $random_number = array_rand($number, 2);
            $random_number = implode($random_number);
            return strtolower($letter . $name_of_email[1] . $random_number . "@" . $email_syntax1 . '.com');
        }



        //---------create pass to insert into the 'friends2' table-------//
        function create_pass()
        {
            $pass = '';
            $characters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'o', 's', 'y', 'n', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];
            for ($i = 0; $i < 8; $i++) {
                $random_index = array_rand($characters);
                $pass .= $characters[$random_index];
            }
            return $pass;
        }

        //--------insert friend id to myfriends2 table-------//
        function my_friend_id($conn)
        {
            $i = 1;
            for (; $i <= 50; $i++) {
                $friend_id1 = rand(1, 20);
                $friend_id2 = rand(1, 20);
                if ($friend_id1 == $friend_id2) {
                    $i--;
                    continue;
                } else {
                    //---check duplicate friend_id 1 and 2 in my friend table --------//
                    $query = "SELECT 1 FROM `myfriends2` WHERE friend_id1 = $friend_id1 AND friend_id2 = $friend_id2";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        $i--;
                        continue;
                    }

                    $sql = "INSERT INTO `myfriends2` (`friend_id1`, `friend_id2`) VALUES ($friend_id1, $friend_id2)";
                    if ($conn->query($sql) === false) {
                        die("Insert fail \n" . mysqli_error($conn));
                    }
                }
            }
        }

        //--------update number_of_friends column in 'friends2' table-------//
        function number_of_friends($conn)
        {
            $sql1 = "SELECT friend_id FROM friends2 ";
            $result1 = mysqli_query($conn, $sql1);
            if ($result1) {
                while ($row1 = $result1->fetch_assoc()) {
                    $friend_id = $row1['friend_id'];
                    $query = "SELECT COUNT(*) AS number0f_friend FROM `myfriends2` WHERE `friend_id1` = '$friend_id'";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $number0f_friend = $row['number0f_friend'];
                            $sql = "UPDATE `friends2` SET `num_of_friends` = '$number0f_friend'  WHERE `friend_id` = '$friend_id' ";
                            mysqli_query($conn, $sql);
                        }
                    }
                }
            }

        }

        ?>

    </body>

</html>
