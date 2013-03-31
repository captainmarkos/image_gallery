<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    // make sure the album id is present
    if(!isset($_GET['collection_id'])) 
    {
	echo "Collection id is not defined";
        exit;
    } 

    // get the collection id
    $collection_id = $_GET['collection_id'];

    $sql  = "SELECT id, name, description, image FROM collections WHERE id=$collection_id " ;
    $sql .= "AND gallery_id=$gallery_id";
    $result = mysql_query($sql) or die('Error, get collection info failed. SQL=' . $sql . '<br /><br /> ' . mysql_error());
    $row = mysql_fetch_assoc($result);	

    if(mysql_num_rows($result) == 0) 
    {
	// Cant find a collection with that id or the collection does not belong to a gallery.
   	print "<p>The collection is not found.</p>\n";
        print "<br/>\n";
        print "Go back to the <a href=\"index.php?page=list_collection\">collection list</a>\n";
        exit;
    } 

?>

<!-- BEGIN: collection_detail -->

<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr>
        <td align="left" class="igadmin_font2"><b>Collection Details</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th width="150" style="height: 30px;">Collection Name</th>
        <td align="left"><?php echo $row['name']; ?>&nbsp;</td>
    </tr>
    <tr> 
        <th width="150" style="height: 30px;">Description</th>
        <td align="left"><?php echo $row['description']; ?>&nbsp;</td>
    </tr>
    <tr> 
        <th width="150">Image</th>
        <td align="left"><img src="<?php echo getImage('collection', $row['image']); ?>" width="100" height="75" border="1" />&nbsp;</td>
    </tr>
    <tr> 
        <td width="150">&nbsp;</td>
        <td align="left">
            <input name="btnModify" type="button" id="btnModify" value="Modify" onclick="window.location.href='index.php?page=modify_collection&amp;collection_id=<?php echo $collection_id; ?>';" />
            <input name="btnAddImage" type="button" id="btnAddImage" value="Add Album" onclick="window.location.href='index.php?page=add_album&collection_id=<?php echo $collection_id; ?>';" />
            <input name="btnBack" type="button" id="btnBack" value="Back" onClick="window.history.back();">
        </td>
    </tr>
</table>

<br />

<?php include 'hints.html'; ?>

<!-- END: collection_detail -->
