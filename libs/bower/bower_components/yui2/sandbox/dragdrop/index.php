<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: DragDrop</title>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.3.0/build/reset-fonts-grids/reset-fonts-grids.css"> 
        <link rel="stylesheet" type="text/css" href="../../build/assets/skins/sam/logger.css"> 
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
        <div id="demo" class="demo">Drag Group One</div>
        <div id="demo2" class="demo">Drag Group Two</div>
        <div id="demo3" class="demo">Drag Three (dragOnly)</div>
        <div id="demo4" class="demo">Drag Four (Proxy)</div>
        <p><button id="toggle">Shim Off</button><button id="debug" disabled>Debug Shim: Off</button></p>
        <iframe src="blank.htm" height="300" width="300"></iframe>        
        <div id="play">
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
            <div class="demoDrop"></div>
        </div>

    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../../build/yahoo/yahoo-min.js"></script> 
<script type="text/javascript" src="../../build/dom/dom-min.js"></script>
<script type="text/javascript" src="../../build/event/event.js"></script> 
<script type="text/javascript" src="../../build/logger/logger-min.js"></script> 
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
    YAHOO.util.Event.on('toggle', 'click', function() {
        YAHOO.util.DDM.useShim = !YAHOO.util.DDM.useShim;
        YAHOO.util.Dom.get('toggle').innerHTML = 'Shim ' + ((YAHOO.util.DDM.useShim) ? 'On' : 'Off');
        YAHOO.util.Dom.get('debug').disabled = ((YAHOO.util.DDM.useShim) ? false : true);
    });
    YAHOO.util.Event.on('debug', 'click', function() {
        YAHOO.util.DDM._debugShim = !YAHOO.util.DDM._debugShim;
        YAHOO.util.Dom.get('debug').innerHTML = 'Debug Shim: ' + ((YAHOO.util.DDM._debugShim) ? 'On' : 'Off');
    });

    dd = new YAHOO.util.DD('demo', 'one');
    /*
    dd.on('dragOverEvent', function() {
        console.log('WooHoo.. Custom Event - dragOverEvent', arguments);
    });
    */
    /*
    dd.on('dragDropEvent', function() {
        console.log('WooHoo.. Custom Event - dragDropEvent', arguments);
    });
    dd.on('mouseDownEvent', function() {
        console.log('WooHoo.. Custom Event - mouseDownEvent', arguments);
    });
    /*
    for (var i in dd.events) {
        dd.on(i + 'Event', function() {
            var args = arguments;
            setTimeout(function() {
                //console.log('dd:', args);
            }, 0);
        }, dd, true);
    }*/
    dd.on('dragEvent', function() {
        //console.log('Drag: ', arguments);
    });
    /*
    dd.onMouseDown = function(ev) {
        console.log('onMouseDown override');
        return false;
    };
    */
    dd.addToGroup('two');
    dd2 = new YAHOO.util.DD('demo2', 'two', { useShim: true });
    for (var i in dd2.events) {
        dd2.on(i + 'Event', function() {
            //console.log('dd2: ', arguments);
        }, dd2, true);
    }
    //dd2.addToGroup('one');
    dd3 = new YAHOO.util.DD('demo3', '', {
        dragOnly: true
    });

    dd4 = new YAHOO.util.DDProxy('demo4', '', {
        useShim: false
    });

    
    var divs = YAHOO.util.Dom.get('play').getElementsByTagName('div');
    for (var i = 0; i < divs.length; i++) {
        if (divs[i]) {
            var tar = new YAHOO.util.DDTarget(divs[i], ((i % 2) ? 'two' : 'one'));
        }
    }
});

YAHOO.util.Event.on(window, 'load', function() {
    /*
    var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true, height: '500px' });
    logger.hide();
    YAHOO.widget.Logger.enableBrowserConsole();
    */
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
