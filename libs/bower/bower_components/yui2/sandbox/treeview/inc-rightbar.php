<?php
if (isset($_GET["mode"])) {
    $mode = $_GET["mode"];
} else {
    $mode = "";
}

if ($mode == "dist") {
    $ext = "html";
} else {
    $ext = "php";
}

?>

<style type="text/css">

/* logger default styles */
/* font size is controlled here: default 77% */
#yui-log {position:absolute;top:1em;right:1em;font-size:77%;text-align:left;}
/* width is controlled here: default 31em */
.yui-log {background-color:#AAA;border:1px solid black;font-family:monospace;z-index:9000;}
.yui-log p {margin:1px;padding:.1em;}
.yui-log button {font-family:monospace;}
.yui-log .yui-log-hd {padding:.5em;background-color:#575757;color:#FFF;}
/* height is controlled here: default 20em*/
.yui-log .yui-log-bd {width:100%;height:20em;background-color:#FFF;border:1px solid gray;overflow:auto;}
.yui-log .yui-log-ft {margin-top:.5em;margin-bottom:1em;}
.yui-log .yui-log-ft .yui-log-categoryfilters {}
.yui-log .yui-log-ft .yui-log-sourcefilters {width:100%;border-top:1px solid #575757;margin-top:.75em;padding-top:.75em;}
.yui-log .yui-log-btns {position:relative;float:right;bottom:.25em;}
.yui-log .yui-log-filtergrp {margin-right:.5em;}
.yui-log .info {background-color:#A7CC25;} /* A7CC25 green */
.yui-log .warn {background-color:#F58516;} /* F58516 orange */
.yui-log .error {background-color:#E32F0B;} /* E32F0B red */
.yui-log .time {background-color:#A6C9D7;} /* A6C9D7 blue */
.yui-log .window {background-color:#F2E886;} /* F2E886 tan */

</style>


<div id="container">
<img class="ylogo" src="img/logo.gif" alt="" />
  <div id="containerTop">
    <div id="header">
      <h1>
      
      </h1>
      <h4>&nbsp;</h4>
    </div>
    <div id="main">

<div id="rightbar">

<div id="rightBarPad">
<h3>Examples</h3>

<div id="linkage">
<ul>
<li><a href="default.<?php echo $ext ?>?mode=<?php echo $mode ?>">Default tree widget</a></li>
<li><a href="dynamic.<?php echo $ext ?>?mode=<?php echo $mode ?>">Dynamic load</a></li>

<?php if ($mode != "dist") { ?>
<li><a href="lazy.<?php echo $ext ?>?mode=<?php echo $mode ?>">Dynamic load with connection</a></li>
<?php } ?>

<li><a href="folders.<?php echo $ext ?>?mode=<?php echo $mode ?>">Folder view</a></li>
<li><a href="customicons.<?php echo $ext ?>?mode=<?php echo $mode ?>">Custom icons</a></li>
<li><a href="menu.<?php echo $ext ?>?mode=<?php echo $mode ?>">Menu</a></li>
<li><a href="html.<?php echo $ext ?>?mode=<?php echo $mode ?>">HTML node</a></li>
<li><a href="multi.<?php echo $ext ?>?mode=<?php echo $mode ?>">Multiple trees, different styles</a></li>
<li><a href="check.<?php echo $ext ?>?mode=<?php echo $mode ?>">Task list</a></li>
<li><a href="anim.<?php echo $ext ?>?mode=<?php echo $mode ?>">Fade animation</a></li>
</ul>

</div> 


    <script type="text/javascript">
    //<![CDATA[
    YAHOO.example.logApp = function() {
        var divId;
        return {
            init: function(p_divId, p_toggleElId, p_clearElId) {
                divId = p_divId
            },

            onload: function() {
                if (YAHOO.widget.Logger) {
                    var reader = new YAHOO.widget.LogReader( "logDiv", { height: "400px" } );
                    //reader._onClickPauseBtn(null, reader);
                }
            }
        };
    } (); 

    YAHOO.util.Event.on(window, "load", YAHOO.example.logApp.onload);

    //]]>
    </script>

    <div id="logDiv"></div>

    
</div>

</div>

