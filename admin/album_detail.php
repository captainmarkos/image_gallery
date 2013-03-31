<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query should have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    // make sure the album id is present
    if(!isset($_GET['album_id'])) 
    {
	echo "Album id is not defined.";
        exit;
    } 

    $gallery_id = $_SESSION['gallery_id'];
    $album_id = $_GET['album_id'];
	
    $sql  = "SELECT albums.id AS album_id, ";
    $sql .= "       collections.id AS collection_id, ";
    $sql .= "       collections.name AS collection_name, ";
    $sql .= "       albums.name AS album_name, ";
    $sql .= "       albums.description AS album_description, ";
    $sql .= "       albums.image AS album_image ";
    $sql .= "FROM albums, collections ";
    $sql .= "WHERE albums.id=$album_id AND albums.collection_id=collections.id ";
    $sql .= "AND collections.gallery_id=$gallery_id";
    $result = mysql_query($sql) or die('Error, get album info failed. ' . mysql_error());
    $row = mysql_fetch_assoc($result);	

    if(mysql_num_rows($result) == 0) 
    {
        // Cant find an album with that id or the album does not belong to a collection.
        print "<p>The album is not found or does not belong to a collection.</p>\n";
        print "<br/>\n";
        print "Go back to the <a href=\"index.php?page=list_album\">album list</a>\n";
        exit;
    } 
	
?>

<!-- BEGIN: album_detail -->

<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Album Details</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th width="150" style="height: 30px;">Collection Name</th>
        <td align="left"><?php echo $row['collection_name']; ?>&nbsp;</td>
    </tr>
    <tr> 
        <th width="150" style="height: 30px;">Album Name</th>
        <td align="left"><?php echo $row['album_name']; ?>&nbsp;</td>
    </tr>
    <tr> 
        <th width="150" style="height: 30px;">Description</th>
        <td align="left"><?php echo $row['album_description']; ?>&nbsp;</td>
    </tr>
    <tr> 
        <th width="150">Image</th>
        <td align="left"><img src="<?php echo getImage('album', $row['album_image']); ?>" width="100" border="1" />&nbsp;</td>
    </tr>
    <tr> 
        <td width="150">&nbsp;</td>
        <td align="left">
            <input name="btnModify" type="button" id="btnModify" value="Modify" onclick="window.location.href='index.php?page=modify_album&collection_id=<?php echo $row['collection_id']; ?>&album_id=<?php echo $album_id; ?>';" />
            <input name="btnAddImage" type="button" id="btnAddImage" value="Add Image" onclick="window.location.href='index.php?page=add_image&album_id=<?php echo $album_id; ?>';" />
            <input name="btnBack" type="button" id="btnBack" value="Back" onClick="window.history.back();">
        </td>
    </tr>
</table>

<br />

<?php include 'hints.html'; ?>

<!-- END: album_detail -->

