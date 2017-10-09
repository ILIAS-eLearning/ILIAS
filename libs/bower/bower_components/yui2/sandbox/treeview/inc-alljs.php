<?php
$ext = "debug";
$mode = "debug";

if (isset($_GET["mode"])) {
    $mode = $_GET["mode"];

    if ($mode == "min") {
        $ext = "-min";
    }

    if ($mode == "debug") {
        $ext = "-debug";
    }
}

?>
<script type="text/javascript" src="../../build/yahoo/yahoo-min.js" ></script>
<script type="text/javascript" src="../../build/event/event-min.js"></script>
<script type="text/javascript" src="../../build/dom/dom-min.js"></script>

<!-- The following are required for the logger -->
<script type="text/javascript" src="../../build/logger/logger-min.js"></script>
<!-- End logger reqs -->

<?php
if ($mode == "min") {
	echo '<script type="text/javascript" src="../../build/treeview-min.js" ></script>';
} else if ($mode == "build") {
	echo '<script type="text/javascript" src="../../build/treeview/treeview.js" ></script>';
} else if ($mode == "debug") {
	echo '<script type="text/javascript" src="../../build/treeview/treeview-debug.js" ></script>';
} else if ($mode == "prod") {
	echo '<script type="text/javascript" src="http://us.js2.yimg.com/us.js.yimg.com/lib/common/widgets/2/treeview/treeview_2.7.0.js" ></script>';
} else {
	echo '
		<script type="text/javascript" src="../../src/js/TreeView.js" ></script>
		<script type="text/javascript" src="../../src/js/Node.js" ></script>
		<script type="text/javascript" src="../../src/js/RootNode.js" ></script>
		<script type="text/javascript" src="../../src/js/TextNode.js" ></script>
		<script type="text/javascript" src="../../src/js/HTMLNode.js" ></script>
		<script type="text/javascript" src="../../src/js/MenuNode.js" ></script>
        <!--
		<script type="text/javascript" src="../../../src/js/ListNode.js" ></script>
        -->

		<script type="text/javascript" src="../../src/js/anim/TVAnim.js" ></script>
		<script type="text/javascript" src="../../src/js/anim/TVFadeIn.js" ></script>
		<script type="text/javascript" src="../../src/js/anim/TVFadeOut.js" ></script>
	';
}
?>

