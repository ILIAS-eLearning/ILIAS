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
    </style>
</head>
<body class="yui-skin-sam">
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a>YAHOO.widget.Editor</a></h1></div>
    <div id="bd">
        <form method="post" action="index.php" id="form1">
        <textarea id="editor" name="editor" rows="20" cols="75">
        <?php
        if ($_POST['editor']) {
            echo($_POST['editor']);
        } else {
            //Performance Testing
            //for ($i = 0; $i <= 1000; $i++) {
            //echo('<b>Pass ('.$i.')</b><br>');
        ?>
        This is a test<br><strong>Strong Tag</strong> <em>Em Tag</em> <span style="font-weight: bold">Style Bold</span>
        <p class="yui-noedit">This is some test text. And a <a href="#">test link</a>. <a href="nada"><img src="pics/Photo1.jpg" align="right"/></a></p>
        This is a test.<br><font face="Courier New"><u>This</u></font><font face="Courier New"> is</font> <b>some</b> <i id="testId">content</i>... <i class="test"><b class="test1 test2">Test Again</b></i><br>Some more plain text goes here..<br>
        <ol>
            <li>Item 1</li>
            <li>Item 2</li>
            <li>Item 3</li>
        </ol>
        <a href="http://blog.davglass.com/">This is <b>some</b> more test text.</a> This is some more test text. This is some more test text. This is some more test text.<br>
        <ul>
            <li>List Item</li>
        </ul>
        <font face="Times New Roman">This is some more test text. This is some more <b>test <i>text</i></b></font>. This is some more test text. This is some more test text. This is some more test text. This is some more test text. This is some more test text. This is some more test text. This is some more test text. 
        <img src="pics/Photo3.jpg"/>
        <?php
            //}
        }
        ?>
        </textarea>

        <br><input type="submit" value="Submit Form"/>
        <p>
        Code Type: <select id="code_type">
            <option value="semantic" selected> Semantic</option>
            <option value="xhtml"> XHTML </option>
            <option value="css"> CSS </option>
            <option value="default"> Default </option>
        </select><a href="#" id="editorHTML">Editor HTML</a><br>
        <a href="#" id="editorSE">Editor Selected Element</a><br>
        <a href="#" id="editorToggle">Toggle Design Mode</a><br>
        </p>
        <textarea rows="20" cols="75" id="afterHTML" style="display: none;"></textarea>
        <?php
        if ($_POST['editor']) {
            echo('<h2>Posted Data</h2>');
            echo('<textarea rows="20" cols="75" id="saveHTML">'.$_POST['editor'].'</textarea>');
        }
        ?>
        </form>
    </div>
    <div id="ft">&nbsp;</div>
</div>
<!--script type="text/javascript" src="js/prototype.js?bust=<?php echo(mktime()); ?>"></script-->
<script type="text/javascript" src="../yui-dev/build/yahoo-dom-event/yahoo-dom-event.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../yui-dev/build/element/element.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../yui-dev/build/logger/logger.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../yui-dev/build/container/container_core-min.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../yui-dev/build/menu/menu-min.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../yui-dev/build/button/button-min.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/toolbar-button.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/toolbar.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/simple-editor.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/editor.js?bust=<?php echo(mktime()); ?>"></script>
<script>
YAHOO.util.Event.on(window, 'load', function() {
    var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true, height: '500px' });
});
</script>
<script>
var myConfig = {
    height: '300px',
    width: '530px',
    animate: true,
    dompath: true,
    handleSubmit: true,
    focusAtStart: true//,
    //extracss: 'body { background-color: blue; }'
};

myEditor = new YAHOO.widget.Editor('editor', myConfig);
//myEditor._defaultToolbar.buttonType = 'basic';
myEditor.render();

YAHOO.util.Event.onAvailable('editorHTML', function() {
    var state = 'off';
    YAHOO.util.Event.addListener('editorHTML', 'click', function(ev) {
        var type = YAHOO.util.Dom.get('code_type');
        var selValue = type.options[type.options.selectedIndex].value;
        myEditor.set('markup', selValue);
        myEditor.saveHTML();
        YAHOO.util.Dom.get('afterHTML').value = myEditor.getEditorHTML();
        myEditor.setStyle('position', 'static');
        myEditor.setStyle('top', '');
        myEditor.setStyle('left', '');
        myEditor.setStyle('visibility', 'visible');
        YAHOO.util.Dom.setStyle('afterHTML', 'display', 'block')
        YAHOO.util.Event.stopEvent(ev);
    });
});

YAHOO.util.Event.onAvailable('editorToggle', function() {
    YAHOO.util.Event.addListener('editorToggle', 'click', function(ev) {
        var state = myEditor._toggleDesignMode();
        alert('Set designMode to: ' + state);
        YAHOO.util.Event.stopEvent(ev);
    });
});
YAHOO.util.Event.onAvailable('editorSE', function() {
    YAHOO.util.Event.addListener('editorSE', 'click', function(ev) {
        var el = myEditor._getSelectedElement();
        alert(el.tagName + ':: (' + el.innerHTML + ')');
        YAHOO.util.Event.stopEvent(ev);
    });
});
</script>
</body>
</html>
