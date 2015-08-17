<!DOCTYPE html>
<?php
if(!empty($_REQUEST['basedata'])) {
	setcookie("cc_data", $_REQUEST['basedata'], 0, "/");
}
?>
<html>
<head>
	<meta name="viewport" content="user-scalable=0,width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0" />
	<meta http-equiv="Content-Type" content="text/html" charset="UTF-8"/>
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="shortcut icon" type="image/png" href="favicon32.ico">
	<title>CometChat</title>
	<link type="text/css" href="./cometchatcss.php?cc_theme=synergy" rel="stylesheet" charset="utf-8">
	<script type="text/javascript" src="./cometchatjs.php?cc_theme=synergy" charset="utf-8"></script>
</head>
<body>
</body>
</html>