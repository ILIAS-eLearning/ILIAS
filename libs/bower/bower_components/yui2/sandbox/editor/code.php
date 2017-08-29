<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: YAHOO.widget.Editor</title>
    <link rel="stylesheet" type="text/css" href="../yui-dev/build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" href="http://blog.davglass.com/wp-content/themes/davglass/style.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="../yui-dev/build/assets/skins/sam/menu.css"> 
    <link rel="stylesheet" type="text/css" href="../yui-dev/build/assets/skins/sam/container.css"> 
    <link rel="stylesheet" type="text/css" href="../yui-dev/build/assets/skins/sam/calendar.css"> 
    <link rel="stylesheet" type="text/css" href="../yui-dev/build/assets/skins/sam/logger.css"> 
    <link rel="stylesheet" type="text/css" href="../yui-dev/build/assets/skins/sam/button.css"> 
    <link rel="stylesheet" type="text/css" href="css/editor-core.css"> 
    <link rel="stylesheet" type="text/css" href="css/skin-sam.css">
    <style>
        #form1 {
            margin: 2em;
        }
        .yui-toolbar-subcont {
            display: none;
        }

    </style>
</head>
<body class="yui-skin-sam">
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a>YAHOO.widget.Editor</a></h1></div>
    <div id="bd">
        <form method="post" action="index.php" id="form1">
        <textarea id="editor" name="editor" rows="20" cols="75"></textarea>
        </form>
    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../yui-dev/build/yahoo-dom-event/yahoo-dom-event.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../yui-dev/build/element/element-min.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../yui-dev/build/logger/logger.js?bust=<?php echo(mktime()); ?>"></script> 
<script src="js/toolbar-button.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/toolbar.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/simple-editor.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/code-editor.js?bust=<?php echo(mktime()); ?>"></script>
<script>
YAHOO.util.Event.on(window, 'load', function() {
    var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true });
});
</script>
</body>
</html>
