<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    // Get either the collection id from the select list or for the URL.
    $collection_id = isset($_POST['cboCollection']) && ($_POST['cboCollection'] != '') ? $_POST['cboCollection'] : $_GET['collection_id'];

    if(!isset($_GET['album_id'])) { echo "Album ID is not defined"; exit; }  // Make sure the album_id is set.
 
    if(isset($_POST['txtName'])) 
    {
	$album_id     = $_POST['hidAlbumId'];
	$album_name   = $_POST['txtName'];
	$album_desc   = $_POST['mtxDesc'];

        $album_name = mysql_real_escape_string($album_name);
        $album_desc = mysql_real_escape_string($album_desc);

	if($_FILES['fleImage']['tmp_name'] != '') 
        {
	    $imgName = $_FILES['fleImage']['name'];
	    $tmpName = $_FILES['fleImage']['tmp_name'];
	
	    // Just like when we added this album we will need to rename the image
	    // name to avoid duplicate file name problem.
	    $newName = md5(rand() * time()) . strtolower(strrchr($imgName, "."));
		
	    // Resize the new album image.
	    $result = createThumbnail($tmpName, ALBUM_IMG_DIR . $newName, THUMBNAIL_WIDTH);

	    if(!$result) 
            {
		echo "Error uploading file";
		exit;
	    }

	    // Since a new image for this album is specified we need to delete the old one.
 	    $sql  = "SELECT albums.image AS album_image FROM collections, albums ";
            $sql .= "WHERE albums.id=$album_id AND collections.gallery_id=$gallery_id";
	    $result = mysql_query($sql) or die('Error, get album image info failed.<br />' . mysql_error());
 	    $row = mysql_fetch_assoc($result);

	    unlink(ALBUM_IMG_DIR . $row['album_image']);
		
	    $newName = "'$newName'";
	} 
        else 
        {
	    // dont change the image
	    $newName = "image";
	}
		
	$sql  = "UPDATE albums SET name='$album_name', description='$album_desc', ";
        $sql .= "collection_id=$collection_id, image=$newName ";
        $sql .= "WHERE id=$album_id";
        mysql_query($sql) or die('SQL: ' . $sql . '<br><br>ERROR: modify album failed : ' . mysql_error());                    

        // After saving the modification go to the detail page.
	echo "<script>window.location.href='index.php?page=album_detail&collection_id=$collection_id&album_id=$album_id';</script>";
        exit;
    } 
    else 
    {
	// get the album id
	$album_id = $_GET['album_id'];
	
	$sql  = "SELECT albums.id AS album_id, ";
        $sql .= "       albums.collection_id AS collection_id, ";
        $sql .= "       albums.name AS album_name, ";
        $sql .= "       albums.description AS album_description, ";
        $sql .= "       albums.image AS album_image ";
        $sql .= "FROM collections, albums WHERE albums.id=$album_id AND collections.gallery_id=$gallery_id";
	$result = mysql_query($sql) or die('Error, get album info failed. ' . mysql_error());
        $row = mysql_fetch_assoc($result);
	
	if(mysql_num_rows($result) == 0) 
        {
	    // Cant find an album with that id.
   	    print "<p align=\"center\">Album not found. Go back to the ";
            print "<a href=\"index.php\">album list</a></p>\n";
            exit;
	} 

	// get collection list
        $sql = "SELECT id, name FROM collections WHERE gallery_id=$gallery_id ORDER BY name";
	$res2 = mysql_query($sql) or die('Error, get collection list failed : ' . mysql_error());
			
        $collectionList = '';
	while($row2 = mysql_fetch_assoc($res2)) 
        {
	    $collectionList .= '<option value="' . $row2['id']. '"';
	    if($row2['id'] == $collection_id)
            {
		$collectionList .= ' selected';
	    }
	    $collectionList .= '>' . $row2['name'] . '</option>' . "\n";	
	}

?>

<!-- BEGIN: modify_album -->

<form method="post" enctype="multipart/form-data" name="frmAlbum" id="frmAlbum">
<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Modify Album</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr>
        <th width="150">Collection</th>
        <td align="left"><select class="colselect" name="cboCollection" id="cboCollection"><?php echo $collectionList; ?></select></td>
    </tr>
    <tr> 
        <th width="150">Album Name</th>
        <td align="left"> 
            <input name="txtName" type="text" id="txtName" size="24" maxlength="20" value="<?php echo $row['album_name']; ?>" />
        </td>
    </tr>
    <tr> 
        <th width="150">Description</th>
        <td align="left"> 
            <textarea name="mtxDesc" cols="50" rows="4" id="mtxDesc"><?php echo $row['album_description']; ?></textarea>
        </td>
        </tr>
    <tr> 
        <th width="150">Image</th>
        <td align="left">
            <img src="<?php echo getImage('album', $row['album_image']); ?>" border="1" width="100" /><br /> 
            <input name="fleImage" type="file" class="box" id="fleImage2" style="margin-top: 2px;" />
        </td>
    </tr>
    <tr> 
        <td width="150">&nbsp;</td>
        <td align="left">
            <?php 
                $cancel_onclick  = "window.location.href='index.php?page=list_album&";
                $cancel_onclick .= "collection_id=$collection_id';";
            ?>
            <input name="btnAdd" type="submit" id="btnAdd" value="Save" /> 
            <input name="btnCancel" type="button" id="btnCancel" value="Cancel" onClick="<?php echo $cancel_onclick; ?>" /> 
            <input name="hidAlbumId" type="hidden" id="hidAlbumId" value="<?php echo $album_id; ?>" />
        </td>
    </tr>
</table>
</form>

<br />

<?php include 'hints.html'; ?>

<!-- END: modify_album -->

<?php

    } // end else
?>
