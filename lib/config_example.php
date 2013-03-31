<?php

    session_start();

    // db properties
    $dbhost = 'localhost';
    $dbuser = 'dbuser';
    $dbpass = 'password';
    $dbname = 'test';


    // A gallery can have an image used as a thumbnail.  We save the collection images here.
    define('GALLERY_IMG_DIR', '/var/chroot/home/content/81/9389481/html/image_gallery/images/gallery/');
    define('WWWROOT_GALLERY_IMG_DIR', '/image_gallery/images/gallery/');

    // A collection can have an image used as a thumbnail.  We save the collection images here.
    define('COLLECTION_IMG_DIR', '/var/chroot/home/content/81/9389481/html/image_gallery/images/collection/');
    define('WWWROOT_COLLECTION_IMG_DIR', '/image_gallery/images/collection/');

    // An album can have an image used as a thumbnail we save the album images here.
    define('ALBUM_IMG_DIR', '/var/chroot/home/content/81/9389481/html/image_gallery/images/album/');
    define('WWWROOT_ALBUM_IMG_DIR', '/image_gallery/images/album/');

    // All images inside an album are stored here.
    define('IMAGES_IMG_DIR', '/var/chroot/home/content/81/9389481/html/image_gallery/images/images/');
    define('WWWROOT_IMAGES_IMG_DIR', '/image_gallery/images/images/');

    // All display images inside an album are stored here.  Display images are those
    // that are used for display and are smaller so they load quicker.
    define('IMAGES_IMG_DISPLAY_DIR', '/var/chroot/home/content/81/9389481/html/image_gallery/images/images/display_images/');
    define('WWWROOT_IMAGES_IMG_DISPLAY_DIR', '/image_gallery/images/images/display_images/');

    // All image thumbnails are stored here.
    define('IMAGES_IMG_THUMBS_DIR', '/var/chroot/home/content/81/9389481/html/image_gallery/images/images/thumbnails/');
    define('WWWROOT_IMAGES_IMG_THUMBS_DIR', '/image_gallery/images/images/thumbnails/');

    // When we upload an image we dont want a huge image (such as 3264 x 2448) so 
    // we will resize it.
    define('IMAGE_WIDTH', 1200);  // 1200 x 900

    // When we upload an image the thumbnail is created on the fly.  Here we set 
    // the thumbnail width in pixel. The height will be adjusted proportionally.
    define('THUMBNAIL_WIDTH', 100);

    // The display image size.
    define('DISPLAY_IMAGE_WIDTH', 800);  // 800 x 600

    // Make a connection to mysql here.
    $conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ("unable to connect to the database: " . mysql_error());
    mysql_select_db($dbname) or die ("unable to select the database '$dbname': " . mysql_error());

?>
