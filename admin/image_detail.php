<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query should have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    // make sure the image id is present
    if(!isset($_GET['image_id'])) 
    {
	echo "Image id is not defined.";
        exit;
    } 

    $image_id = $_GET['image_id'];
    $album_id = $_GET['album_id'];

    $sql = "SELECT name FROM albums WHERE id=$album_id";
    $result = mysql_query($sql) or die('ERROR: unable to get album name. ' . mysql_error());
    $row = mysql_fetch_assoc($result);
    $album_name = $row['name'];

    $sql  = "SELECT images.id AS image_id, ";
    $sql .= "       images.title AS image_title, ";
    $sql .= "       images.image AS image, ";
    $sql .= "       images.description AS image_description, ";
    $sql .= "       images.album_id AS album_id, ";
    $sql .= "       collections.id AS collection_id, ";
    $sql .= "       collections.name AS collection_name ";
    $sql .= "FROM collections, images ";
    $sql .= "LEFT JOIN albums ON images.album_id=albums.id ";
    $sql .= "WHERE collections.gallery_id=$gallery_id AND albums.collection_id=collections.id ";
    $sql .= "AND images.id=$image_id";
    $result = mysql_query($sql) or die('Error, get image info failed. ' . mysql_error());
    if(mysql_num_rows($result) == 0) 
    {
        print "<p align=\"center\">Image not found. ";
        print "Click <a href=\"index.php?page=list_image\">here</a> to go to the image list.</p>\n";
        exit;
    }
    $row = mysql_fetch_assoc($result);

    $imgData = getimagesize(IMAGES_IMG_DIR . $row['image']);
    $img_w = $imgData[0];
    $img_h = $imgData[1];
    $imgWWWFilePath = getImage('glimage', $row['image']);
?>

<!-- BEGIN: image_detail -->
<center>
<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Image Detail</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th width="150" style="height: 30px;">Collection</th>
        <td align="left"><?php echo $row['collection_name']; ?>&nbsp;</td>
    </tr>
    <tr> 
        <th width="150" style="height: 30px;">Album</th>
        <td align="left"><?php echo $album_name; ?>&nbsp;</td>
    </tr>
    <tr> 
        <th width="150" style="height: 30px;">Image Title</th>
        <td align="left"><?php echo $row['image_title']; ?>&nbsp;</td>
    </tr>

    <tr> 
        <th width="150" style="height: 30px;">Description</th>
        <td align="left">
            <textarea name="mtxDesc" cols="50" rows="4" id="mtxDesc" readonly="readonly"><?php echo $row['image_description']; ?>&nbsp;</textarea></td>
    </tr>

    <tr> 
        <th width="150" style="height: 30px;">Image</th>
        <td align="left"><img width="400" style="max-width: 400px;" src="<?php print $imgWWWFilePath; ?>" /></td>
    </tr>
    <tr> 
        <td align="left" colspan="2">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="height: 30px;">
                <tr valign="middle">
                    <td align="left">Actual image size (w x h): <?php print "$img_w x $img_h"; ?></td>
                    <td align="right">Click <a target="_blank" href="<?php print $imgWWWFilePath; ?>"><b>here</b></a> to view actual image.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr> 
        <td colspan="2" align="center">
            <input name="btnModify" type="button" id="btnModify" value="Modify" onclick="window.location.href='index.php?page=modify_image&album_id=<?php echo $row['album_id']; ?>&image_id=<?php echo $row['image_id']; ?>';" />  
            <input name="btnBack" type="button" id="btnBack" value="Back" onclick="window.history.back();" />
        </td>
    </tr>
</table>
</center>

<br />

<!-- END: image_detail -->
