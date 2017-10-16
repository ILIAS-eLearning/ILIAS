<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: DragDrop</title>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.3.0/build/reset-fonts-grids/reset-fonts-grids.css"> 
        <link rel="stylesheet" type="text/css" href="../yui-dev/build/assets/skins/sam/logger.css"> 
    <link rel="stylesheet" href="http://blog.davglass.com/wp-content/themes/davglass/style.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="http://us.js2.yimg.com/us.js.yimg.com/i/ydn/yuiweb/css/dpsyntax-min-11.css">
    <style type="text/css" media="screen">
        p, h2 {
            margin: 1em;
        }
        #play {
            position: absolute;
            top: 100px;
            right: 150px;
            width: 60%;
            border: 1px solid black;
        }
        .demo {
            z-index: 100;
        }
        .demo, .demoDrop {
            height: 100px;
            width: 100px;
            border: 3px solid black;
            background-color: #ccc;
        }
        .demoDrop {
            float: left;
            margin: 3px;
        }
        .yui-dd-dragging .yui-dd-draggable-drag {
            opacity: .5;
            z-index: 999;
        }
        .yui-dd-dragging .yui-dd-drag-valid-target {
            background-color: green;
        }
        .yui-dd-dragging .yui-dd-target {
            border: 3px solid blue;
        }
        .yui-dd-dragging .yui-dd-valid-target {
            border: 3px solid green;
        }
        .yui-dd-dragging .yui-dd-invalid-target {
            border: 3px solid red;
        }
        .yui-dd-dragging .yui-dd-over-valid-target {
            background-color: green;
        }
        .yui-dd-dragging .yui-dd-over-invalid-target {
            background-color: red;
        }
        .yui-dd-draggable {
            cursor: move;
        }
	</style>
</head>
<body class="yui-skin-sam">
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a href="http://blog.davglass.com/">YUI: DragDrop</a></h1></div>
    <div id="bd">
        <select>
            <option>One </option>
            <option>One </option>
            <option>One </option>
        </select>
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
        <div id="demo" class="demo">Drag Group One</div>
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
 <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse justo nibh, pharetra at, adipiscing ullamcorper.</p>        
    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../yui-dev/build/yahoo/yahoo-min.js"></script> 
<script type="text/javascript" src="../yui-dev/build/dom/dom-min.js"></script> 
<script type="text/javascript" src="../yui-dev/build/event/event-min.js"></script> 
<script type="text/javascript" src="../yui-dev/build/logger/logger-min.js"></script> 
<script type="text/javascript" src="js/DragDropMgr.js"></script> 
<script type="text/javascript" src="js/DragDrop.js"></script> 
<script type="text/javascript" src="js/DD.js"></script> 
<script type="text/javascript" src="js/DDTarget.js"></script> 
<script type="text/javascript" src="js/DDProxy.js"></script> 
<script type="text/javascript">

var dd, dd2, dd3;

YAHOO.util.Event.onDOMReady(function() {
    dd = new YAHOO.util.DDProxy('demo', 'one');
    dd.endDrag = function() { 
        alert(YAHOO.lang.dump(arguments)); 
    }; 
    
});

YAHOO.util.Event.on(window, 'load', function() {
    var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true, height: '500px' });
    logger.hide();
    YAHOO.widget.Logger.enableBrowserConsole();

    YAHOO.util.Event.on(window, 'keypress', function(ev) {
        if (ev.keyCode == 27) {
            YAHOO.util.DragDropMgr.stopDrag(null, true);
        }
    });

});

</script>
</body>
</html>
<?php @include_once($_SERVER["DOCUMENT_ROOT"]."/wp-content/plugins/shortstat/inc.stats.php"); ?>
