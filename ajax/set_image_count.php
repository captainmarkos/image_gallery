<?php

require_once('../lib/config.php');
require_once('../lib/image_gallery.php');

$image_gallery = new ImageGallery($dbconn);
$count = 0;

$filename = isset($_REQUEST['filename']) ? $_REQUEST['filename'] : '';

if($filename) { $count = $image_gallery->set_imageviews($filename); }

print $count;

?>
