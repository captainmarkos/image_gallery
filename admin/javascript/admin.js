// ------------------------------------------------------ //
// admin.js                                  Mark Brettin //
//                                                        //
// Various functions for image_gallery - admin.           //
//                                                        //
// ------------------------------------------------------ //



// Setup some globals
// ------------------

var cleared_email_textbox = false;



function clearEmailTextBox()
{
    if(cleared_email_textbox == false)
    {
        cleared_email_textbox = true;
        var form = document.getElementById('igform');

        var str = form.email.value;
        if(str.search(/email/i) != -1)  // found
        {
            form.email.value = "";
	}
    }
}


function changeBox()
{
    document.getElementById('ps1').style.display = 'none';
    document.getElementById('ps2').style.display = '';
    document.getElementById('passwd').focus();
}


function restoreBox()
{
    if(document.getElementById('passwd').value == '')
    {
        document.getElementById('ps1').style.display = '';
        document.getElementById('ps2').style.display = 'none';
    }
}


function deleteCollection(collection_id)
{
    var str  = "Delete this collection?\n\n";
        str += "WARNING: All albums and images in this collection will also be deleted.";

    if(confirm(str))
    {
        window.location.href = 'index.php?deleteCollection&collection_id=' + collection_id;
    }
}


function deleteAlbum(album_id)
{
    var str  = "Delete this album?\n\n";
        str += "WARNING: All images in this album will also be deleted.";

    if(confirm(str))
    {
	window.location.href = 'index.php?deleteAlbum&album_id=' + album_id;
    }
}


function viewAlbum(collection_id)
{
    // View albums for a specified collection.
    if(collection_id != '')
    {
        window.location.href = "index.php?page=list_album&collection_id=" + collection_id;
    }
    else
    {
        window.location.href = "index.php?page=list_album";
    }
}


function viewImage(album_id) 
{
    // View images for a specified album.
    if(album_id != '') 
    {
	window.location.href = 'index.php?page=list_image&album_id=' + album_id;
    } 
    else 
    {
	window.location.href = 'index.php?page=list_image';
    }
}


function updateAlbumList(collection_id, page_name)
{
    // If an item is selected from the collection list then we need to update the album list
    // so that only albums from that collection are available in the album select list.

    if(collection_id != '') 
    {
	window.location.href = 'index.php?page=' + page_name + '&collection_id=' + collection_id;
    } 
    else 
    {
	window.location.href = 'index.php?page=' + page_name;
    }
}


function updateCollectionList(album_id, page_name)
{
    // If an item is selected from the album list then we need to update the collection list
    // so that only the collections to which the album belongs is in the collection list.

    if(album_id != '') 
    {
	window.location.href = 'index.php?page='  + page_name + '&album_id=' + album_id;
    } 
    else 
    {
	window.location.href = 'index.php?page=' + page_name;
    }
}


function deleteImage(album_id, image_id) 
{
    if(confirm('Delete this image?')) 
    {
       	window.location.href = 'index.php?page=list_image&delete&album_id=' + album_id + '&image_id=' + image_id;
    }
}


function viewLargeImage(imageName)
{
    imgWindow = window.open('', 'largeImage', "width=" + screen.availWidth + ",height="  + screen.availHeight + ",top=0,left=0,screenY=0,screenX=0,status=yes,scrollbars=yes,resizable=yes,menubar=no");
    imgWindow.focus();
    imgWindow.location.href = '../viewImage.php?type=glimage&name=' + imageName;
}
