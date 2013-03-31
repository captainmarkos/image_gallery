<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];
    $album_id   = isset($_GET['album_id']) ? $_GET['album_id'] : '';
    $pageNumber = isset($_GET['pageNum']) ? $_GET['pageNum'] : 1;

    // Make sure the album_id belongs to a collection belonging to the gallery.
    $sql  = "SELECT albums.id AS album_id, ";
    $sql .= "       albums.collection_id AS collection_id, ";
    $sql .= "       collections.gallery_id ";
    $sql .= "FROM albums LEFT JOIN collections ON albums.collection_id=collections.id ";
    $sql .= "WHERE collections.gallery_id=$gallery_id";
    $result = mysql_query($sql) or die('ERROR: album verification failed. ' . mysql_error());
    if(mysql_num_rows($result) < 1)
    {
        if($album_id != '')
            { print "Album verification failed.<br /><br />The album does not exist or does not belong to this gallery.<br />"; }
        else
            { print "No images in this gallery yet.<br /><br />"; include 'hints_images.html'; }
        return; //exit;
    }

    if(isset($_GET['delete']) && isset($_GET['album_id']) && isset($_GET['image_id'])) 
    {
	// Get the image file name so we can delete it from the server.
        $sql  = "SELECT image, thumbnail, display_image FROM images WHERE id=" . $_GET['image_id'];
	$sql .= " AND album_id=" . $_GET['album_id'];
	$result = mysql_query($sql) or die('ERROR: finding image failed. ' . mysql_error());
	if(mysql_num_rows($result) == 1)
        {
	    $row = mysql_fetch_assoc($result);
		
	    // remove the image and the thumbnail from the server
	    unlink(IMAGES_IMG_DIR . $row['image']);
	    unlink(IMAGES_IMG_THUMBS_DIR . $row['thumbnail']);
	    unlink(IMAGES_IMG_DISPLAY_DIR . $row['display_image']);
			
	    // and then remove the database entry
	    $sql  = "DELETE FROM images WHERE id = {$_GET['image_id']} ";
            $sql .= "AND album_id = {$_GET['album_id']}";
	    mysql_query($sql) or die('SQL: ' . $sql . '<br />ERROR: delete image failed. ' . mysql_error());		
	}	
    }

    $imagesPerPage = 10;
    $offset = ($pageNumber - 1) * $imagesPerPage;
    $imgCounter = $offset + 1;

    // Get album list for album select list.
    $sql  = "SELECT albums.id AS album_id, albums.name AS album_name ";
    $sql .= "FROM albums LEFT JOIN collections ON albums.collection_id=collections.id ";
    $sql .= "WHERE collections.gallery_id=$gallery_id ORDER BY albums.name";
    $result = mysql_query($sql) or die('SQL: ' . $sql . '<br />ERROR: get album list failed : ' . mysql_error());

    $albumList = '';
    while($row = mysql_fetch_assoc($result)) 
    {
	$albumList .= '<option value="' . $row['album_id'] . '"' ;
	
	if($row['album_id'] == $album_id) { $albumList .= ' selected="selected"'; }
	
	$albumList .= '>' . $row['album_name'] . '</option>' . "\n";	
    }

?>

<!-- BEGIN: list_image -->

<center>

<table width="100%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Image Listing</b></td>
        <td align="right" class="igadmin_font1">Show Album:&nbsp;
            <select name="cboAlbum" id="cboAlbum" class="myselect" onchange="viewImage(this.value);">
                <option value="">-- All Albums --</option>
                <?php echo $albumList; ?> 
            </select>
        </td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th align="center">&#35;</th>
        <th align="center">Collection</th>
        <th align="center">Album</th>
        <th align="center">Image</th>
        <th width="80" align="center">Date</th>
        <th width="50" align="center">&nbsp;</th>
        <th width="50" align="center">&nbsp;</th>
    </tr>

<?php

    // Ordering by id allows us to display the images in order as they were inserted.
    $sql  = "SELECT images.id AS image_id, ";
    $sql .= "       images.title AS image_title, ";
    $sql .= "       images.thumbnail AS image_thumbnail, ";
    $sql .= "       DATE_FORMAT(images.timestamp, '%Y-%m-%d') AS image_date, ";
    $sql .= "       images.album_id AS album_id, ";
    $sql .= "       albums.name AS album_name, ";
    $sql .= "       collections.name AS collection_name ";
    $sql .= "FROM collections, images LEFT JOIN albums ON images.album_id=albums.id ";
    $sql .= "WHERE albums.collection_id=collections.id ";
    if($album_id != '') 
    {
	$sql .= "AND images.album_id=$album_id ";
    }
    $sql .= "ORDER BY images.id LIMIT $offset, $imagesPerPage";
    $result = mysql_query($sql) or die('SQL: ' . $sql . '<br />ERROR: list image failed. ' . mysql_error());
    if(mysql_num_rows($result) < 1)
    {
        print '<tr><td colspan="7" align="center"><br />No images yet.<br /><br /></td></tr></table></center><br />';
        include 'hints_images.html';
        return; //exit;
    }

    while($row = mysql_fetch_assoc($result)) 
    {
?>
    <tr valign="middle" bgcolor="#ffffff"> 
        <td width="50" align="center"><?php echo $imgCounter++; ?></td>

        <td align="center" class="igadmin_font1"><?php echo $row['collection_name']; ?></td>

        <td align="center" class="igadmin_font1"><?php echo $row['album_name']; ?></td>

        <td align="center" class="igadmin_font1">
            <a href="?page=image_detail&album_id=<?php echo $row['album_id']; ?>&image_id=<?php echo $row['image_id']; ?>"><img src="<?php echo getImage('glthumbnail', $row['image_thumbnail']); ?>" width="100" border="1" /></a><br />
            <?php echo $row['image_title']; ?>
        </td>
        <td width="80" align="center" class="igadmin_font1"><?php echo $row['image_date']; ?></td>
        <td width="50" align="center"><a href="?page=modify_image&album_id=<?php echo $row['album_id']; ?>&image_id=<?php echo $row['image_id']; ?>">Modify</a></td>
        <td width="50" align="center"><a href="javascript:deleteImage(<?php echo "'" . $row['album_id'] ."', '" . $row['image_id'] . "'"; ?>);">Delete</a></td>
    </tr>

<?php

    }

    $sql  = "SELECT images.id FROM images LEFT JOIN albums ON images.album_id=albums.id ";
    if($album_id != '') { $sql .= "WHERE images.album_id = $album_id "; }
    $result = mysql_query($sql) or die('SQL: ' . $sql . mysql_error());
    $totalResults = mysql_num_rows($result);
    $pagingLink = getPagingLink($totalResults, $pageNumber, $imagesPerPage, "page=list_image&album_id=$album_id");

?>
    <tr valign="middle" bgcolor="#ffffff" style="height: 30px;">
        <td colspan="7">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="left"><?php print $totalResults; ?> Total Images</td>
                <td align="right"><?php print $pagingLink ?>&nbsp;</td>
            </tr>
            </table>
        </td>
    </tr>

    <tr>
        <?php
            if($album_id == '')
                { $onclicktxt = "window.location.href='index.php?page=add_image';"; }
            else
                { $onclicktxt = "window.location.href='index.php?page=add_image&album_id=$album_id';"; }
        ?>
        <td colspan="7" align="right"><input type="button" name="btnAdd" value="Add Image" onclick="<?php print $onclicktxt; ?>" /></td>
    </tr>
</table>
</center>

<br />

<?php if($totalResults < 3) { include 'hints_images.html'; } ?>

<!-- END: list_image -->
