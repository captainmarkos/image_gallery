<?php

    // A collection may have many albums.  List all the collections.
    // Get all collections for a given gallery_id.
    $gallery_id = isset($_GET['gallery_id']) && ($_GET['gallery_id'] != '') ? $_GET['gallery_id'] : '';

    $sql  = "SELECT galleries.id AS gallery_id, ";
    $sql .= "       collections.id AS collection_id, ";
    $sql .= "       collections.name AS collection_name, ";
    $sql .= "       collections.image AS collection_image ";
    $sql .= "FROM galleries ";
    $sql .= "LEFT JOIN collections ON galleries.id=collections.gallery_id ";
    $sql .= "WHERE galleries.id=$gallery_id GROUP BY collections.id ORDER BY collections.name";
    $result = mysql_query($sql) or die('Error, list collections failed. ' . mysql_error());

    if((mysql_num_rows($result) == 0) || ($gallery_id == ''))
    {
	echo "No collections yet";
    } 
    else 
    {
        echo "<!-- BEGIN list_collection -->\n";
	echo '<table width="700" border="0" cellspacing="1" cellpadding="2" align="center">';
        echo "\n";
	
	// The collection is listed in a table.  Here we specify how many columns
	// we want to show on each row.
	$colsPerRow = 4;
	
	// width of each column in percent
	$colWidth = (int)(100/$colsPerRow);
	$i = 0;
	while($row = mysql_fetch_assoc($result)) 
        {
            if(empty($row['collection_id']) == true) { print "No collection(s)"; break; }

	    if($i % $colsPerRow == 0)
            {		
		echo "<tr>\n";   // start a new row
	    }

            $sql = "SELECT COUNT(albums.id) AS numalbums FROM albums WHERE albums.collection_id=" . $row['collection_id'];
            $res2 = mysql_query($sql) or die('Error, list collections failed. ' . mysql_error());
            $row2 = mysql_fetch_assoc($res2);

            $numAlbums  = $row2['numalbums'];
            $numAlbums .= ($row2['numalbums'] > 1) ? " albums" : " album";


	    echo '<td width="' . $colWidth . '%">' . 
	         '<a class="iglink2" href="index.php?page=list_album&gallery_id=' . 
                 $row['gallery_id'] . '&collection_id=' . $row['collection_id'] . '">' .
	         '<img src="' . getImage('collection', $row['collection_image']) . '" width="100" height="75" border="1" />' .
		 '<br />' . $row['collection_name'] . '</a><br />' . $numAlbums . "</td>\n";

	    if($i % $colsPerRow == $colsPerRow - 1) 
            {
		echo '</tr>';    // end this row
	    }		
		
	    $i += 1;
	}
	
	// print blank columns
	if($i % $colsPerRow != 0) 
        {
	    while($i++ % $colsPerRow != 0) 
            {
		echo '<td width="' . $colWidth . '%">&nbsp;' . "</td>\n";
	    }	
	    echo "</tr>\n";
	}	
	echo "</table>\n";
        echo "<!-- END list_collection -->\n";
}
?>
