<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>YUI: YAHOO.widget.Editor</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="../../build/reset-fonts-grids/reset-fonts-grids.css"> 
</head>
<body class="yui-skin-sam">
<div id="davdoc" class="yui-t7">
    <div id="hd"><h1 id="header"><a>YAHOO.widget.Editor</a></h1></div>
    <div id="bd">
        <a href="#foo" id="foo">Foo!!</a>
    </div>
    <div id="ft">&nbsp;</div>
</div>
<script type="text/javascript" src="../../build/yahoo/yahoo.js"></script> 
<script type="text/javascript" src="../../build/dom/dom.js"></script> 
<script type="text/javascript" src="../../build/event/event.js"></script> 
<script>


var log = function(str) {
    if (window.opera) {
        window.opera.postError(str);
    } else {
        console.log(str);
    }
};

log('1: ' + YAHOO.lang.isString('Foo'));
log('2: ' + YAHOO.lang.isString(document.getElementById('foo')));
var a = document.createElement('a');
a.href = 'http://foo.com';
a.innerHTML = 'Foo Two!!';
log('3: ' + YAHOO.lang.isString(a));

var d = document.createElement('div');
d.innerHTML = '<a href="http://foo-bar.com">Foo Three!!</a>';
log('4: ' + YAHOO.lang.isString(d.firstChild));

YAHOO.util.Event.on([d, d.firstChild], 'focus', log);

</script>
</body>
</html>
