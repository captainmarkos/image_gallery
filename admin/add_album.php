<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.
    //
    // An album must belong to a collection and therefore a collection can
    // have many albums.  An album can belong to only one collection.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    if(isset($_POST['txtName']))
    {
        $album_name    = $_POST['txtName'];
	$album_desc    = $_POST['mtxDesc'];
        $collection_id = $_POST['cboCollection'];

	$imgName = $_FILES['fleImage']['name'];
	$tmpName = $_FILES['fleImage']['tmp_name'];

        // Make sure the collection_id to be saved belongs to a gallery.
        $sql  = "SELECT id, gallery_id FROM collections WHERE id=$collection_id AND gallery_id=$gallery_id";
        $result = mysql_query($sql) or die('Error, finding gallery_id : ' . mysql_error());
        if(mysql_num_rows($result) < 1)
        {
            print "The collection chosen does not belong to a gallery.<br /><br />";
            print "Please log out and sign back in to try again.<br />";
            exit;
	}

	// We need to rename the image name just to avoid duplicate
	// file names.  First get the file extension.
	$ext = strtolower(strrchr($imgName, "."));
	
	// Now create a new random name.
	$newName = md5(rand() * time()) . $ext;

        // The album image will be saved here.
        $imgPath = ALBUM_IMG_DIR . $newName;
	
	// resize all album image
	$result = createThumbnail($tmpName, $imgPath, THUMBNAIL_WIDTH);
	
	if(!$result) 
        {
	    echo "Error uploading file";
	    exit;
	}
	
        $album_name = mysql_real_escape_string($album_name);
        $album_desc = mysql_real_escape_string($album_desc);

	$sql  = "INSERT INTO albums (collection_id, name, image, description) VALUES ";
        $sql .= "($collection_id, '$album_name', '$newName', '$album_desc')";
        mysql_query($sql) or die('SQL = ' . $sql . '<br /><br />ERROR: add album failed : ' . mysql_error());                    

        // The album is saved, go to the album list.
	echo "<script>window.location.href='index.php?page=list_album&collection_id=$collection_id';</script>";
	exit;
    }

    // get collection names for select list
    $sql  = "SELECT id, name FROM collections WHERE gallery_id=$gallery_id ORDER BY name";
    $result = mysql_query($sql) or die('Error, get collection list failed : ' . mysql_error());

    $collectionList = '';
    while($row = mysql_fetch_assoc($result)) 
    {
	$collectionList .= '<option value="' . $row['id'] . '"' ;
	if($row['id'] == $_GET['collection_id']) 
        {
	    $collectionList .= ' selected';
	}
	$collectionList .= '>' . $row['name'] . '</option>';
    }
?>

<!-- BEGIN add_album -->

<form action="" method="post" enctype="multipart/form-data" name="frmAlbum" id="frmAlbum">
<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Create New Album</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr>
        <th width="150">Collection</th>
        <td align="left"><select class="colselect" name="cboCollection" id="cboCollection"><?php echo $collectionList; ?></select></td>
    </tr>
    <tr> 
        <th width="150">Album Name</th>
        <td align="left"><input name="txtName" type="text" id="txtName" size="24" maxlength="20" /></td>
    </tr>
    <tr> 
        <th width="150">Description</th>
        <td align="left"><textarea name="mtxDesc" cols="50" rows="4" id="mtxDesc"></textarea></td>
    </tr>
    <tr> 
        <th width="150">Image</th>
        <td align="left"><input name="fleImage" type="file" class="box" id="fleImage" /></td>
    </tr>
    <tr> 
        <td width="150" bgcolor="#FFFFFF">&nbsp;</td>
        <td align="left">
            <input name="btnAdd" type="submit" id="btnAdd" value="Add Album" />&nbsp;
            <input name="btnCancel" type="button" id="btnCancel" value="Cancel" onClick="window.history.back();" />
        </td>
    </tr>
</table>
</form>

<br />

<?php include 'hints.html'; ?>

<!-- END add_album -->
