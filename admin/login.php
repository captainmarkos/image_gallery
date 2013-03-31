<?php

    require_once '../lib/config.php';
    require_once '../lib/functions.php';

    $errMsg = '';
    $email = isset($_POST['email']) && ($_POST['email'] != '') ? $_POST['email'] : '';
    $passwd = isset($_POST['passwd']) && ($_POST['passwd'] != '') ? $_POST['passwd'] : '';
    $login = isset($_POST['login']) && ($_POST['login'] != '') ? $_POST['login'] : '';


    if($email != '' && $login == 'Log In')
    {
        $sql  = "SELECT * FROM users WHERE email='$email' AND passwd=PASSWORD('$passwd')";
        $result = mysql_query($sql) or die("SQL: $sql<br>\nERROR: " . mysql_error());
        if(mysql_num_rows($result) != 0)
        {
            // We have a good log in.
            $_SESSION['email'] = $email;
            $_SESSION['isLogin'] = true;
	    header('Location: index.php?page=list_collection');
	    exit;
        }
        else { $errMsg = "<font color=\"#cd0000\"><b>Invalid User ID or Password</b></font>"; }
    }
    else
    {
        if((empty($_SESSION['manage_photos']) == false) && $_SESSION['manage_photos'] == true)
        {
            $_SESSION['isLogin'] = true;
	    header('Location: index.php?page=list_collection');
	    exit;
	}
    }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Image Gallery - Admin Login</title>
<link rel="stylesheet" type="text/css" href="admin.css" />
<script type="text/javascript" src="javascript/admin.js"></script>
</head>
<body>
<br />
<br />

<center>
<table width="750" border="0" cellpadding="0" cellspacing="0" class="table_login">
    <tr>
        <td>
            <form action="" method="post" name="igform" id="igform">
            <input type="text" class="myinput" value="Email" name="email" size="24" onfocus="clearEmailTextBox();" />

            <span id="ps1">
                <input type="text" class="myinput" value="Password" name="passwd_tmp" size="12" onfocus="changeBox();" />
            </span>

            <span id="ps2" style="display: none;">
                <input type="password" class="myinput" value="" name="passwd" id="passwd" size="12" onblur="restoreBox();" />
            </span>

            <input type="submit" value="Log In" id="login" name="login" class="mybutton" />
            </form>
        </td>
    </tr>
</table>


<table width="750" border="0" cellpadding="2" cellspacing="1" class="table_main">
    <tr> 
        <th>Gallery : Administration <?php if($errMsg != '') { print ": $errMsg"; } ?></th>
    </tr>
    <tr>
        <td>
<center>Understanding the image gallery layout.</center>
<br />
To help organize and manage your uploaded images, the following will be helpful.
Within the image gallery there are collections (one per user), albums and images.  
Let us describe the structure in this way:
<ul>
    <li>Each user can create and have many albums.</li>
    <li>An album may contain many images.</li>
    <li>The gallery may contain many collections.</li>
</ul>
When you create an album you must upload an image which becomes your album thumbnail. 
When you upload your images, thumbnails are automagically created.
<br />
<br />
More useful facts:
<ul>
    <li>Links can be shared so others may view the collections, albums or images.</li>
    <li>If image file sizes are large, uploading images will take longer.</li>
    <li>The max number of images you can upload at once is five (5).</li>
</ul>
        </td>
    </tr>
</table>

</center>

</body>
</html>
