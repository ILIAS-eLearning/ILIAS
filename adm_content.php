<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">

<html>
<head>
<title></title>
<link rel="stylesheet" type="text/css" href="ilias.css">
</head>
<body link="#000099" alink="#000099" vlink="#000099" marginwidth="0" marginheight="0">
<br><br><br><br><br>
<div align="center">
<img src="images/ilias_logo_big.jpg">
</div>
<h1 align="center">Administration</h1>
<?php
/*
  include_once("ilias_db.inc");
  include_once("class.mysql.inc");
  include_once("class.tree.inc");
  include_once("class.lang_text.inc");
  include_once("errors.inc");

  $dbh =	mysql_pconnect($__virtus_dbhost,$__virtus_dbuser,$__virtus_dbpasswd)
  or die("Error: unable to connect to SQL server.");
  mysql_select_db($__virtus_dbname)
  or die("Error: database could not be opened.");

  $db           = new DB_Sql;
  $db->Host     = $__virtus_dbhost;
  $db->Database = $__virtus_dbname;
  $db->User     = $__virtus_dbuser;
  $db->Password = $__virtus_dbpasswd;

  require_once "PEAR.php";
  require_once "DB.php";
  require_once "Auth/Auth.php";
  require_once "classes/class.template.php";
  require_once "classes/class.ilias.php";
  include_once "function.library.php";
  include_once "classes/class.util.php";
  include_once "classes/class.tree.php";


  $ilias =& new ILIAS;

  $id="1|29";

  if (empty($id))
  {
  $id = 1;
  }

  $nodes = array();
  $params = explode("|",$id);
  $id = $params[0];

  foreach ($params as $val)
  {
  $nodes[] = $val;
  }


  $tree = new Tree($id);

// display tree
$Tree = $tree->buildTree($nodes);

//var_dump($Tree);
//exit;
			
// get tree information			
$exp_data = $tree->display($Tree,$id,0,$open);


//$tree->getPath();
//echo $tree->Path;
			
echo "<pre>";
//var_dump($exp_data);
echo "</pre>";


//echo "<br><b>max level:</b> ".$tree->maxlvl;

*/
?>

</body>
</html>
