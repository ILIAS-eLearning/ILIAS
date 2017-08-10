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
        .demo {
            height: 100px;
            width: 100px;
            border: 3px solid black;
            background-color: #ccc;
            overflow: auto;
        }
	</style>
</head>
<body class="yui-skin-sam">
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a href="http://blog.davglass.com/">YUI: DragDrop</a></h1></div>
    <div id="bd">
        <div id="demo" class="demo">
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        Drag Group One<br>
        </div>

    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../yui-dev/build/yahoo/yahoo-min.js"></script> 
<script type="text/javascript" src="../yui-dev/build/dom/dom-min.js"></script> 
<script type="text/javascript" src="../yui-dev/build/event/event-min.js"></script> 
<script type="text/javascript" src="../yui-dev/build/logger/logger.js"></script> 
<script type="text/javascript" src="js/DragDropMgr.js"></script> 
<script type="text/javascript" src="js/DragDrop.js"></script> 
<script type="text/javascript" src="js/DD.js"></script> 
<script type="text/javascript" src="js/DDTarget.js"></script> 
<script type="text/javascript" src="js/DDProxy.js"></script> 
<script type="text/javascript">

YAHOO.util.DDM.mode = YAHOO.util.DDM.INTERSECT;

var dd, dd2, dd3;

YAHOO.util.Event.onDOMReady(function() {
    //YAHOO.util.DDM.clickTimeThresh = 5000;
    //YAHOO.util.DDM.clickPixelThresh = 5000;
    dd = new YAHOO.util.DD('demo', 'one', {
        events: {
            dragOver: false
        }
    });
    /*
    dd.on('dragDropEvent', function() {
        console.log('WooHoo.. Custom Event - dragDropEvent', arguments);
    });
    dd.on('mouseDownEvent', function() {
        console.log('WooHoo.. Custom Event - mouseDownEvent', arguments);
    });
    
    for (var i in dd.events) {
        dd.on(i + 'Event', function() {
            var args = arguments;
            setTimeout(function() {
                //console.log('dd:', args);
            }, 0);
        }, dd, true);
    }*/
    dd.on('dragEvent', function() {
        //console.log(arguments);
    });
    /*
    dd.onMouseDown = function(ev) {
        console.log('onMouseDown override');
        return false;
    };
    */
    dd.addToGroup('two');
    dd2 = new YAHOO.util.DD('demo2', 'two');
    for (var i in dd2.events) {
        dd2.on(i + 'Event', function() {
            //console.log('dd2: ', arguments);
        }, dd2, true);
    }
    //dd2.addToGroup('one');
    dd3 = new YAHOO.util.DD('demo3', '', {
        dragOnly: true
    });

    
    var divs = YAHOO.util.Dom.get('play').getElementsByTagName('div');
    for (var i = 0; i < divs.length; i++) {
        if (divs[i]) {
            new YAHOO.util.DDTarget(divs[i], ((i % 2) ? 'two' : 'one'));
        }
    }
});

YAHOO.util.Event.on(window, 'load', function() {
    var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true, height: '500px' });
    //logger.hide();
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
