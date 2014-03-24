<?php

require_once('lib/db_helper.php');
require_once('lib/image_gallery.php');

$db_helper = new DBHelper($dbconn);
$image_gallery = new ImageGallery($dbconn);

    // A gallery can have many collections.  List all the galleries.
    // Perform a JOIN because we need to get a collection count for each gallery.
    $sql  = "SELECT galleries.id, galleries.name, galleries.image, ";
    $sql .= "count(collections.id) AS numcollections FROM galleries ";
    $sql .= "LEFT JOIN collections ON galleries.id=collections.gallery_id ";
    $sql .= "GROUP BY galleries.id ORDER BY galleries.name";
    $result = $dbconn->query($sql) or die('Error, list galleries failed. ' . $dbconn->error());

    if($result->num_rows == 0) {
	echo "No galleries yet";
    } 
    else {
        echo "<!-- BEGIN list_gallery -->\n";
	echo '<table width="700" border="0" cellspacing="1" cellpadding="2" align="center">';
        echo "\n";
	
	// The gallery is listed in a table.  Here we specify how many columns
	// we want to show on each row.
	$colsPerRow = 4;
	
	// width of each column in percent
	$colWidth = (int)(100/$colsPerRow);
	$i = 0;
	while($row = $result->fetch_assoc()) {
	    if($i % $colsPerRow == 0) {
		echo "<tr>\n";   // start a new row
	    }

            $numCollections  = $row['numcollections'];
            $numCollections .= ($row['numcollections'] > 1 || $row['numcollections'] == 0) ? " collections" : " collection";


	    echo '<td width="' . $colWidth . '%">' . 
	         '<a class="iglink2" href="index.php?page=list_collection&gallery_id=' . $row['id'] . '">' .
	         '<img src="' . $image_gallery->getImage('gallery', $row['image']) . '" width="100" height="75" border="1" />' .
                 '<br />' . $row['name'] . '</a><br />' . $numCollections . "</td>\n";

	    if(($i % $colsPerRow) == $colsPerRow - 1) {	echo '</tr>'; }    // end this row
		
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
        echo "<!-- END list_gallery -->\n";
}

?>
