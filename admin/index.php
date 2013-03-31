<?php

    require_once '../lib/config.php';
    require_once '../lib/functions.php';

    checkLogin();

    $avatar_image = '';

    if(empty($_SESSION['email']) == false)  // user is signed in
    {
        $sql  = "SELECT users.email AS email, users.gallery_id AS gallery_id, ";
        $sql .= "galleries.name AS gallery_name, galleries.image AS gallery_image ";
        $sql .= "FROM users LEFT JOIN galleries ON users.gallery_id=galleries.id ";
        $sql .= "WHERE users.email='" . $_SESSION['email'] . "'";
        $result = mysql_query($sql) or die("SQL: $sql<br>\nERROR: " . mysql_error());
        $num_rows = mysql_num_rows($result);
        $row = mysql_fetch_assoc($result);
        if($num_rows !=0 && (empty($row['gallery_image']) == false))
        {
            $_SESSION['gallery_id'] = $row['gallery_id'];
            $avatar_image = WWWROOT_GALLERY_IMG_DIR . $row['gallery_image'];
        }
    }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Image Gallery Admin</title>
<link rel="stylesheet" type="text/css" href="admin.css" />
<script type="text/javascript" src="javascript/admin.js"></script>
</head>
<body>
<center>
<table width="780" border="1" cellpadding="2" cellspacing="1">
    <tr>
        <td width="150" valign="top" align="left">

            <?php
                print '<center><img src="' . $avatar_image . '" style="margin: 4px 6px 0px 0px;" ' .
                      'width="100" height="75" border="1" /><br /><font class="igadmin_font1">';
                print substr($_SESSION['email'], 0, strpos($_SESSION['email'], '@'));
                print '</font></center>';
            ?>

            <hr width="95%" size="1" />
            <p>&nbsp;<a href="index.php?page=list_collection">List Collections</a></p>
            <p>&nbsp;<a href="index.php?page=add_collection">Add Collection</a></p>

            <hr width="95%" size="1" />
            <p>&nbsp;<a href="index.php?page=list_album">List Albums</a></p>
            <p>&nbsp;<a href="index.php?page=add_album">Add Album</a></p>

            <hr width="95%" size="1" />
            <p>&nbsp;<a href="index.php?page=list_image">List Images</a></p>
            <p>&nbsp;<a href="index.php?page=add_image">Add Image</a></p>
            <p>&nbsp;<a href="index.php?page=add_multiple_images">Add Multiple Images</a></p>

            <hr width="95%" size="1" />
            <p>&nbsp;<a href="logout.php">Logout</a></p>
            <p>&nbsp;<a href="../../logbook/index.php?page=suggestions" target="_blank">Contact Us</a></p>
            <p>&nbsp;</p>
        </td>
        <td align="center" valign="top" style="padding: 10px;">

<?php

    if(isset($_GET['deleteCollection']) && isset($_GET['collection_id'])) 
    {
        $collection_id = $_GET['collection_id'];
	
        $sql = "SELECT id, name, image FROM collections WHERE id=$collection_id";
	$result = mysql_query($sql) or die('Delete collection failed. ' . mysql_error());
	if(mysql_num_rows($result) == 1) 
        {
	    $row = mysql_fetch_assoc($result);
	
            // Remove the collection image
	    unlink(COLLECTION_IMG_DIR . $row['image']);

            // Delete the collections record from the table.
            $sql = "DELETE FROM collections WHERE id=$collection_id";
	    mysql_query($sql) or die('Delete collection failed. ' . mysql_error());

            // Find the album(s) in this collection so we can delete the images records.
            $sql = "SELECT id, image FROM albums WHERE collection_id=$collection_id";
	    $res2 = mysql_query($sql) or die('Error finding albums for Delete collection. ' . mysql_error());
            while($row2 = mysql_fetch_assoc($res2))
            {
                delete_images('IMAGES', $row2['id']);  // delete images and thumbnails from this album_id
            }

            // delete all the albums for this collection_id
            delete_images("ALBUM", $collection_id);

	    echo "<script>window.location.href='index.php';</script>";
            exit;
	} 
        else 
        {
	    echo "<p align=center><b>Cannot delete a non-existent collection.</b></p>";
	}
    }


    if(isset($_GET['deleteAlbum']) && isset($_GET['album_id'])) 
    {
        $album_id = $_GET['album_id'];
	
        $sql = "SELECT id, name, image FROM albums where id=$album_id";
	$result = mysql_query($sql) or die('Delete image failed. ' . mysql_error());
	if(mysql_num_rows($result) == 1) 
        {
	    $row = mysql_fetch_assoc($result);
	
            delete_images('IMAGES', $album_id);  // delete images and thumbnails from this album id

            unlink(ALBUM_IMG_DIR . $row['image']);

            $sql = "DELETE FROM albums WHERE id=$album_id";
	    $result = mysql_query($sql) or die('Delete album failed. ' . mysql_error());

	    echo "<script>window.location.href='index.php';</script>";
            exit;
	} 
        else 
        {
	    echo "<p align=\"center\"><b>Cannot delete a non-existent album.</b></p>";
	}
    }


    // Which page should be shown now?
    $page = (isset($_GET['page']) && $_GET['page'] != '') ? $_GET['page'] : 'list_collection';

    // Only the pages listed here can be accessed any other pages will result in error.
    $allowedPages =     array('list_album'); 
    array_push($allowedPages, 'add_album'); 
    array_push($allowedPages, 'album_detail'); 
    array_push($allowedPages, 'modify_album'); 
    array_push($allowedPages, 'list_image'); 
    array_push($allowedPages, 'add_image'); 
    array_push($allowedPages, 'add_multiple_images'); 
    array_push($allowedPages, 'image_detail'); 
    array_push($allowedPages, 'modify_image');
    array_push($allowedPages, 'add_collection');
    array_push($allowedPages, 'collection_detail');
    array_push($allowedPages, 'list_collection');
    array_push($allowedPages, 'modify_collection');

			
    if(in_array($page, $allowedPages)) 
    {
        include $page . '.php';
    } 
    else 
    {
?>
        <table width="100%" border="0" align="center" cellpadding="2" cellspacing="1">
            <tr> 
            <td align="center"><b>Error : The page you are looking for does not exist.</b></td>
            </tr>
        </table>
<?php	
    }
?>
        </td>
    </tr>
</table>

</center>

<br />
<br />

</body>
</html>
