<?php
    if (!empty($errMsg)) 
    {
        echo '<div class="alerts">'; 
        echo '<h2><span>Error!!!!!<span></h2>';
        $errors = explode("<br>", $errMsg);
        foreach ($errors as $error) {
            if (!empty($error)) 
            {
                echo '<li>' . $error . '</li>';
            }
        }
        echo '</div>';
        }
?>  