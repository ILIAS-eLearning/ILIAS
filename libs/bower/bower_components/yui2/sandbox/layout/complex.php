<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: Layout Manager - Complex Nested</title>
    <link rel="stylesheet" type="text/css" href="../../build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" type="text/css" href="http://us.js2.yimg.com/us.js.yimg.com/i/ydn/yuiweb/css/dpsyntax-min-11.css">
    <link rel="stylesheet" type="text/css" href="../../build/resize/assets/skins/sam/resize.css">
    <link rel="stylesheet" type="text/css" href="../../src/layout/css/layout-core.css">
    <?php
    if ($_GET['skin']) {
        echo('<link rel="stylesheet" type="text/css" href="../../build/layout/assets/skins/sam/layout.css">'."\n");
    } else {
        echo('<link rel="stylesheet" type="text/css" href="css/dev.css">'."\n");
    }
    ?>

    <style type="text/css" media="screen">
	</style>
</head>
<body class="yui-skin-sam">
<script type="text/javascript" src="../../build/yahoo/yahoo.js"></script> 
<script type="text/javascript" src="../../build/dom/dom.js"></script> 
<script type="text/javascript" src="../../build/event/event.js"></script> 
<script type="text/javascript" src="../../build/animation/animation.js"></script> 
<script type="text/javascript" src="../../build/dragdrop/dragdrop.js"></script> 
<script type="text/javascript" src="../../build/selector/selector-beta.js"></script> 
<script type="text/javascript" src="../../build/element/element.js"></script> 
<script type="text/javascript" src="../../build/logger/logger-min.js"></script> 
<script src="http://us.js2.yimg.com/us.js.yimg.com/i/ydn/yuiweb/js/dpsyntax-min-2.js"></script>
<script type="text/javascript" src="../../build/resize/resize-min.js"></script> 
<script type="text/javascript" src="js/layout.js"></script> 
<script type="text/javascript" src="js/layoutunit.js"></script> 
<script type="text/javascript">

(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event;

    //var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true, height: '500px' });
    //logger.hide();
    //YAHOO.widget.Logger.enableBrowserConsole();
        

    Event.onDOMReady(function() {
        //YAHOO.util.DDM.useShim = true;
        var layout = new YAHOO.widget.Layout({
            units: [
                { position: 'top', height: 300, body: '', resize: true, proxy: true},
                { position: 'center', body: '' }
            ]
        });
        layout.on('render', function() {
            var t = layout.getUnitByPosition('top');
            var layout2 = new YAHOO.widget.Layout(t.body, {
                parent: layout,
                units: [
                    { position: 'left', width: 200, body: '', resize: true, proxy: true },
                    { position: 'center', body: '' }
                ]
            });
            layout2.on('render', function() {
                var l = layout2.getUnitByPosition('left');
                var layout11 = new YAHOO.widget.Layout(l.body, {
                    parent: layout2,
                    units: [
                        { position: 'top', height: 80, body: 'top 11', header: 'Top 11', resize: true, collapse: true, proxy: true },
                        { position: 'bottom', height: 80, body: 'Bottom 11', header: 'Bottom 11', resize: true, collapse: true, proxy: true },
                        { position: 'center', body: 'center 11', header: 'Center 11' }
                    ]
                });
                layout11.render();

                var t = layout2.getUnitByPosition('center');
                var layout3 = new YAHOO.widget.Layout(t.body, {
                    parent: layout2,
                    units: [
                        { position: 'left', width: 400, body: '', resize: true, proxy: true },
                        { position: 'center', body: '' }
                    ]
                });
                layout3.on('render', function() {
                    var t = layout3.getUnitByPosition('center');
                    var layout4 = new YAHOO.widget.Layout(t.body, {
                        parent: layout3,
                        units: [
                            { position: 'top', height: 100, body: 'top 4', header: 'Top 4', collapse: true, resize: true, proxy: true },
                            { position: 'right', width: 100, body: 'right 4', resize: true, proxy: true },
                            { position: 'center', body: '' }
                        ]
                    });
                    layout4.on('render', function() {
                        var t = layout4.getUnitByPosition('center');
                        var layout10 = new YAHOO.widget.Layout(t.body, {
                            parent: layout4,
                            units: [
                                { position: 'right', width: 100, body: 'right 10', resize: true, proxy: true },
                                { position: 'left', width: 100, body: 'left 10', resize: true, proxy: true },
                                { position: 'center', body: 'center 10', header: 'Center 10' }
                            ]
                        });
                        layout10.render();
                    });
                    layout4.render();

                    var l = layout3.getUnitByPosition('left');
                    var layout8 = new YAHOO.widget.Layout(l.body, {
                        parent: layout3,
                        units: [
                            { position: 'top', height: 40, body: 'top 8', resize: true, proxy: true },
                            { position: 'right', width: 70, body: 'right 8', resize: true, proxy: true },
                            { position: 'center', body: 'center 8', header: 'Center 8' }
                        ]
                    });
                    layout8.render();
                });
                layout3.render();
            });
            layout2.render();

            var c = layout.getUnitByPosition('center');
            var layout5 = new YAHOO.widget.Layout(c.body, {
                parent: layout,
                units: [
                    { position: 'bottom', height: 40, body: 'bottom 5', resize: true, proxy: true },
                    { position: 'right', width: 200, body: '', resize: true, proxy: true },
                    { position: 'center', body: '' }
                ]
            });
            layout5.on('render', function() {
                var r = layout5.getUnitByPosition('right');
                var layout12 = new YAHOO.widget.Layout(r.body, {
                    parent: layout5,
                    units: [
                        { position: 'top', height: 150, body: 'top 12', header: 'Top 12', collapse: true, resize: true, proxy: true },
                        { position: 'center', body: 'center 12' }
                    ]
                });
                layout12.render();

                var c = layout5.getUnitByPosition('center');
                var layout6 = new YAHOO.widget.Layout(c.body, {
                    parent: layout5,
                    units: [
                        { position: 'top', height: 40, body: 'top 6', resize: true, proxy: true },
                        { position: 'right', width: 200, body: 'right 6', header: 'Right 6', collapse: true, resize: true, proxy: true },
                        { position: 'center', body: '' }
                    ]
                });
                layout6.on('render', function() {
                    var c = layout6.getUnitByPosition('center');
                    var layout7 = new YAHOO.widget.Layout(c.body, {
                        parent: layout6,
                        units: [
                            { position: 'top', height: 40, body: 'top 7', resize: true, proxy: true },
                            { position: 'left', width: 200, body: 'left 7', header: 'Left 7', collapse: true, resize: true, proxy: true },
                            { position: 'center', body: '' }
                        ]
                    });
                    layout7.on('render', function() {
                        var c = layout7.getUnitByPosition('center');
                        var layout9 = new YAHOO.widget.Layout(c.body, {
                            parent: layout7,
                            units: [
                                { position: 'bottom', height: 40, body: 'bottom 9', resize: true, proxy: true },
                                { position: 'right', width: 200, body: 'right 9', header: 'Right 9', collapse: true, resize: true, proxy: true },
                                { position: 'center', body: 'center 9', header: 'Center 9' }
                            ]
                        });
                        layout9.render();
                    });
                    layout7.render();
                });
                layout6.render();
            });
            layout5.render();
        });
        layout.render();
    });
})();

</script>
</body>
</html>
