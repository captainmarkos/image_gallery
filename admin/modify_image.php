<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    if(!isset($_GET['image_id'])) { echo "Image ID is not defined."; exit; }  // Make sure the image_id is set.

    $album_id = $_GET['album_id'];
    $image_id = $_GET['image_id'];

    if(isset($_POST['txtTitle'])) 
    {
        $collection_id = $_POST['hdnCollection'];
	$album_id      = $_POST['cboAlbum'];
	$image_title   = $_POST['txtTitle'];
	$image_desc    = $_POST['mtxDesc'];

        $image_title = mysql_real_escape_string($image_title);
	$image_desc  = mysql_real_escape_string($image_desc);
        $image_width = $image_height = 0;

        // Make sure the album_id belongs to a collection belonging to the gallery.
        $sql  = "SELECT albums.id AS album_id, ";
        $sql .= "       albums.collection_id AS collection_id, ";
        $sql .= "       collections.gallery_id ";
        $sql .= "FROM albums LEFT JOIN collections ON albums.collection_id=collections.id ";
        $sql .= "WHERE collections.gallery_id=$gallery_id AND collections.id=$collection_id";
        $result = mysql_query($sql) or die('ERROR: album verification failed. ' . mysql_error());
        if(mysql_num_rows($result) < 1)
        {
            print "Something kinda funky is going on to have receieved this error.<br /><br />";
            print "If you believe this is a bug please send us an email and provide details so we can look into it.<br />";
            exit;
        }

        if($_FILES['fleImage']['tmp_name'] != '') 
        {
	    $images = uploadImage('fleImage', IMAGES_IMG_DIR);

            if($images['image'] == '' && $images['thumbnail'] == '' && $images['display_image'] == '') 
            {
		echo "Error uploading file - modify_image failed.";
		return;
            }

            $imgData = getimagesize(IMAGES_IMG_DIR . $images['image']);
            $image_width = $imgData[0];
            $image_height = $imgData[1];

	    $image         = "'" . $images['image'] . "'";
	    $thumbnail     = "'" . $images['thumbnail'] . "'";
	    $display_image = "'" . $images['display_image'] . "'";

	    $sql  = "SELECT image, thumbnail, display_image FROM images WHERE id=$image_id";
	    $result = mysql_query($sql) or die('Error, get image info failed. ' . mysql_error());
	    $row = mysql_fetch_assoc($result);
	    unlink(IMAGES_IMG_DIR . $row['image']);
	    unlink(IMAGES_IMG_THUMBS_DIR . $row['thumbnail']);
	    unlink(IMAGES_IMG_DISPLAY_DIR . $row['display_image']);
	} 
        else 
        {
	    // the old image is not replaced
	    $image         = "image";
	    $thumbnail     = "thumbnail";
            $display_image = "display_image";
	}

        $sql  = "UPDATE images SET album_id=$album_id, title='$image_title', ";
        $sql .= "description='$image_desc', image=$image, thumbnail=$thumbnail, ";
        $sql .= "display_image=$display_image, image_width=$image_width, image_height=$image_height ";
        $sql .= "WHERE id=$image_id";
	mysql_query($sql) or die('ERROR: update image failed : ' . mysql_error());

	echo "<script>window.location.href='index.php?page=list_image&album_id=$album_id';</script>";
        exit;
    } 
    else 
    {
        $sql  = "SELECT images.id AS image_id, ";
        $sql .= "       images.title AS image_title, ";
        $sql .= "       images.thumbnail AS image_thumbnail, ";
        $sql .= "       images.description AS image_description, ";
        $sql .= "       images.album_id AS album_id, ";
        $sql .= "       collections.id AS collection_id, ";
        $sql .= "       collections.name AS collection_name ";
        $sql .= "FROM collections, images ";
        $sql .= "LEFT JOIN albums ON images.album_id=albums.id ";
        $sql .= "WHERE collections.gallery_id=$gallery_id AND albums.collection_id=collections.id ";
        $sql .= "AND images.id=$image_id";
	$result = mysql_query($sql) or die('Error, get image info failed. ' . mysql_error());
        $row = mysql_fetch_assoc($result);
	if(mysql_num_rows($result) == 0) 
        {
            print "<p align=\"center\">Image not found. ";
            print "Click <a href=\"index.php?page=list_image\">here</a> to go to the image list.</p>\n";
            return;
	}

	// Construct the album list.
        $sql  = "SELECT albums.id AS album_id, albums.name AS album_name ";
        $sql .= "FROM albums LEFT JOIN collections ON albums.collection_id=collections.id ";
        $sql .= "WHERE collections.gallery_id=$gallery_id ";
        if($collection_id != '')
        {
            $sql .= "AND albums.collection_id=$collection_id ";
        }
        $sql .= "ORDER BY albums.name";
        $res2 = mysql_query($sql) or die('SQL: ' . $sql . '<br />ERROR: get album list failed : ' . mysql_error());
        $albumList = '';
        while($row2 = mysql_fetch_assoc($res2)) 
        {
	    $albumList .= "\n" . '<option value="' . $row2['album_id']. '"';
   	    if($row2['album_id'] == $album_id) { $albumList .= ' selected'; }
	    $albumList .= '>' . $row2['album_name'] . '</option>';	
        }
        if($albumList == '') { $albumList = '<option value="">No Albums For This Collection</option>'; }

?>
<!-- BEGIN: modify_image -->

<form action="" method="post" enctype="multipart/form-data" name="frmAlbum" id="frmAlbum">
<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Modify Image</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">

    <tr> 
        <th width="150">Album</th>
        <td align="left">
            <select name="cboAlbum" id="cboAlbum" class="colselect">
                <?php echo $albumList; ?> 
            </select>
        </td>
    </tr>

    <tr> 
        <th width="150">Image Title</th>
        <td align="left">
            <input name="txtTitle" type="text" id="txtTitle" size="24" maxlength="20" value="<?php echo $row['image_title']; ?>" />
        </td>
    </tr>

    <tr> 
        <th width="150">Description</th>
        <td align="left">
            <textarea name="mtxDesc" cols="50" rows="4" id="mtxDesc"><?php echo htmlspecialchars($row['image_description']); ?></textarea>
        </td>
    </tr>

    <tr> 
        <th width="150">Image</th>
        <td align="left">
              <img src="<?php echo getImage('glthumbnail', $row['image_thumbnail']); ?>" width="100" border="0" /><br />
              <input name="fleImage" type="file" class="box" id="fleImage2" style="margin-top: 2px;" />
        </td>
    </tr>

    <tr> 
        <td width="150">&nbsp;</td>
        <td align="left">
            <?php
                $cancel_onclick = "window.location.href='index.php?page=list_image&album_id=$album_id';";
            ?>
            <input name="btnModify" type="submit" class="box" id="btnModify" value="Save" /> 
            <input name="btnCancel" type="button" id="btnCancel" value="Cancel" onclick="<?php echo $cancel_onclick; ?>" />
        </td>
    </tr>
</table>
<input name="hdnCollection" type="hidden" value="<?php echo $row['collection_id']; ?>" />
</form>

<?php

    }
?>

<br />

<?php include 'hints_images.html'; ?>

<!-- END: modify_image -->

