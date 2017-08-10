<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: YAHOO.widget.ImageCropper</title>
    <link rel="stylesheet" type="text/css" href="../../build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" href="http://blog.davglass.com/wp-content/themes/davglass/style.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="../../build/assets/skins/sam/skin.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/resize/assets/skins/sam/resize.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/imagecropper/assets/skins/sam/imagecropper.css"> 

    <style type="text/css" media="screen">
        p, h2 {
            margin: 3em;
        }
	</style>
</head>
<body class="yui-skin-sam">
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a href="http://blog.davglass.com/">YUI: YAHOO.widget.ImageCropper</a></h1></div>
    <div id="bd">
        <p><img src="yui.jpg" id="crop1"></p>
        <!--p><img src="Palace.jpg" id="crop2"></p-->
    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../../build/yahoo-dom-event/yahoo-dom-event.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/animation/animation-min.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/dragdrop/dragdrop-min.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/element/element-min.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/logger/logger.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/resize/resize-debug.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="js/crop.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript">

(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event;
YAHOO.util.Event.on(window, 'load', function() {
    //var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true });
    //YAHOO.widget.Logger.enableBrowserConsole();
    //logger.hide();
});

    Event.onDOMReady(function() {
        crop = new YAHOO.widget.ImageCropper('crop1', {
            initialXY: [20, 20],
            keyTick: 5,
            shiftKeyTick: 50,
            initHeight: (Dom.get('crop1').height / 2),
            initWidth: (Dom.get('crop1').width / 2)
        });
        //crop2 = new YAHOO.widget.ImageCropper('crop2', {
        //    ratio: true
        //});
        /*
            var region = crop.getCropCoords();
            
            convert ClownFish.jpg -crop [width x height + left + top] ClownFish-new.jpg
        */
    });
})();

</script>
</body>
</html>
<?php @include_once($_SERVER["DOCUMENT_ROOT"]."/wp-content/plugins/shortstat/inc.stats.php"); ?>
