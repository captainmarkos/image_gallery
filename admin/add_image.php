<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query should have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];
    $album_id = isset($_GET['album_id']) ? $_GET['album_id'] : '';
    $collection_id = isset($_GET['collection_id']) ? $_GET['collection_id'] : '';

    if(isset($_POST['txtTitle']))
    {
	$collection_id = $_POST['cboCollection'];
	$album_id      = $_POST['cboAlbum'];
	$image_title   = $_POST['txtTitle'];
	$image_desc    = $_POST['mtxDesc'];

        // Make sure the album_id belongs to a collection belonging to the gallery.
        $sql  = "SELECT albums.id AS album_id, ";
        $sql .= "       albums.collection_id AS collection_id, ";
        $sql .= "       collections.gallery_id ";
        $sql .= "FROM albums LEFT JOIN collections ON albums.collection_id=collections.id ";
        $sql .= "WHERE collections.gallery_id=$gallery_id AND collections.id=$collection_id";
        $result = mysql_query($sql) or die('ERROR: album verification failed. ' . mysql_error());
        if(mysql_num_rows($result) < 1)
        {
            print "Album verification failed.  The album does not belong to a gallery.<br /><br />";
            print "Please verify the album exists and belongs to a collection in this gallery.<br />";
            exit;
        }

	$images = uploadImage('fleImage', IMAGES_IMG_DIR);

        if($images['image'] == '' && $images['thumbnail'] == '' && $images['display_image'] == '') 
        {
	    echo "Error uploading file";
	    exit;
	}
	
	$image         = $images['image'];
	$thumbnail     = $images['thumbnail'];
        $display_image = $images['display_image'];
	$ext = strstr($image, ".") ? ltrim(strstr($image, "."), ".") : "";

        $imgData = getimagesize(IMAGES_IMG_DIR . $image);
        $image_width = $imgData[0];
        $image_height = $imgData[1];

        $image_title = mysql_real_escape_string($image_title);
        $image_desc  = mysql_real_escape_string($image_desc);

	$sql  = "INSERT INTO images (album_id, title, description, image, image_width, image_height, ";
        $sql .= "thumbnail, display_image, type) VALUES ($album_id, '$image_title', '$image_desc', ";	
        $sql .= "'$image', $image_width, $image_height, '$thumbnail', '$display_image', '$ext')";
        mysql_query($sql) or die('SQL: ' . $sql . '<br /><br />ERROR: add image failed : ' . mysql_error());
    
        echo "<script>window.location.href='index.php?page=list_image&collection_id=$collection_id&album_id=$album_id';</script>";
	exit;
    } 	

    // Construct the items of a collections select list.
    $sql  = "SELECT collections.id AS collection_id, ";
    $sql .= "collections.name AS collection_name ";
    $sql .= "FROM collections LEFT JOIN albums ON collections.id=albums.collection_id ";
    $sql .= "WHERE collections.gallery_id=$gallery_id ";

    if($album_id != '')
    {
        $sql .= "AND albums.id=$album_id ";
    }
    $sql .= "AND albums.id IS NOT NULL GROUP BY collections.name ORDER BY collections.name";
    $res2 = mysql_query($sql) or die('Error, get collections list failed : ' . mysql_error());
    $collectionList = '';
    while($row2 = mysql_fetch_assoc($res2))
    {
	$collectionList .= "\n" . '<option value="' . $row2['collection_id'] . '"' ;
	if($row2['collection_id'] == $collection_id) { $collectionList .= ' selected'; }
	$collectionList .= '>' . $row2['collection_name'] . '</option>';
    }

    // Construct the items of a album select list.
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
	if ($row2['album_id'] == $album_id) { $albumList .= ' selected'; }
	$albumList .= '>' . $row2['album_name'] . '</option>';	
    }
    if($albumList == '') { $albumList = '<option value="">No Albums For This Collection</option>'; }

?>

<!-- BEGIN: add_image -->

<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr> 
        <td align="left" class="igadmin_font2"><b>Add Image</b></td>
    </tr>
</table>

<form action="" method="post" enctype="multipart/form-data" name="frmAlbum" id="frmAlbum">
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr>
        <th width="150">Collection</th>
        <td width="80" align="left">
            <select class="colselect" name="cboCollection" id="cboCollection" onchange="updateAlbumList(this.value, 'add_image');">
                <?php echo $collectionList; ?>
            </select>
        </td>
    </tr>

    <tr>
        <th width="150">Album</th>
        <td width="80" align="left">
            <select class="colselect" name="cboAlbum" id="cboAlbum" onchange="updateCollectionList(this.value, 'add_image');">
                <?php echo $albumList; ?>
            </select>
        </td>
    </tr>

    <tr> 
        <th width="150">Image Title</th>
        <td align="left"><input name="txtTitle" type="text" id="txtTitle" size="24" maxlength="20" /></td>
    </tr>
    <tr> 
        <th width="150">Description</th>
        <td align="left"><textarea name="mtxDesc" cols="50" rows="4" id="mtxDesc"></textarea></td>
    </tr>
    <tr> 
        <th width="150">Image</th>
        <td align="left"><input name="fleImage" type="file" class="box" id="fleImage2" /></td>
    </tr>
    <tr> 
        <td width="150">&nbsp;</td>
        <td align="left">
            <input name="btnAdd" type="submit" class="box" id="btnAdd" value="Add Image" /> 
            <input name="btnCancel" type="button" id="btnCancel" value="Cancel" onclick="window.history.back();" /> 
        </td>
    </tr>
</table>
</form>

<br />

<?php include 'hints_images.html'; ?>

<!-- END add_image -->
