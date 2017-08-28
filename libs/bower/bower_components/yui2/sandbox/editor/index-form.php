<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: YAHOO.widget.Editor</title>
    <link rel="stylesheet" type="text/css" href="../../build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" href="http://blog.davglass.com/wp-content/themes/davglass/style.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="../../build/logger/assets/logger.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/menu/assets/skins/sam/menu.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/button/assets/skins/sam/button.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/resize/assets/skins/sam/resize.css"> 
    <link rel="stylesheet" type="text/css" href="../../build/editor/assets/skins/sam/editor.css"> 
    
    <style>
        #form1 {
            margin: 2em;
        }
    </style>
</head>
<body>
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a>YAHOO.widget.Editor</a></h1></div>
    <div id="bd">
        <form method="post" action="index-full.php" id="form1" class="yui-skin-sam">
        <textarea id="editor" name="editor" rows="20" cols="75">
        </textarea>
        <br><input type="submit" value="Submit Form"/>
        <p>
        Code Type: <select id="code_type">
            <option value="semantic" selected> Semantic</option>
            <option value="xhtml"> XHTML </option>
            <option value="css"> CSS </option>
            <option value="default"> Default </option>
        </select><br>
        <a href="#" id="editorHTML">Editor HTML</a><br>
        <a href="#" id="editorSE">Editor Selected Element</a><br>
        <a href="#" id="editorDisable">Toggle Disable</a><br>
        <a href="#" id="editorFocus">Focus Window</a><br>
        <a href="#" id="editorInput">Create Input</a><br>
        </p>
        <textarea rows="20" cols="75" id="afterHTML" style="display: none;"></textarea>
        <?php
        if ($_POST['editor']) {
            echo('<h2>Posted Data</h2>');
            echo('<textarea rows="20" cols="75" id="saveHTML">'.stripslashes($_POST['editor']).'</textarea>');
            //echo('<pre>'.print_r($_POST, 1).'</pre>');
        }
        ?>
        </form>
    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../../build/yahoo/yahoo.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/dom/dom.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/event/event.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/dragdrop/dragdrop.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/animation/animation.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/element/element.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/resize/resize-beta.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/logger/logger.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/container/container_core.js?bust=<?php echo(mktime()); ?>"></script> 

<script type="text/javascript" src="../../build/menu/menu.js?bust=<?php echo(mktime()); ?>"></script> 
<script type="text/javascript" src="../../build/button/button.js?bust=<?php echo(mktime()); ?>"></script>
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
    width: '785px',
    animate: true,
    dompath: true,
    handleSubmit: true,
    //ptags: true,
    drag: true,
    resize: true,
    //extracss: 'body { font-size: 11px; }',
    //autoHeight: true,
    focusAtStart: true//,
    //extracss: 'body { background-color: blue; }'
};

myEditor = new YAHOO.widget.Editor('editor', myConfig);
//myEditor.on('afterRender', function() {
//    myEditor.toolbar.collapse();
//});
/*
myEditor._keyMap.CLOSE_WINDOW = {
    key: 69,
    mods: ['alt', 'ctrl']
};
*/
//myEditor._defaultToolbar.buttonType = 'basic';
/*
myEditor.on('windowInsertImageRender', function() {
    console.log(arguments);
});
*/
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

YAHOO.util.Event.onAvailable('editorDisable', function() {
    YAHOO.util.Event.addListener('editorDisable', 'click', function(ev) {
        if (myEditor.get('disabled')) {
            myEditor.set('disabled', false);
        } else {
            myEditor.set('disabled', true);
        }
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
YAHOO.util.Event.onAvailable('editorFocus', function() {
    YAHOO.util.Event.addListener('editorFocus', 'click', function(ev) {
        var el = myEditor.focus();
        YAHOO.util.Event.stopEvent(ev);
    });
});

YAHOO.util.Event.onAvailable('editorInput', function() {
    YAHOO.util.Event.addListener('editorInput', 'click', function(ev) {
        YAHOO.util.Event.stopEvent(ev);
        delete myEditor.invalidHTML.input;
        if (myEditor._hasSelection()) {
            var text = '';
            if (myEditor.browser.ie) {
                text = myEditor._getRange().text;
            } else {
                text = myEditor._getSelection().toString();
            }
            var id = YAHOO.util.Dom.generateId();
            myEditor.execCommand(
                'inserthtml',
                '<input id="'+id+'" class="fitb" readonly="readonly" value="'+text.replace(/"/g, '&quot;')+'" size="'+(text.length)+'" />'
            );
            
        } else {
            alert('Select Something First..');
        }
    });
    myEditor.on('beforeEditorKeyDown', function(e) {
        var tar = YAHOO.util.Event.getTarget(e.ev);
        if (tar.tagName && tar.tagName.toLowerCase() == 'input') {
            YAHOO.util.Event.stopEvent(e.ev);
            return false;
        }
    });
});

</script>
</body>
</html>
