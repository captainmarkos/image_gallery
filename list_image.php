<?php

    // An image belongs to an album and an album belongs to a collection.
    // Finally a collection belongs to a gallery.
    // Get all images for the given album_id, collection_id and gallery_id.

    $gallery_id    = isset($_GET['gallery_id']) && ($_GET['gallery_id'] != '') ? $_GET['gallery_id'] : '';
    $collection_id = isset($_GET['collection_id']) && ($_GET['collection_id'] != '') ? $_GET['collection_id'] : '';
    $album_id      = isset($_GET['album_id']) && ($_GET['album_id'] != '') ? $_GET['album_id'] : '';

    // Ordering by id allows us to display the images in order as they were inserted.
    $sql  = "SELECT galleries.id AS gallery_id, ";
    $sql .= "       collections.id AS collection_id, ";
    $sql .= "       albums.id AS album_id, ";
    $sql .= "       images.id AS image_id, ";
    $sql .= "       images.image AS image, ";
    $sql .= "       images.title AS image_title, ";
    $sql .= "       images.description AS image_desc, ";
    $sql .= "       images.thumbnail AS image_thumbnail ";
    $sql .= "FROM galleries, collections, albums ";
    $sql .= "LEFT JOIN images ON images.album_id=albums.id ";
    $sql .= "WHERE galleries.id=$gallery_id AND collections.id=$collection_id ";
    $sql .= "AND albums.id=$album_id ORDER BY images.id";
    $result = mysql_query($sql) or die('Error, list image failed. ' . mysql_error());

    if((mysql_num_rows($result) == 0) || ($gallery_id == '') || ($collection_id == '') || ($album_id == ''))
    {
	echo "No image in this album yet";
    } 
    else 
    {
        echo "\n<!-- BEGIN list_image -->\n";
        echo '<table width="700" border="0" cellspacing="1" cellpadding="2" align="center">';
        echo "\n";

	// The image is listed in a table.  Here we specify how many columns 
	// we want to show on each row.
	$colsPerRow = 4;
	
	// width of each column in percent
	$colWidth = (int)(100/$colsPerRow);
	$i = 0;
        $overlay_ctr = 0;
	while($row = mysql_fetch_assoc($result)) 
        {
            if(empty($row['image_id']) == true) { print "No Image(s)"; break; }

	    if($i % $colsPerRow == 0) echo "<tr>\n";    // start a new row

            echo '<td valign="top" width="' . $colWidth . '%">' . "\n"; 

            // Using jquery-tools to build the overlay.  Setup the overlay triggers.

            echo '    <a href="view_image.php?image_id=' . $row['image_id'] . 
                 '" rel="#overlay">' . "\n" . '        <img class="trigger_img" src="' . 
                 getImage('glthumbnail', $row['image_thumbnail']) . '" alt="' . 
                 getImage('glimage', $row['image']) . '" ' .
                 'width="100" height="75" id="thumb' . $i . '" /></a>' . "\n        <br />" .
                 $row['image_title'] . "<br />";


            $imageURL = getImageURL($collection_id, $album_id, $row['image_id']);
            $viewcount = get_imageviews($imageURL);
            echo '<span id="vcount' . $i . '">' . $viewcount . "</span> views\n</td>\n\n";

	    if($i % $colsPerRow == $colsPerRow - 1) echo "</tr>\n";
		
	    $i += 1;
            $overlay_ctr += 1;
	}
	
	// print blank columns
	if($i % $colsPerRow != 0) 
        {
	    while($i++ % $colsPerRow != 0) 
            {
		echo '<td width="' . $colWidth . '%">&nbsp;</td>';
                echo "\n";
	    }	
	    echo "</tr>\n";
	}	
	echo "</table>\n";
    }

?>

<div class="overlay_element" id="overlay">

    <div class="contentWrap"></div> <!-- the external content is loaded inside this tag -->

</div>

<script type="text/javascript">                                         

    // When the document is ready do some work.
    $(document).ready(function() 
    {
        // BEGIN: mdb - new
        // Because different users will have different resolutions, check the 
        // height of the browser window so we can set sizes up appropriately.
        if(getWindowHeight() < 750)
        {
            // This browser window is a little small so use smaller settings.
            //alert("getWindowHeight(): " + getWindowHeight());
            $("div.overlay_element").css('background-image', 'url(images/white_small.png)');
            $("div.overlay_element").css('width', '620px');
            $("div.contentWrap").css('height', '485px');
            $("#overlay").css('background-image', 'url(images/not_transparent_small.png)');
        }
        // END: mdb - new

        <?php
            // We need to use some PHP to generate the javascript to bind a click event to the thumbnails.
            for($i = 0; $i < $overlay_ctr; $i++)
            {
                echo '$("#thumb' . $i . '").bind("click", function() { set_imageviews("' . $i . '"); });';
                echo "\n        ";
	    }
	    echo "\n";
        ?>
    });


    function set_imageviews(i)
    {
        var thsel = "#thumb" + i;       // thumbnail img selector
        var vcsel = "#vcount" + i;      // view count span selector

        var image_filename = $(thsel).attr("alt");
        //alert('image_filename = ' + image_filename);

        // When a thumbnail is clicked on update the imageviews.
        $.get("ajax/set_image_count.php", { filename: image_filename }, 
            function(data)
            {
                //alert("data = " + data);
                $(vcsel).text(data);
            });
    }


    // Make all links with the 'rel' attribute open overlays
    $(function() 
    {
        // if the function argument is given to overlay,
        // it is assumed to be the onBeforeLoad event listener
        $("a[rel]").overlay(
        {
            //mask: 'darkred',
            effect: 'apple',           // The effect to be used when an overlay is opened and closed.
            top: 10,                   // Specify how far from the top the overlay is displayed in pixels.
            //speed: 'fast',               // A numerical value (in milliseconds).

            onBeforeLoad: function() 
            {
                // grab wrapper element inside content
                var wrap = this.getOverlay().find(".contentWrap");

                // load the page specified in the trigger
                wrap.load(this.getTrigger().attr("href"));
            }
        });
    });

</script>

<!-- END list_image -->
