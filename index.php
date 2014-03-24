<?php

require_once('lib/config.php');
require_once('lib/image_gallery.php');

$image_gallery = new ImageGallery($dbconn);

// Which page should be shown now
$page = (isset($_GET['page']) && $_GET['page'] != '') ? $_GET['page'] : 'list_gallery';

// Only the pages listed here can be accessed
// any other pages will result in error
$allowedPages = array('list_gallery', 'list_collection', 'list_album', 'list_image');

if(!in_array($page, $allowedPages)) {
    $page = 'notfound';
}

$logbook_id = isset($_POST['logbook_id']) && ($_POST['logbook_id'] != '') ? $_POST['logbook_id'] : 0;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="Scuba Diver Photo Gallery" />
<meta name="keywords" content="blue wild scuba, scuba diving, photo gallery, image gallery" />
<title>Bluewild - Image Gallery</title>
<link rel="stylesheet" type="text/css" href="gallery.css" />
<?php if($page == 'list_image') { echo "<script type=\"text/javascript\" src=\"javascript/jquery-1.5.1.min.js\"></script>\n"; } ?>
<?php if($page == 'list_image') { echo "<script type=\"text/javascript\" src=\"javascript/jquery.tools.min.js\"></script>\n"; } ?>
<?php if($page == 'list_image') { echo "<script type=\"text/javascript\" src=\"javascript/gallery.js\"></script>\n"; } ?>
</head>
<body>

<br />
<center>

<?php
if($_SESSION['email'] != '') {
    $_SESSION['manage_photos'] = true;
?>
    <table width="750" border="0" id="table_ig_signin">
        <tr>
            <td align="left" class="igfont_small">Signed In: <?php echo $_SESSION['email']; ?>&nbsp;</td>
            <td align="right" class="igfont_small">
                <a href="admin/login.php" class="iglink1" target="_blank">Manage Photos</a> | 
                <a href="../logbook/index.php?logout=yes" class="iglink1">Sign Out</a></td>
        </tr>
    </table>
<?php } else { ?>
    <table width="750" border="0" id="table_ig_signin">
        <tr>
            <td align="left" class="igfont_small"><a href="../logbook/index.php" class="iglink1">Sign In</a></td>
            <td align="right" class="igfont_small">&nbsp;</td>
        </tr>
    </table>
<?php } ?>


<table width="750" border="0" align="center" cellpadding="2" cellspacing="1" class="table_main">
    <tr> 
        <th><a class="iglink2" href="index.php">Gallery</a> <?php echo $image_gallery->showBreadcrumb(); ?></th>
    </tr>
    <tr>
        <td valign="top">

        <?php include($page. '.php'); ?>

        </td>
    </tr>
</table>
</center>

</body>
</html>
