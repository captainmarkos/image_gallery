<?php

    // An album can have many images.  List all the albums for that belong to the gallery id.
    // Get all albums for the given gallery_id and collection_id.
    $gallery_id = isset($_GET['gallery_id']) && ($_GET['gallery_id'] != '') ? $_GET['gallery_id'] : '';
    $collection_id = isset($_GET['collection_id']) && ($_GET['collection_id'] != '') ? $_GET['collection_id'] : '';

    $sql  = "SELECT galleries.id AS gallery_id, ";
    $sql .= "       collections.id AS collection_id, ";
    $sql .= "       albums.id AS album_id, ";
    $sql .= "       albums.name AS album_name, ";
    $sql .= "       albums.image AS album_image ";
    $sql .= "FROM galleries, collections ";
    $sql .= "LEFT JOIN albums ON albums.collection_id=collections.id ";
    $sql .= "WHERE galleries.id=$gallery_id AND collections.id=$collection_id ORDER BY albums.id";
    $result = mysql_query($sql) or die('Error, list album failed. ' . mysql_error());

    if((mysql_num_rows($result) == 0) || ($gallery_id == '') || ($collection_id == ''))
    {
	echo "No albums yet";
    } 
    else 
    {
        echo "<!-- BEGIN list_album -->\n";
	echo '<table width="700" border="0" cellspacing="1" cellpadding="2" align="center">';
        echo "\n";
	
	// The album is listed in a table.  Here we specify how many columns
	// we want to show on each row.
	$colsPerRow = 4;
	
	// width of each column in percent
	$colWidth = (int)(100/$colsPerRow);
	$i = 0;
	while($row = mysql_fetch_assoc($result)) 
        {
            if(empty($row['album_id']) == true) { print "No album(s)"; break; }

	    if($i % $colsPerRow == 0)
            {		
		echo "<tr>\n";   // start a new row
	    }

            $sql = "SELECT COUNT(images.id) AS numimages FROM images WHERE images.album_id=" . $row['album_id'];
            $res2 = mysql_query($sql) or die("Error, getting image count failed.<br />$sql<br />" . mysql_error());
            $row2 = mysql_fetch_assoc($res2);

            $numImages  = $row2['numimages'];
            $numImages .= ($row2['numimages'] > 1) ? " images" : " image";

	    echo '<td width="' . $colWidth . '%">' . 
	         '<a class="iglink2" href="index.php?page=list_image&gallery_id=' . $gallery_id . '&collection_id=' . $collection_id .
                 '&album_id=' . $row['album_id'] . '">' .
	         '<img src="' . getImage('album', $row['album_image']) . '" width="100" height="75" border="1" />' .
		 '<br />' . $row['album_name'] . '</a><br />' . $numImages . "</td>\n";

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
        echo "<!-- END list_album -->\n";

}
?>
