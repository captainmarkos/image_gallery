<?php

require_once('db_helper.php');

class ImageGallery {
    private $dbh;
    private $db_helper;

    const MISSING_DB_HANDLE = "DB handle is NULL";

    public function __construct($dbh) {
        if(!$dbh) {
            throw new InvalidArgumentException(self::MISSING_DB_HANDLE);
        }
        $this->dbh = $dbh;
        $this->db_helper = new DBHelper($dbh);
    }

    public function getImageURL($collection_id, $album_id, $image_id) {
        // Return the URL of the image requested.
        $image_url = '';

        if($collection_id == '' || $album_id == '' || $image_id == '') {
            $retval = "collection_id = $collection_id album_id = $album_id image_id = $image_id";
            return($retval);
        }
        else {
            $sql  = "SELECT images.id, images.album_id, images.title, ";
            $sql .= "images.description, images.image, ";
            $sql .= "albums.collection_id, albums.name ";
            $sql .= "FROM images, albums ";
            $sql .= "WHERE images.id=? AND images.album_id=albums.id";
            $sql = $this->db_helper->construct_secure_query($sql, $image_id);
	    $result = $this->dbh->query($sql) or die('Error, get image info failed. ' . $this->dbh->error());

            if($result->num_rows == 0) {
                return("image_id not found in database");
	    }
            else {
                $row = $result->fetch_assoc();
                $image_url = $this->getImage('glimage', $row['image']);
            }
        }
        return($image_url);
    }

    public function uploadImage($inputName, $uploadDir) {
        // Upload an image and create the thumbnail. The thumbnail is stored
        // under the thumbnail sub-directory of $uploadDir.  Also create a
        // display image which is most likey smaller than the original and
        // store that under the display_images sub-directory of $uploadDir.
        //
        // Return the uploaded image name, thumbnail name and display image name.
        $image = $_FILES[$inputName];
        $image_name = '';
        $thumbnail_name = '';
        $display_image_name = '';

        // if a file is given
        if(trim($image['tmp_name']) != '') {
            $ext = substr(strrchr($image['name'], "."), 1);
            $ext = strtolower($ext);

            // Generate a random new file name to avoid name conflict
            // then save the image under the new file name
            $image_name = md5(rand() * time()) . ".$ext";

            $result = move_uploaded_file($image['tmp_name'], $uploadDir . $image_name);

            $full_image_name = $uploadDir . $image_name;
            if($result == true) {
                // create thumbnail
                $thumbnail_name = IMAGES_IMG_THUMBS_DIR . "tn_" . $image_name;
                $display_image_name = IMAGES_IMG_DISPLAY_DIR . "ds_" . $image_name;

                $result = $this->createThumbnail($full_image_name, $thumbnail_name, THUMBNAIL_WIDTH);
                if($result == '') {
                    unlink($full_image_name);  // create thumbnail failed, delete the image
                    $image_name = $thumbnail_name = $display_image_name = '';
                } 
                else { $thumbnail_name = $result; } 

                $size = $this->getimagesize($full_image_name);  // Get the image size and resize it if necessary.
                if($size[0] > DISPLAY_IMAGE_WIDTH) {
                    // Create the display image.
                    $result = $this->createThumbnail($full_image_name, $display_image_name, DISPLAY_IMAGE_WIDTH);
                    if($result == '') {
                        unlink($full_image_name);        // create display image failed, delete the image
                        $image_name = $thumbnail_name = $display_image_name = '';
                    } 
                    else { $display_image_name = $result; }
                }

                if($size[0] > IMAGE_WIDTH) {
                    // See if we need to resize the actual image.
                    $result = $this->createThumbnail($full_image_name, $full_image_name, IMAGE_WIDTH);
                    if($result == '') {
                        unlink($full_image_name);        // create actual image failed, delete the image
                        $image_name = $thumbnail_name = $display_image_name = '';
                    }
                    else { $image_name = $result; }
                }
            } 
            else {
                // failed uploading the image
                $image_name = $thumbnail_name = $display_image_name = '';
            }
        }
        $items = array(
            'image' => $image_name,
            'thumbnail' => $thumbnail_name,
            'display_image' => $display_image_name
        );
        return $items;
    }

    public function multiImageUploader($input, $index, $uploadDir) {
        // Upload images and create the thumbnails. The thumbnail is stored 
        // under the thumbnail sub-directory of $uploadDir.  Also create the 
        // display images which are most likey smaller than the original and
        // store them under the display_images sub-directory of $uploadDir.
        //
        // This funcion is used when $input is an array of image arrays from
        // $_FILES['imagefiles'][][] therfore $input = $_FILES['imagefiles'].
        //
        // Return the uploaded image name and the thumbnail also.
        $image = $input;
        $image_name = '';
        $thumbnail_name = '';
        $display_image_name = '';

        // if a file is given
        if(trim($image['tmp_name'][$index]) != '') {
            $ext = substr(strrchr($image['name'][$index], "."), 1); 
            $ext = strtolower($ext);

            // Generate a random new file name to avoid name conflict
            // then save the image under the new file name
            $image_name = md5(rand() * time()) . ".$ext";

            $result = move_uploaded_file($image['tmp_name'][$index], $uploadDir . $image_name);

            $full_image_name = $uploadDir . $image_name;

            if($result == true) {
                // create thumbnail
                $thumbnail_name = IMAGES_IMG_THUMBS_DIR . "tn_" . $image_name;
                $display_image_name = IMAGES_IMG_DISPLAY_DIR . "ds_" . $image_name;

                $result = $this->createThumbnail($full_image_name, $thumbnail_name, THUMBNAIL_WIDTH);

                if($result == '') {
                    unlink($full_image_name);  // create thumbnail failed, delete the image
                    $image_name = $thumbnail_name = $display_image_name = '';
                } 
                else { $thumbnail_name = $result; }

                $size = getimagesize($full_image_name);  // Get the image size and resize it if necessary.
                if($size[0] > DISPLAY_IMAGE_WIDTH) {
                    // Create the display image.
                    $result = $this->createThumbnail($full_image_name, $display_image_name, DISPLAY_IMAGE_WIDTH);
                    if(!$result) {
                        unlink($full_image_name); // create display image failed, delete the image
                        $image_name = $thumbnail_name = $display_image_name = '';
                    } 
                    else { $display_image_name = $result; }
                }

                // See if we need to resize the actual image.
                if($size[0] > IMAGE_WIDTH) {
                    $result = $this->createThumbnail($full_image_name, $full_image_name, IMAGE_WIDTH);
                    if(!$result) {
                        unlink($full_image_name);        // create actual image failed, delete the image
                        $image_name = $thumbnail_name = $display_image_name = '';
                    }
                    else { $image_name = $result; }
                }
            } 
            else {
                // failed uploading the image
                $image_name = $thumbnail_name = $display_image_name = '';
            }
        }
        $items = array(
            'image' => $image_name,
            'thumbnail' => $thumbnail_name,
            'display_image' => $display_image_name
        );
        return $items;
    }

    public function createThumbnail($srcFile, $destFile, $width, $quality = 100) {
        // Create a thumbnail of $srcFile and save it to $destFile.
        // The thumbnail will be $width pixels.
        $thumbnail = '';

        if(file_exists($srcFile) && isset($destFile)) {
            $size = $this->getimagesize($srcFile);
            $w    = number_format($width, 0, ',', '');
            $h    = number_format(($size[1] / $size[0]) * $width, 0, ',', '');
            $thumbnail = $this->copyImage($srcFile, $destFile, $w, $h, $quality);
        }

        // return the thumbnail file name on sucess or blank on fail
        return basename($thumbnail);
    }

    public function copyImage($srcFile, $destFile, $w, $h, $quality = 100) {
        // Copy an image to a destination file. The destination image
        // size will be $w X $h pixels.
        $tmpSrc  = pathinfo(strtolower($srcFile));
        $tmpDest = pathinfo(strtolower($destFile));
        $size    = getimagesize($srcFile);

        if($tmpDest['extension'] == "gif" || $tmpDest['extension'] == "jpg") {
            $destFile  = substr_replace($destFile, 'jpg', -3);        // substr_replace(string, replacement, start)
            $dest      = imagecreatetruecolor($w, $h);
        } 
        elseif($tmpDest['extension'] == "png") {
            $dest = imagecreatetruecolor($w, $h);
        } 
        else return(false); 

        switch($size[2]) {
            case 1:       //GIF
                $src = imagecreatefromgif($srcFile);
                break;
            case 2:       //JPEG
                $src = imagecreatefromjpeg($srcFile);
                break;
            case 3:       //PNG
                $src = imagecreatefrompng($srcFile);
                break;
            default:
                return(false);
                break;
        }

        imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);

        switch($size[2]) {
            case 1:
            case 2:
                imagejpeg($dest, $destFile, $quality);
                break;
            case 3:
               imagepng($dest,$destFile);
        }
        return($destFile);
    }

    public function checkLogin() {
        // Check if the user is logged in or not
        if(!isset($_SESSION['isLogin']) || ($_SESSION['isLogin'] == false)) {
            header('Location: login.php');
            exit;
        }
    }

    public function checkSessionVars() {
        if(empty($_SESSION['gallery_id']) == true) {
            echo "Important session variables are not set. ";
            echo "Your session may have timed out.<br /><br />";
            echo "Please log back in and try again.";
	    exit;
        }
    }

    public function getPagingLink($totalResults, $pageNumber, $itemsPerPage = 10, $strGet = '') {
        // Create the link for moving from one page to another
        $pagingLink = '';
        $totalPages = ceil($totalResults / $itemsPerPage);

        // how many link pages to show
        $numLinks = 10;

        // create the paging links only if we have more than one page of results
        if($totalPages > 1) {
            $self = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ;

            // print 'previous' link only if were not on page one
            if($pageNumber > 1) {
                $page = $pageNumber - 1;
                if($page > 1) {
                    $prev = " <a class=\"iglink2\" href=\"$self?pageNum=$page&$strGet\">[Prev]</a> ";
                } 
                else {
                    $prev = " <a class=\"iglink2\" href=\"$self?$strGet\">[Prev]</a> ";
                }	

                $first = " <a class=\"iglink2\" href=\"$self?$strGet\">[First]</a> ";
            } 
            else {
                $prev  = '';     // we're on page one, don't show 'previous' link
                $first = '';     // nor 'first page' link
            }

            // print 'next' link only if we are not on the last page
            if($pageNumber < $totalPages) {
                $page = $pageNumber + 1;
                $next = " <a class=\"iglink2\" href=\"$self?pageNum=$page&$strGet\">[Next]</a> ";
                $last = " <a class=\"iglink2\" href=\"$self?pageNum=$totalPages&$strGet\">[Last]</a> ";
            } 
            else {
                $next = ''; // we're on the last page, don't show 'next' link
                $last = ''; // nor 'last page' link
            }

            $start = $pageNumber - ($pageNumber % $numLinks) + 1;
            $end   = $start + $numLinks - 1;		

            $end   = min($totalPages, $end);

            $pagingLink = array();
            for($page = $start; $page <= $end; $page++)	{
                if($page == $pageNumber) {
                    $pagingLink[] = " $page ";   // no need to create a link to current page
                } 
                else {
                    if($page == 1) {
                        $pagingLink[] = " <a class=\"iglink2\" href=\"$self?$strGet\">$page</a> ";
                    } 
                    else {	
                        $pagingLink[] = " <a class=\"iglink2\" href=\"$self?pageNum=$page&$strGet\">$page</a> ";
                    }	
                }
            }

            $pagingLink = implode(' | ', $pagingLink);

            // return the page navigation link
            $pagingLink = $first . $prev . $pagingLink . $next . $last;
        }

        return($pagingLink);
    }

    public function showBreadcrumb() {
        // Display the breadcrumb navigation on top of the gallery page
        $separator = " - ";

        if(isset($_REQUEST['page'])) {
            $page = $_REQUEST['page'];

            if($page == 'list_collection') {
                // Page is displaying all the collections for a gallery.
                $gallery_id = $_REQUEST['gallery_id'];

                // Display the gallery name.
                $sql = "SELECT name AS gallery_name FROM galleries WHERE id=?";
                $sql = $this->db_helper->construct_secure_query($sql, $gallery_id);
                $result = $this->dbh->query($sql) or die('showBreadcrumb() ERROR: get gallery name failed. ' . $this->dbh->error());
                $row = $result->fetch_assoc();
                echo $separator . $row['gallery_name'];
            }
            else if($page == 'list_album') {
                // Page is displaying all the albums for a collection.
                $gallery_id    = $_REQUEST['gallery_id'];
                $collection_id = $_REQUEST['collection_id'];

                // Provide a link to the gallery.
                $sql = "SELECT name AS gallery_name FROM galleries WHERE id=?";
                $sql = $this->db_helper->construct_secure_query($sql, $gallery_id);
                $result = $this->dbh->query($sql) or die('showBreadcrumb() ERROR: get gallery name failed. ' . $this->dbh->error());
                $row = $result->fetch_assoc();

                $qs = "?page=list_collection&gallery_id=$gallery_id";

                echo $separator . '<a class="iglink2" href="index.php' . $qs . '">';
                echo $row['gallery_name'] . '</a>';

                // Display the collection name.
                $sql = "SELECT name AS collection_name FROM collections WHERE id=?";
                $sql = $this->db_helper->construct_secure_query($sql, $collection_id);
                $result = $this->dbh->query($sql) or die('showBreadcrumb() ERROR: get collection name failed. ' . $this->dbh->error());
                $row = $result->fetch_assoc();
                echo $separator . $row['collection_name'];
            }
            else if($page == 'list_image') {
                // Page is displaying all the images for an album.
                $gallery_id    = $_REQUEST['gallery_id'];
                $collection_id = $_REQUEST['collection_id'];
                $album_id      = $_REQUEST['album_id'];

                // Provide a link to the gallery.
                $sql = "SELECT name AS gallery_name FROM galleries WHERE id=?";
                $sql = $this->db_helper->construct_secure_query($sql, $gallery_id);
                $result = $this->dbh->query($sql) or die('showBreadcrumb() ERROR: get gallery name failed. ' . $this->dbh->error());
	        $row = $result->fetch_assoc();

                $qs = "?page=list_collection&gallery_id=$gallery_id";

                echo $separator . '<a class="iglink2" href="index.php' . $qs . '">';
                echo $row['gallery_name'] . '</a>';

                // Provide a link to the collection.
                $sql = "SELECT name AS collection_name FROM collections WHERE id=?";
                $sql = $this->db_helper->construct_secure_query($sql, $collection_id);
                $result = $this->dbh->query($sql) or die('showBreadcrumb() ERROR: get collection name failed. ' . $this->dbh->error());
                $row = $result->fetch_assoc();

                $qs = "?page=list_album&gallery_id=$gallery_id&collection_id=$collection_id";

                echo $separator .'<a class="iglink2" href="index.php' . $qs . '">';
                echo $row['collection_name'] . '</a>';

                // Display the album name.
                $sql = "SELECT name AS album_name FROM albums WHERE id=?";
                $sql = $this->db_helper->construct_secure_query($sql, $album_id);
	        $result = $this->dbh->query($sql) or die('showBreadcrumb() ERROR: get album name failed. ' . $this->dbh->error());
                $row = $result->fetch_assoc();
                echo $separator . $row['album_name'];
            }
        }
    }

    public function getImage($type, $name) {
        // All images in the gallery must be stored inside the webroot directory.
        // To display the image we must provide the image type and the image name.
        // This function will return the path to the image.
        $filePath = '';

        if($type == 'gallery') {
            $filePath = WWWROOT_GALLERY_IMG_DIR . $name;
        } 
        else if($type == 'collection') {
            $filePath = WWWROOT_COLLECTION_IMG_DIR . $name;
        } 
        else if($type == 'album') {
            $filePath = WWWROOT_ALBUM_IMG_DIR . $name;
        } 
        else if($type == 'glimage') {
            $filePath = WWWROOT_IMAGES_IMG_DIR . $name;
        } 
        else if($type == 'glthumbnail') {
            $filePath = WWWROOT_IMAGES_IMG_THUMBS_DIR . $name;
        } 
        else {
            $filePath = "";    // invalid image type
        }
        return($filePath);
    }

    public function set_imageviews($filename) {
        $newcount = 0;
        $sql  = "SELECT id, filename, counter ";
        $sql .= "FROM imageviews WHERE filename=?";
        $sql = $this->db_helper->construct_secure_query($sql, $filename);
        $res = $this->dbh->query($sql) or die('Error: update_imageviews() failed --> ' . $this->dbh->error());
        if($res->num_rows == 0) {
            $sql = "INSERT INTO imageviews (filename, counter) VALUES (?, '1')";
            $sql = $this->db_helper->construct_secure_query($sql, $filename);
            $res = $this->dbh->query($sql) or die('Error: update_imageviews() failed --> ' . $this->dbh->error());
            $newcount = 1;
        }
        else {
            $row = $res->fetch_assoc();
            $newcount = $row['counter'] +1;
            $sql = "UPDATE imageviews SET counter=? WHERE id=?";
            $params = array($newcount, $row['id']);
            $sql = $this->db_helper->construct_secure_query($sql, $params);
            $res = $this->dbh->query($sql) or die('Error: update_imageviews() failed --> ' . $this->dbh->error());
        }
        return($newcount);
    }

    public function get_imageviews($filename) {
        // Returns the number of views for the given filename.
        $sql = "SELECT filename, counter FROM imageviews WHERE filename=?";
        $sql = $this->db_helper->construct_secure_query($sql, $filename);
        $res = $this->dbh->query($sql) or die('Error: get_imageviews() failed --> ' . $this->dbh->error());
        if($res->num_rows == 0) { return(0); }
        $row = $res->fetch_assoc();
        return($row['counter']);
    }

    public function getCollectionName($collection_id) {
        $retval = '';

        $sql = "SELECT name FROM collections WHERE id=?";
        $sql = $this->db_helper->construct_secure_query($sql, $collection_id);
        $res = $this->dbh->query($sql) or die('ERROR: getCollectionName() failed. ' . $this->dbh->error());
        while($row = $res->fetch_assoc()) { 
            return $row['name']; 
        }
    }

    public function delete_images($name, $id) {
        // This function will unlink image files from the appropriate directory.
        if($name == 'IMAGES') {
            // delete images and thumbnails for the given album id
            $sql = "SELECT image, thumbnail, display_image FROM images WHERE album_id=?";
            $sql = $this->db_helper->construct_secure_query($sql, $id);
            $res_i = $this->dbh->query($sql) or die('ERROR delete_images() failed.<br />' . $sql . '<br />' . $this->dbh->error());
            while($data1 = mysql_fetch_assoc($res_i)) {
                unlink(IMAGES_IMG_DIR . $data1['image']);
                unlink(IMAGES_IMG_THUMBS_DIR . $data1['thumbnail']);
                unlink(IMAGES_IMG_DISPLAY_DIR . $data1['display_image']);
            }
            $sql = "DELETE FROM images WHERE album_id=?";
            $sql = $this->db_helper->construct_secure_query($sql, $id);
            $this->dbh->query($sql) or die('Delete collection images failed. ' . $this->dbh->error());
        }
        elseif($name == 'ALBUM') {
            // delete album images for this given collection id
            $sql = "SELECT image FROM albums WHERE collection_id=?";
            $sql = $this->db_helper->construct_secure_query($sql, $id);
            $res_a = $this->dbh->query($sql) or die('ERROR delete_images() failed.<br />' . $sql . '<br />' . $this->dbh->error());
            while($data2 = mysql_fetch_assoc($res_a)) {
                unlink(ALBUM_IMG_DIR . $data2['image']);
            }
            $sql = "DELETE FROM albums WHERE collection_id=?";
            $sql = $this->db_helper->construct_secure_query($sql, $id);
            $this->dbh->query($sql) or die('Delete collection failed. ' . $this->dbh->error());
        }
    }
}

?>
