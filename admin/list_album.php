<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];
    $collection_id = isset($_GET['collection_id']) ? $_GET['collection_id'] : '';

    $albumPerPage = 10;
    $pageNumber  = isset($_GET['pageNum']) ? $_GET['pageNum'] : 1;

    $offset = ($pageNumber - 1) * $albumPerPage;
    $albumCounter = $offset + 1;

    // Find all the album records.
    $sql  = "SELECT albums.id AS album_id, ";
    $sql .= "       albums.collection_id AS collection_id, ";
    $sql .= "       albums.name AS album_name, ";
    $sql .= "       albums.image AS album_image, ";
    $sql .= "       COUNT(images.id) AS numimages ";
    $sql .= "FROM albums ";
    $sql .= "LEFT JOIN images ON albums.id=images.album_id ";
    $sql .= "JOIN collections ON albums.collection_id=collections.id ";
    $sql .= "WHERE collections.gallery_id=$gallery_id ";
    if($collection_id != '') 
    {
        $sql .= "AND albums.collection_id=$collection_id ";
    }
    $sql .= "GROUP BY albums.name LIMIT $offset, $albumPerPage";
    $result = mysql_query($sql) or die('Error, list album failed. ' . mysql_error());


    // Construct the items of a collections select list.
    $sql = "SELECT id, name FROM collections WHERE gallery_id=$gallery_id ORDER BY name";
    $res2 = mysql_query($sql) or die('Error, get collections list failed : ' . mysql_error());

    $collectionList = '';
    while($row2 = mysql_fetch_assoc($res2))
    {
	$collectionList .= '<option value="' . $row2['id'] . '"' ;
	if($row2['id'] == $collection_id) 
        {
	    $collectionList .= ' selected';
	}
	$collectionList .= '>' . $row2['name'] . '</option>';
    }
?>

<!-- BEGIN list_album -->

<center>

<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr> 
        <td align="left"  class="igadmin_font2"><b>Album Listing</b></td>
        <td align="right" class="igadmin_font1">Collection : 
            <select name="cboCollection" id="cboCollection" class="myselect" onchange="viewAlbum(this.value);">
                <option value="">-- All Collections --</option>
                <?php echo $collectionList; ?> 
            </select>
        </td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th align="center" width="30">&#35;</th>
        <th align="center">Collection</td>
        <th align="center">Album</th>
        <th align="center" width="100" align="center">Images</th>
        <th align="center" width="60" align="center">&nbsp;</th>
        <th align="center" width="60" align="center">&nbsp;</th>
    </tr>

<?php 
    if(mysql_num_rows($result) == 0) 
    {
        print "<tr bgcolor=\"#FFFFFF\">\n";
        print "    <td colspan=\"6\"><b>No album(s) yet</b></td>\n";
        print "</tr>\n";
    } 
    else 
    {
        $albumCounter = $offset + 1;
	while($row = mysql_fetch_assoc($result)) 
        {
	    $numimages = "<a href=\"?page=list_image&album_id=" . $row['album_id'] . "\">" . $row['numimages'] . " Images</a>";
?>
    <tr valign="middle"> 
        <td width="30" align="center"><?php echo $albumCounter++; ?></td>
        <td align="center"><?php $cn = getCollectionName($row['collection_id']); print $cn; ?></td>

        <td align="center">
            <a href="?page=album_detail&album_id=<?php echo $row['album_id']; ?>"><img src="<?php echo getImage('album', $row['album_image']); ?>" width="100" border="1" /></a>
            <br />
            <a href="?page=album_detail&amp;album_id=<?php echo $row['album_id']; ?>"><?php echo $row['album_name']; ?></a>
        </td>

        <td width="120" align="center"><?php echo $numimages; ?></td>

        <td width="60" align="center">
            <?php $modifyhref = "?page=modify_album&collection_id=" . $row['collection_id'] . "&album_id=" . $row['album_id']; ?>
            <a href="<?php echo $modifyhref; ?>">Modify</a>
        </td>
        <td width="60" align="center"><a href="javascript:deleteAlbum(<?php echo $row['album_id']; ?>);">Delete</a></td>
    </tr>

<?php
        } // end while
    }

    $sql  = "SELECT albums.id FROM collections, albums LEFT JOIN images ON albums.id=images.album_id ";
    $sql .= "WHERE collections.gallery_id=$gallery_id ";
    if($collection_id != '')
    {
        $sql .= "AND albums.collection_id=$collection_id ";
    }
    $sql .= "GROUP BY albums.id ORDER BY albums.name";
    $result = mysql_query($sql) or die('SQL: ' . $sql . mysql_error());
    $totalResults = mysql_num_rows($result);	
    $pagingLink = getPagingLink($totalResults, $pageNumber, $albumPerPage, "page=list_album");

?>
    <tr valign="middle" bgcolor="#ffffff" style="height: 30px;">
        <td colspan="6">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="left"><?php print $totalResults; ?> Total Albums</td>
                <td align="right"><?php print $pagingLink ?>&nbsp;</td>
            </tr>
            </table>
        </td>
    </tr>

    <tr>
        <?php 
            if($collection_id == '') 
                { $onclicktxt = "window.location.href='index.php?page=add_album';"; }
            else
                { $onclicktxt = "window.location.href='index.php?page=add_album&collection_id=$collection_id';"; }
        ?>
        <td colspan="6" align="right"><input type="button" name="btnAdd" value="Add Album" onclick="<?php print $onclicktxt; ?>" /></td>
    </tr>
</table>
</center>

<br />

<?php if($totalResults < 3) { include 'hints.html'; } ?>

<!-- END list_album -->


