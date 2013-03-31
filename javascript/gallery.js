
function getWindowHeight()
{
    // Because users will have different resolutions we return the 
    // browser window height so we can set things up accordingly.

    var bwidth  = 0;
    var bheight = 450;

    if(document.body && document.body.offsetWidth) 
    {
        bwidth = document.body.offsetWidth;
        bheight = document.body.offsetHeight;
    }

    if(document.compatMode=='CSS1Compat' &&
       document.documentElement &&
       document.documentElement.offsetWidth) 
    {
        bwidth = document.documentElement.offsetWidth;
        bheight = document.documentElement.offsetHeight;
    }

    if(window.innerWidth && window.innerHeight) 
    {
        bwidth = window.innerWidth;
        bheight = window.innerHeight;
    }

    return(bheight);

    //document.write("browser size:" + bwidth + " x " + bheight + "<br />");

    //var viewimg = document.getElementById("viewimg");
    //if(bheight > 750) { viewimg.style.width = '800px'; }
    //else              { viewimg.style.width = '600px'; }
}
