<?php

    // This script displays the actual image and is called from the overlay triggers
    // in list_image.php.  It needs the images.id to find the image in the table.
    //
    // We would like to display the image as big as possible however each user is going
    // to have different display resolutions and therefore we dont want to display the
    // image to big on a small resolution.
    //
    // What we will do is find the size of the browser window (viewport) and this will
    // will help us to display the image in a more appropriate size.  This of course is
    // not a fix-all method but I think in most cases it will be suitable.
    //
    include 'lib/config.php';
    include 'lib/functions.php';

    if(!isset($_GET['image_id']) || $_GET['image_id'] == '') { exit; }

    $image_id = $_GET['image_id'];

    $sql  = "SELECT id, album_id, title, image, image_width, image_height, display_image, ";
    $sql .= "description FROM images WHERE id=$image_id";
    $result = mysql_query($sql) or die('ERROR: view_image.php failed. ' . mysql_error());
    if(mysql_num_rows($result) == 0) { die('ERROR: view_image.php - No image found. '); }
    $row = mysql_fetch_assoc($result);

    $actual_image = WWWROOT_IMAGES_IMG_DIR . $row['image'];
    $display_image = WWWROOT_IMAGES_IMG_DISPLAY_DIR . $row['display_image'];
    $desc = $row['description'];
    $desc = preg_replace("/\r/", "", htmlentities($desc, ENT_QUOTES));
    $desc = preg_replace("/\n/", "<br />", $desc);
    $title = $row['title'];
    $album_id = $row['album_id'];

    $image_width = $row['image_width'];
    $image_height = $row['image_height'];

    $base_url = "http://" . $_SERVER['SERVER_NAME'];
    $direct_url = $base_url . WWWROOT_IMAGES_IMG_DIR . $row['image'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="gallery.css" />
<script type="text/javascript">

// Writing code to write code!

<?php

    // ----------------------------------------------------------------------------------------
    // Build an assoc. array of assoc. arrays.  First key is the images.id.  Output javascript.
    // ----------------------------------------------------------------------------------------
    $sql  = "SELECT id, album_id, image, image_width, image_height, display_image, ";
    $sql .= "title, description FROM images WHERE album_id=$album_id ORDER BY id";
    $result = mysql_query($sql) or die('ERROR: view_image.php failed. ' . mysql_error());
    if((mysql_num_rows($result) == 0) || ($album_id == ''))
    {
	return;
    }
    $el = 0;
    print "var curr_idx = 0;\n";
    print "var asar = new Array();\n";
    while($row = mysql_fetch_assoc($result))
    {
        if($image_id == $row['id']) { print "curr_idx = " . $el . ";\n"; }

        $tmp_desc = preg_replace("/\r/", "", htmlentities($row['description'], ENT_QUOTES));
        $tmp_desc = preg_replace("/\n/", "<br />", $tmp_desc);

        print "asar['" . $el . "'] = new Array();\n";
        print "asar['" . $el . "']['id'] = \"" . $row['id'] . "\";\n"; 
        print "asar['" . $el . "']['image'] = \"" . WWWROOT_IMAGES_IMG_DIR . $row['image'] . "\";\n"; 
        print "asar['" . $el . "']['image_width'] = \"" . $row['image_width'] . "\";\n";
        print "asar['" . $el . "']['image_height'] = \"" . $row['image_height'] . "\";\n";
        print "asar['" . $el . "']['display_image'] = \"" . WWWROOT_IMAGES_IMG_DISPLAY_DIR . $row['display_image'] . "\";\n";
        print "asar['" . $el . "']['title'] = \"" . $row['title'] . "\";\n"; 
        print "asar['" . $el . "']['description'] = \"" . $tmp_desc . "\";\n\n";
        $el++;
    }

?>

if(curr_idx == 0) { $('#prev_link').css('display', 'none'); }

if(curr_idx == asar.length -1) { $('#next_link').css('display', 'none'); }

function next_img(idx)
{
    // Get the next element in asar if possible and display that image info.

    var index = idx +1;
    if(index < asar.length)
    {
        $('#viewimg').attr('src', asar[index]['display_image']);
        $('#viewimg').load(function() 
        {
            $('#spinner').css('display', 'none');  // when image load is complete turn off
        });

        $('#prev_link').css('display', 'inline');
        $('#spinner').css('display', 'inline');
        $('#title').html(asar[index]['title']);
        $('#image_size').html(asar[index]['image_width'] + ' x ' + asar[index]['image_height']);
        $('#actual_image').attr('href', asar[index]['image']);
        $('#description').html(asar[index]['description']);
        curr_idx = index;

        // When a new image is displayed update the imageviews.
        $.get("ajax/set_image_count.php", { filename: asar[index]['image'] }, 
            function(data)
            {
                // This is where we would do something with the ajax script output.
                //alert("data = " + data);
                //$(vcsel).text(data);  // maybe later we will display the view count on this page
            });
    }

    if((index +1) >= asar.length) { $('#next_link').css('display', 'none'); }
}

function prev_img(idx)
{
    // Get the previous element in asar if possible and display that image info.

    var index = curr_idx -1;
    if(index >= 0)
    {
        $('#viewimg').attr('src', asar[index]['display_image']);
        $('#spinner').css('display', 'inline');
        $('#viewimg').load(function() 
        {
            $('#spinner').css('display', 'none');  // when image load is complete turn off
        });

        $('#next_link').css('display', 'inline');
        $('#title').html(asar[index]['title']);
        $('#image_size').html(asar[index]['image_width'] + ' x ' + asar[index]['image_height']);
        $('#actual_image').attr('href', asar[index]['image']);
        $('#description').html(asar[index]['description']);
        curr_idx = index;

        // When a new image is displayed update the imageviews.
        $.get("ajax/set_image_count.php", { filename: asar[index]['image'] }, 
            function(data)
            {
                // This is where we would do something with the ajax script output.
                //alert("data = " + data);
                //$(vcsel).text(data);  // maybe later we will display the view count on this page
            });
    }

    if((index -1) < 0) { $('#prev_link').css('display', 'none'); }
}

function setImgSize()
{
    var bwidth  = 0;
    var bheight = 0;

    if(document.body && document.body.offsetWidth) 
    {
        bwidth = document.body.offsetWidth;
        bheight = document.body.offsetHeight;
    }

    if(document.compatMode=='CSS1Compat' &&
       document.documentElement &&
       document.documentElement.offsetWidth) 
    {
        bwidth = document.documentElement.offsetWidth;
        bheight = document.documentElement.offsetHeight;
    }

    if(window.innerWidth && window.innerHeight) 
    {
        bwidth = window.innerWidth;
        bheight = window.innerHeight;
    }

    //alert("browser size:" + bwidth + " x " + bheight);

    // Because users will have different resolutions we set the width 
    // appropriately which will of course adjust the height.

    var viewimg = document.getElementById("viewimg");

    if(bheight > 750) { viewimg.style.width = '800px'; }
    else              { viewimg.style.width = '600px'; }
}

</script>
</head>
<body>

<script type="text/javascript"> setImgSize(); </script>

<img src="<?php echo $display_image; ?>" id="viewimg" style="min-height: 450px;" border="0" alt="<?php echo $title; ?>" />
<br />

<div class="overlay_imgtxt">

    Title: <span id="title"><?php echo $title; ?></span>

    <div style="float: right; position: relative;">
        <img id="spinner" style="display: none;" src="images/animated_progress.gif" width="24" height="24" />

        <span id="prev_link" class="prevImg" onclick="prev_img(curr_idx);"><img src="images/prev.png" border="0" height="24" width="72" /></span>

        <span id="next_link" class="nextImg" onclick="next_img(curr_idx);"><img src="images/next.png" border="0" height="24" width="39" /></span>
    </div>

    <br />

    Image Size: <span id="image_size"><?php echo "$image_width x $image_height"; ?></span>
    &nbsp;&nbsp;
    <a id="actual_image" class="iglink3" target="_blank" href="<?php echo $direct_url; ?>">Actual Image</a>

    <br />
    <br />

    <span id="description"><?php print $desc; ?></span>

    <br />

</div>

</body>
</html>

