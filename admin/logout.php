<?php

    require_once '../lib/config.php';

    // To logout we only need to set the value of isLogin to false
    $_SESSION['isLogin'] = false;
    $_SESSION['email'] = '';
    $_SESSION['manage_photos'] = false;
    header('Location: ../index.php');
    exit;

?>
