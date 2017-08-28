<?php
if (!$_GET['non']) {
    echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
}
?>
<html>
<head>
    <title>YUI: YAHOO.util.Resize</title>
    <link rel="stylesheet" type="text/css" href="../../build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" href="http://blog.davglass.com/wp-content/themes/davglass/style.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="../../build/assets/skins/sam/skin.css"> 
    <link rel="stylesheet" type="text/css" href="../../src/resize/assets/resize-core.css" />
    <link rel="stylesheet" type="text/css" href="../../src/resize/assets/skins/sam/resize-skin.css" />
    
    <style type="text/css" media="screen">
        p, h2 {
            margin: 1em;
        }
        #bd {
            position: relative;
        }
        #resize, #resize2, #resize3 {
            overflow: hidden;
            width: 600px;
            height: 300px;
            border: 1px solid #ccc;
            margin-bottom: 25px;
        }
        #resize_img {
            position: absolute;
            top: 300px;
            left: 800px;
        }
        #resize_img2 {
            position: absolute;
            top: 150px;
            left: 800px;
        }
        #img1_hd {
            position: absolute;
            left: 800px;
            top: 260px;
        }
        #img2_hd {
            position: absolute;
            left: 800px;
            top: 95px;
        }
	</style>
</head>
<body class="yui-skin-sam">
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a href="http://blog.davglass.com/">YUI: YAHOO.util.Resize</a></h1></div>
    <div id="bd">
        <h2 id="img1_hd">Resize: Animate, Ratio, Status, Draggable</h2>
        <p style="position: relative; border: 1px solid red;"><img src="pics/Photo1.jpg" id="resize_img"></p>
        <h2 id="img2_hd">Resize: Animate, AutoRatio, Status, Draggable, Knob Handles</h2>
        <img src="pics/Photo2.jpg" id="resize_img2">
        <h2>Resize: Proxy, Ghost, Ratio, Hover, Status</h2>
        <div id="resize">
        <iframe src="blank.htm" height="98%" width="98%"></iframe>        
        </div>
        <h2>Resize: Proxy, Animate, Ticks</h2>
        <div id="resize2">
            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper, rutrum ac, enim. Nullam pretium interdum metus. Ut in neque. Vivamus ut lorem vitae turpis porttitor tempor. Nam consectetuer est quis lacus. Mauris ut diam nec diam tincidunt eleifend. Vivamus magna. Praesent commodo egestas metus. Praesent condimentum bibendum metus. Sed sem sem, molestie et, venenatis eget, suscipit quis, dui. Morbi molestie, ipsum nec posuere lobortis, massa diam aliquet pede, tempor ultricies neque tortor sit amet nisi. Suspendisse vel quam in nisl dictum condimentum. Maecenas volutpat leo vitae leo. Nullam elit arcu, ullamcorper commodo, elementum nec, dictum nec, augue. Maecenas at tellus vitae ante fermentum elementum. Ut auctor ante et nisi. Suspendisse sagittis tristique eros.</p>
        </div>
        <h2>Resize: Proxy, Animate, Hidden Handles</h2>
        <div id="resize3">
            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper, rutrum ac, enim. Nullam pretium interdum metus. Ut in neque. Vivamus ut lorem vitae turpis porttitor tempor. Nam consectetuer est quis lacus. Mauris ut diam nec diam tincidunt eleifend. Vivamus magna. Praesent commodo egestas metus. Praesent condimentum bibendum metus. Sed sem sem, molestie et, venenatis eget, suscipit quis, dui. Morbi molestie, ipsum nec posuere lobortis, massa diam aliquet pede, tempor ultricies neque tortor sit amet nisi. Suspendisse vel quam in nisl dictum condimentum. Maecenas volutpat leo vitae leo. Nullam elit arcu, ullamcorper commodo, elementum nec, dictum nec, augue. Maecenas at tellus vitae ante fermentum elementum. Ut auctor ante et nisi. Suspendisse sagittis tristique eros.</p>
        </div>
    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../../build/yahoo/yahoo.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/dom/dom.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/event/event.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/animation/animation.js?bust=<?php echo(mktime()); ?>"></script>
<script type="text/javascript" src="../../build/dragdrop/dragdrop.js?bust=<?php echo(mktime()); ?>"></script>
<script type="text/javascript" src="../../build/element/element.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/logger/logger.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="js/resize.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript">
(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event;

    Event.on(window, 'load', function() {
        //var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true });
    });

    Event.onDOMReady(function() {
        //YAHOO.util.DDM.useShim = true;
        var resize_img = new YAHOO.util.Resize('resize_img', {
            width: '100px',
            height: '75px',
            handles: 't, b, r, l, bl, br, tl, tr',
            //knobHandles: true,
            //proxy: true,
            //handles: 'r, l',
            status: true,
            autoRatio: true//,
            //draggable: true
        });
        var resize_img2 = new YAHOO.util.Resize('resize_img2', {
            width: '100px',
            height: '75px',
            //handles: ['t', 'b', 'r', 'l', 'bl', 'br', 'tl', 'tr'],
            handles: 'All',
            proxy: true,
            status: true,
            autoRatio: true,
            //animate: true,
            animateDuration: 1.5,
            animateEasing: YAHOO.util.Easing.bounceOut,
            draggable: true,
            //useShim: true,
            knobHandles: true
        });
        resize_img2.on('startResize', function() {
            //this._proxy.innerHTML = '<img src="' + this.get('element').src + '" style="height: 100%; width: 100%;">';
            //Dom.setStyle(this._proxy.firstChild, 'opacity', '.25');
        }, resize_img2, true);

        var resize = new YAHOO.util.Resize('resize', {
            minHeight: 50,
            minWidth: 300,
            maxWidth: 900,
            maxHeight: 600,
            proxy: true,
            ghost: true,
            hover: true,
            ratio: true,
            status: true,
            useShim: true
        });

        var resize2 = new YAHOO.util.Resize('resize2', {
            //wrap: true
            //minHeight: 50,
            //minWidth: 300,
            //maxWidth: 900,
            //maxHeight: 600//,
            //proxy: true,
            //animate: true,
            //animateDuration: .5,
            //animateEasing: YAHOO.util.Easing.backIn,
            xTicks: 100,
            yTicks: 100
        });

        var resize3 = new YAHOO.util.Resize('resize3', {
            minHeight: 50,
            minWidth: 300,
            maxWidth: 900,
            maxHeight: 600,
            proxy: true,
            animate: true,
            hiddenHandles: true,
            animateDuration: .5,
            animateEasing: YAHOO.util.Easing.backIn
        });


    });

})();

</script>
</body>
</html>
<?php @include_once($_SERVER["DOCUMENT_ROOT"]."/wp-content/plugins/shortstat/inc.stats.php"); ?>
