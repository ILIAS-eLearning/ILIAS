<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: YAHOO.widget.Editor</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="../../build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/logger/assets/logger.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/resize/assets/skins/sam/resize.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/editor/assets/skins/sam/editor.css"> 
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
        <?php
        if ($_POST['editor']) {
            echo('<pre>'.print_r($_POST, 1).'</pre>');
        }
        ?>
        <form method="post" action="index.php" id="form1">
        <!--div id="editor"-->
        <textarea id="editor" name="editor" rows="20" cols="75">
        <?php
            if ($_POST['editor']) {
                echo(stripslashes($_POST['editor']));
            } else {
        ?>
        <?php
        }
        ?>
</textarea>
        <!--img src="pics/Photo1.jpg" align="right"/>
        <p>This is a <span style="font-weight: bold;">test</span>.</p>
        <p>This is a <span style="font-style: italic;">test</span>.</p>
        <p>This is a <span style="text-decoration: underline;">test</span>.</p>
        <p>This is a <span style="color: #BFBF00;">test</span>.</p>
        This is a test.
    test This
    Test This Again.
Test One More Time

Double Return
        This is a test.<br><font face="Courier New"><u>This</u></font><font face="Courier New"> is</font> <b>some</b> <i id="testId">content</i>... <i class="test"><b class="test1 test2">Test Again</b></i><br>Some more plain text goes here..<br>
        <ol>
            <li>Item 1</li>
            <li>Item 1</li>
            <li>Item 1</li>
        </ol>
        <a href="http://blog.davglass.com/">This is <b>some</b> more test text.</a> This is some more test text. This is some more test text. This is some more test text.<br>
        <ul>
            <li>List Item</li>
        </ul>
        <font face="Times New Roman">This is some more test text. This is some more <b>test <i>text</i></b></font>. This is some more test text. This is some more test text. This is some more test text. This is some more test text. This is some more test text. This is some more test text. This is some more test text. 
        <img src="pics/Photo3.jpg"/-->

        <br><input type="submit" id="submit" name="submit" value="Submit Form"/>
        <br><input type="submit" id="submit2" name="submit2" value="Submit Form (2)"/>
        </form>
        <p>
        Code Type: <select id="code_type">
            <option value="semantic" selected> Semantic</option>
            <option value="xhtml"> XHTML </option>
            <option value="css"> CSS </option>
            <option value="default"> Default </option>
        </select><a href="#" id="editorHTML">Editor HTML</a><br>
        <a href="#" id="editorSE">Editor Selected Element</a><br>
        <a href="#" id="populate">Populate HTML</a><br>
        <a href="#" id="hasSelection">hasSelection</a><br>
        </p>
        <textarea rows="20" cols="75" id="afterHTML" style="display: none;"></textarea>
        <?php
        if ($_POST['editor']) {
            echo('<h2>Posted Data</h2>');
            echo('<textarea rows="20" cols="75" id="saveHTML">'.$_POST['editor'].'</textarea>');
        }
        ?>
    </div>
    <div id="ft">&nbsp;</div>
</div>
<script>
    var timer = (new Date()).getTime();
</script>
<!--script type="text/javascript" src="js/prototype.js?bust=<?php echo(mktime()); ?>"></script-->
<script type="text/javascript" src="../../build/yahoo/yahoo.js"></script> 
<script type="text/javascript" src="../../build/dom/dom.js"></script> 
<script type="text/javascript" src="../../build/event/event.js"></script> 
<script type="text/javascript" src="../../build/dragdrop/dragdrop.js"></script> 
<script type="text/javascript" src="../../build/element/element.js"></script> 
<script type="text/javascript" src="../../build/resize/resize.js"></script> 
<script type="text/javascript" src="../../build/selector/selector-beta.js"></script> 
<script>
YAHOO.util.Event.onDOMReady(function() {
    var timer2 = (new Date()).getTime();
    YAHOO.util.Dom.setStyle(document.body, 'color', 'green');
    //alert(timer2 - timer);
    YAHOO.log('Time:' + (timer2 - timer), 'info', 'Timer');
});
</script>
<script type="text/javascript" src="../../build/logger/logger.js"></script> 
<script type="text/javascript" src="../../build/container/container_core-min.js"></script> 
<script src="js/toolbar-button.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/toolbar.js?bust=<?php echo(mktime()); ?>"></script>
<script src="js/simple-editor.js?bust=<?php echo(mktime()); ?>"></script>
<script>
YAHOO.util.Event.on(window, 'load', function() {
    //var logger = new YAHOO.widget.LogReader(null, { logReaderEnabled: true });
});
</script>
<script>

YAHOO.util.Event.on('form1', 'submit', function() {
    var b = YAHOO.util.Selector.query('#form1 input');
    //b[0].disabled = true;
    //b[1].disabled = true;
});

var myConfig = {
    height: '300px',
    width: '530px',
    animate: true,
    dompath: true,
    handleSubmit: true,
    insert: false,
    //ptags: true,
    drag: true,
    resize: true,
    extracss: 'body { font-size: 11px; }'
    //allowNoEdit: true
    //plainText: true,
    //autoHeight: true
    //focusAtStart: true//,
    //disabled: true
};

YAHOO.util.Event.onDOMReady(function() {
    YAHOO.util.Dom.setStyle(document.body, 'background-color', '#f2f2f2');
    //myEditor = new YAHOO.widget.SimpleEditor('editor', myConfig);
    myEditor = new YAHOO.widget.SimpleEditor(YAHOO.util.Dom.get('editor'), myConfig);
    myEditor._resizeConfig.proxy = false;
    //myEditor._defaultToolbar.buttonType = 'advanced';
    //myEditor._defaultToolbar.buttons.splice(2, 2);
    /*
    myEditor.on('editorContentLoaded', function() {
        window.setTimeout(function() {
            myEditor.set('disabled', false);
        }, 2000);
    });
    */
    myEditor.render();
});

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
YAHOO.util.Event.onAvailable('editorSE', function() {
    YAHOO.util.Event.addListener('editorSE', 'click', function(ev) {
        var el = myEditor._getSelectedElement();
        alert(el.tagName + ':: (' + el.innerHTML + ')');
        YAHOO.util.Event.stopEvent(ev);
    });
});
YAHOO.util.Event.onAvailable('populate', function() {
    YAHOO.util.Event.addListener('populate', 'click', function(ev) {
        //myEditor.setEditorHTML('<p>This <a href="test.htm">is a</a> test</p>');
        myEditor.setEditorHTML('<img src="pics/Photo2.jpg">');
        YAHOO.util.Event.stopEvent(ev);
    });
});
YAHOO.util.Event.onAvailable('hasSelection', function() {
    YAHOO.util.Event.addListener('hasSelection', 'click', function(ev) {
        alert('Selection: ' + myEditor._hasSelection());
        YAHOO.util.Event.stopEvent(ev);
    });
});

</script>
</body>
</html>
