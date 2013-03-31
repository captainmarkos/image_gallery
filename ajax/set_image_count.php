<?php

    require_once '../lib/config.php';
    require_once '../lib/functions.php';

    $count = 0;

    $filename = (isset($_GET['filename']) && $_GET['filename'] != '') ? $_GET['filename'] : '';

    if($filename != '') { $count = set_imageviews($filename); }

    print $count;

?>
