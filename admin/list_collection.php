<?php

    // We never need to pass the gallery_id in the query string because its a SESSION variable.
    // Every database query MUST have the gallery_id to keep the gallery protected from other users.

    checkLogin();
    checkSessionVars();

    $gallery_id = $_SESSION['gallery_id'];

    $collectionPerPage = 10;
    $pageNumber  = isset($_GET['pageNum']) ? $_GET['pageNum'] : 1;

    $offset = ($pageNumber - 1) * $collectionPerPage;
    $collectionCounter = $offset + 1;

    $sql  = "SELECT collections.id AS collection_id, ";
    $sql .= "       collections.name AS collection_name, ";
    $sql .= "       collections.image AS collection_image, ";
    $sql .= "       COUNT(albums.id) AS numalbums ";
    $sql .= "FROM collections ";
    $sql .= "LEFT JOIN albums ON collections.id = albums.collection_id ";
    $sql .= "WHERE collections.gallery_id = $gallery_id ";
    $sql .= "GROUP BY collections.id ";
    $sql .= "ORDER BY collections.name LIMIT $offset, $collectionPerPage";
    $result = mysql_query($sql) or die('Error, list collection failed. ' . mysql_error());

?>

<!-- BEGIN list_collection -->

<center>
<table width="100%" border="0" style="height: 30px;" cellpadding="2" cellspacing="1">
    <tr> 
        <td align="left" class="igadmin_font2"><b>Collection Listing</b></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="table_grey">
    <tr> 
        <th align="center" width="30">&#35;</th>
        <th align="center">Collection</td>
        <th align="center" width="120" align="center">Albums</th>
        <th align="center" width="60" align="center">&nbsp;</th>
        <th align="center" width="60" align="center">&nbsp;</th>
    </tr>

<?php 
    if(mysql_num_rows($result) == 0) 
    {
        print "<tr bgcolor=\"#FFFFFF\">\n";
        print "    <td colspan=\"6\"><b>No collection(s) yet</b></td>\n";
        print "</tr>\n";
    } 
    else 
    {
        $collectionCounter = $offset + 1;
	while($row = mysql_fetch_assoc($result)) 
        {
            $numalbums  = "<a href=\"?page=list_album&collection_id=" . $row['collection_id'] . "\">" . $row['numalbums'] . " Albums</a>";
?>
    <tr valign="middle"> 
        <td width="30" align="center"><?php echo $collectionCounter++; ?></td>

        <td align="center">
            <a href="?page=collection_detail&collection_id=<?php echo $row['collection_id']; ?>"><img src="<?php echo getImage('collection', $row['collection_image']); ?>" width="100" height="75" border="1" /></a>
            <br />
            <a href="?page=collection_detail&collection_id=<?php echo $row['collection_id']; ?>"><?php echo $row['collection_name']; ?></a>
        </td>

        <td width="120" align="center"><?php echo $numalbums; ?></td>
        <td width="60" align="center"><a href="?page=modify_collection&collection_id=<?php echo $row['collection_id']; ?>">Modify</a></td>
        <td width="60" align="center"><a href="javascript:deleteCollection(<?php echo $row['collection_id']; ?>);">Delete</a></td>
    </tr>

<?php
        } // end while
    }
    $sql  = "SELECT collections.id AS collection_id FROM collections ";
    $sql .= "LEFT JOIN albums ON collections.id=albums.collection_id ";
    $sql .= "WHERE collections.gallery_id = $gallery_id ";
    $sql .= "GROUP BY collections.id ORDER BY collections.name";
    $result = mysql_query($sql) or die('SQL: ' . $sql . mysql_error());
    $totalResults = mysql_num_rows($result);	
    $pagingLink = getPagingLink($totalResults, $pageNumber, $collectionPerPage, "page=list_collection");

?>
    <tr valign="middle" bgcolor="#ffffff" style="height: 30px;">
        <td colspan="6">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="left"><?php print $totalResults; ?> Total Collections</td>
                <td align="right"><?php print $pagingLink; ?>&nbsp;</td>
            </tr>
            </table>
        </td>
    </tr>

    <tr>
      <td colspan="6" align="right"><input type="button" name="btnAdd" value="Add collection" onclick="window.location.href='index.php?page=add_collection';" /></td>
    </tr>
</table>
</center>

<br />

<?php if($totalResults < 3) { include 'hints.html'; } ?>

<!-- END list_collection -->


