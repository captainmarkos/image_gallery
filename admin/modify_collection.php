<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    if(!isset($_GET['collection_id']))   // Make sure the collection id is present.
    {
	echo "Collection ID is not defined";
	exit;
    }
 
    if(isset($_POST['txtName'])) 
    {
	$collection_id   = $_POST['hidCollectionId'];
	$collection_name = $_POST['txtName'];
	$collection_desc = $_POST['mtxDesc'];

        $collection_name = mysql_real_escape_string($collection_name);
        $collection_desc = mysql_real_escape_string($collection_desc);

	if($_FILES['fleImage']['tmp_name'] != '') 
        {
	    $imgName = $_FILES['fleImage']['name'];
	    $tmpName = $_FILES['fleImage']['tmp_name'];
	
	    // Just like when we added this album we will need to rename the image
	    // name to avoid duplicate file name problem.
	    $newName = md5(rand() * time()) . strtolower(strrchr($imgName, "."));

	    // Resize the new album image.
	    $result = createThumbnail($tmpName, COLLECTION_IMG_DIR . $newName, THUMBNAIL_WIDTH);

	    if(!$result) 
            {
		echo "Error uploading file";
		exit;
	    }

	    // Since a new image for this collection is specified we need to delete the old one.
 	    $sql = "SELECT image FROM collections WHERE id=$collection_id AND gallery_id=$gallery_id";
	    $result = mysql_query($sql) or die('Error, get collection image info failed.<br />' . mysql_error());
 	    $row = mysql_fetch_assoc($result);

	    unlink(COLLECTION_IMG_DIR . $row['image']);
		
	    $newName = "'$newName'";
	} 
        else 
        {
	    $newName = "image";  // dont change the image
	}
		
	$sql  = "UPDATE collections SET name='$collection_name', description='$collection_desc', ";
        $sql .= "image=$newName WHERE id=$collection_id AND gallery_id=$gallery_id";
        mysql_query($sql) or die('SQL: ' . $sql . '<br><br>ERROR: modify collection failed : ' . mysql_error());

        // After saving the modification go to the detail page.
	echo "<script>window.location.href='index.php?page=collection_detail&collection_id=$collection_id';</script>";
        exit;
    } 
    else 
    {
	// get the collection id
	$collection_id = $_GET['collection_id'];
	
	$sql  = "SELECT id, name, description, image FROM collections ";
        $sql .= "WHERE id=$collection_id AND gallery_id=$gallery_id";
	$result = mysql_query($sql) or die('Error, get collection info failed. ' . mysql_error());
        $row = mysql_fetch_assoc($result);		

	if(mysql_num_rows($result) == 0) 
        {
	    // Cant find an collection with that id.
   	    print "<p align=\"center\">Collection not found. Go back to the ";
            print "<a href=\"index.php\">collection list</a></p>\n";
            exit;
	} 

?>

<!-- BEGIN: modify_collection -->

<form method="post" enctype="multipart/form-data" name="frmCollection" id="frmCollection">
<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Modify Collection</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th width="150">Collection Name</th>
        <td align="left"> 
            <input name="txtName" type="text" id="txtName" size="24" maxlength="20" value="<?php echo $row['name']; ?>" />
        </td>
    </tr>
    <tr> 
        <th width="150">Description</th>
        <td align="left"> 
            <textarea name="mtxDesc" cols="50" rows="4" id="mtxDesc"><?php echo $row['description']; ?></textarea>
        </td>
        </tr>
    <tr> 
        <th width="150">Image</th>
        <td align="left">
            <img src="<?php echo getImage('collection', $row['image']); ?>" border="1" width="100" /><br /> 
            <input name="fleImage" type="file" class="box" id="fleImage2" style="margin-top: 2px;" />
        </td>
    </tr>
    <tr> 
        <td width="150">&nbsp;</td>
        <td align="left"> 
            <input name="btnAdd" type="submit" id="btnAdd" value="Save" /> 
            <input name="btnCancel" type="button" id="btnCancel" value="Cancel" onClick="window.history.back();" /> 
            <input name="hidCollectionId" type="hidden" id="hidCollectionId" value="<?php echo $collection_id; ?>" />
        </td>
    </tr>
</table>
</form>

<br />

<?php include 'hints.html'; ?>

<!-- END: modify_collection -->

<?php

    } // end else
?>
