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
        $page = 'about';
        include('functions/header.php');
        
    ?>
    <div class="about">
            <h2>What tasks you have not attempted or not completed?</h2>
            <p><i>I am not copleted Task 9 - Enable mutual friend count in Add Friend Page . </i><p>
            <br />
            <h2>What special features have you done, or attempted, in creating the site that we should know about? </h2>
            <ul>
                <li>Separate header.php and footer.php for common site use.</li>
                <li>Using $errMsg to contain and manage error. </li>
                <li>Change button position on page change</li>
            </ul>
            <br />
            <h2>Which parts did you have trouble with?</h2>
            <ul>
            <li>In my post I had trouble switching from friendlist.php to friendadd.php page. The problem I have is that when I click the unfriend button in friendlist.php it will save the id as the new id which is the id of the profile name I just unfriended so when I switch to friendadd.php page it will use that id so I will error in printing the profile name of the email I logged in And I also have problem in task 5 when I use SQL query to print profile names that are not friends with current user. Maybe I have fixed this bug </li>
            </ul>
            <h2>What would you like to do better next time? </h2>
            <ul>
            <li>I want to do my code better next time especially task 5, I still feel unsatisfied with my code</li>
            </ul>
            <h3>Screenshot of a discussion </h3>
            <img src="images/dissc.png" width="50%%">

            <!-- <img src="image/screenshot.png" style=“width:400px;height:300px;” > -->
            <div class = "about2">
           
            <a  href=friendlist.php> Friend List</a>
            <a  href=friendadd.php> Adds friend</a>
            <a  href=index.php> Home Page</a>
            </div>
    </div>
    <?php 
        // include_once ('include/footer.php');
    ?>
</body>
</html>