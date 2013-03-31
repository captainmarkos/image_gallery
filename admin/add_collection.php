<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.
    //
    // A collection can have many albums however all collections belong to one gallery.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    if(isset($_POST['txtName']))
    {
        $collection_name = $_POST['txtName'];
	$collection_desc = $_POST['mtxDesc'];

	$imgName = $_FILES['fleImage']['name'];
	$tmpName = $_FILES['fleImage']['tmp_name'];

	// We need to rename the image name just to avoid duplicate
	// file names.  First get the file extension.
	$ext = strtolower(strrchr($imgName, "."));
	
	// Now create a new random name.
	$newName = md5(rand() * time()) . $ext;

        // The album image will be saved here.
        $imgPath = COLLECTION_IMG_DIR . $newName;
	
	// resize all album image
	$result = createThumbnail($tmpName, $imgPath, THUMBNAIL_WIDTH);
	
	if(!$result) 
        {
	    echo "Error uploading file";
	    exit;
	}
	
        $collection_name = mysql_real_escape_string($collection_name);
        $collection_desc = mysql_real_escape_string($collection_desc);

	$sql  = "INSERT INTO collections (gallery_id, name, image, description) VALUES ";
        $sql .= "($gallery_id, '$collection_name', '$newName', '$collection_desc')";
        mysql_query($sql) or die('SQL = ' . $sql . '<br><br>Error, add collection failed : ' . mysql_error());

	echo "<script>window.location.href='index.php?page=list_collection';</script>";
	exit;
    }

?>

<!-- BEGIN add_collection -->

<form action="" method="post" enctype="multipart/form-data" name="frmCollection" id="frmCollection">
<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Create New Collection</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th width="150">Collection Name</th>
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
            <input name="btnAdd" type="submit" id="btnAdd" value="Add Collection" />&nbsp;
            <input name="btnCancel" type="button" id="btnCancel" value="Cancel" onClick="window.history.back();" />
        </td>
    </tr>
</table>
</form>

<br />

<?php include 'hints.html'; ?>

<!-- END add_collection -->
