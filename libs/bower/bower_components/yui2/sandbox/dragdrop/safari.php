<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Safari 4 getBoundingClientRect</title>
    <style type="text/css" media="screen">
        body {
            position: relative;
        }

        #test {
            position: absolute;
            top: 200px;
            left: 400px;
            width: 30px;
            height: 30px;
            border: 1px solid black;
            background-color: red;
            cursor: move;
        }
	</style>
</head>
<body>
<p>Click on the red box in Safari 4 and look at the console. The numbers should be similar. Now zoom the page a couple of times then click the box, the numbers are <em>way off</em>.</p>
<div id="test"></div>

<script type="text/javascript">
var drag = null,
deltas = [];
window.onload = function() {
    document.getElementById('test').onmousedown = function(e) {
        var rect = document.getElementById('test').getBoundingClientRect();
        deltas = [e.pageX - rect.left, e.pageY - rect.top];
        console.log('mousedown: ', [e.pageX, e.pageY], [rect.left, rect.top]);
        document.getElementById('test').style.top = e.pageY - deltas[1] + 'px';
        document.getElementById('test').style.left = e.pageX - deltas[0] + 'px';
        drag = true;
        return false;
    };
    document.onmouseup = function(e) {
        drag = false;
    };
    document.onmousemove = function(e) {
        var rect = document.getElementById('test').getBoundingClientRect();
        if (drag) {
            document.getElementById('test').style.top = e.pageY - deltas[1] + 'px';
            document.getElementById('test').style.left = e.pageX - deltas[0] + 'px';
        }
        console.log('over:', [e.pageX, e.pageY], [rect.left, rect.top]);
    };
};
</script>
</body>
</html>
