<?php


include_once "include/ilias_header.inc";

$tplMain->setVariable("STATUSBAR","List of '".$type."'-objects");

$tplContent = new Template("object_list.html",true,true);
$tplContent->setVariable($ilias->ini["layout"]);

if (!empty($type)) {
	$where_clause = " WHERE type = '$type'";
}

// count objects
$query = "SELECT COUNT(*) as num FROM object_data".$where_clause;
$res = $ilias->db->query($query);

if (DB::isError($res)) {
	die("<b>".$res->getMessage()."</b><br>Script: ".__FILE__."<br>Line: ".__LINE__);
}

if ($res->numRows() > 0) {
	$data = $res->fetchRow();
	$hitcount = $data[0];
}
       
if (empty($offset)) {
	$offset = 0;
}

if (empty($limit)) {
	$limit = 10;
}
  
if ($objects = getObjectList($type,$offset,$limit))
{
	$tplContent->touchBlock("header");
	$tplContent->setCurrentBlock("row");

	foreach ($objects as $o)
	{
		$tplContent->setVariable(OBJ_ID,$o["obj_id"]);
		$tplContent->setVariable(OBJ_TYPE,$o["type"]);
		$tplContent->setVariable(USR_ID,$o["usr_id"]);
		$tplContent->setVariable(OBJ_TITLE,$o["title"]);
		$tplContent->setVariable(OBJ_DESC,$o["desc"]);
		$tplContent->setVariable(OBJ_LASTUPDATE,TFormat::fdateDB2dateDE($o["last_update"]));
		$tplContent->setVariable(OBJ_CREATEDATE,TFormat::fdateDB2dateDE($o["create_date"]));
		// Für Rollen werden defaultmäßig Lerneinheiten angeziegt
		// sonst die Rechte des Admins
		if($o["type"] == 'role')
		    $tplContent->setVariable(SHOW,'le');
		else
		    $tplContent->setVariable(SHOW,2);
		$tplContent->parseCurrentBlock();
	}

	if($linkbar = TPrevNextNavBar::Linkbar("listobjects.php",$hitcount,$limit,$offset,$params))
	{
		$tplContent->setCurrentBlock("linkbar");

		if(isset($type))
		{
			$params["type"] = $type;
		}

		$tplContent->setVariable(LINKBAR,$linkbar);
		$tplContent->parseCurrentBlock();
	}
}
else
{
	$tplContent->addBlockFile("SYS_MESSAGE","MSG","message.html");
	$tplContent->setCurrentBlock("MSG");
	$tplContent->setVariable(MESSAGE,"No objects of this type in database!");
	$tplContent->parseCurrentBlock();
}

if (!empty($type))
{
	$tplContent->setCurrentBlock("create");
	$tplContent->setVariable("OBJ_TYPE",$type);
	$tplContent->parseCurrentBlock();
}
		
	$tplMain->setVariable(CONTENT,$tplContent->get());	
include_once "include/ilias_footer.inc";
?>