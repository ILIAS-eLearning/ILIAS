<?php
include_once "include/ilias_header.inc";

//var_dump($_GET);


	// Template generieren
	$tplContent = new Template("content_operations.html",true,true);
	$tplContent->setVariable($ilias->ini["layout"]);

	// Show path
	$tree = new Tree($_GET["obj_id"],1,1);
	$tree->getPath();
	$path = showPath($tree->Path,"content.php");
	$tplContent->setVariable("TREEPATH",$path);

	$tplContent->setVariable("OBJ_SELF","content.php?obj_id=$obj_id&parent=$parent");
	$tplContent->setVariable("CMD","save");
	$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
	$tplContent->setVariable("TPOS",$_GET["parent"]);
		
	$query = "SELECT rbac_operations.ops_id, rbac_operations.operation, rbac_operations.description ".
			 "FROM rbac_operations ".
			 "LEFT JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id ".
			 "LEFT JOIN object_data ON rbac_ta.typ_id = object_data.obj_id ".
			 "WHERE object_data.obj_id = '".$_GET["obj_id"]."'";
		
	$res = $ilias->db->query($query);
       
	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$ops_arr_valid[] = array(
							  "ops_id"	  => $data["ops_id"],
                     		  "operation" => $data["operation"],
                     		  "desc"	  => $data["description"]
						  );
	
	}
		
	// BEGIN ROW

	$tplContent->setCurrentBlock("row",true);

if ($ops_arr = getOperationList())
{
	//$ops_arr = getOperationList();

	$options = array("e" => "enabled","d" => "disabled");

	foreach ($ops_arr as $key => $ops)
	{
		$ops_status = "d";
		
		foreach ($ops_arr_valid as $ops_valid)
		{
			if ($ops["ops_id"] == $ops_valid["ops_id"])
			{
				$ops_status = "e";
				break;
			}
		}
		
		$ops_options = TUtil::formSelect($ops_status,"id[]",$options);
		
		// color changing
		if ($key % 2)
		{
			$css_row = "row_high";	
		}
		else
		{
			$css_row = "row_low";
		}

		$tplContent->setVariable("LINK_TARGET","object.php?obj_id=".$obj_id."&parent=".$parent."&cmd=edit");	
		$tplContent->setVariable("OPS_TITLE",$ops["operation"]);
		$tplContent->setVariable("OPS_DESC",$ops["desc"]);
		$tplContent->setVariable("IMG_TYPE","icon_type.gif");
		$tplContent->setVariable("ALT_IMG_TYPE","Operation");
		$tplContent->setVariable("CSS_ROW",$css_row);
		$tplContent->setVariable("OPS_ID",$ops["ops_id"]);
		$tplContent->setVariable("OPS_STATUS",$ops_options);
		$tplContent->parseCurrentBlock("row");
	}
	
	$tplContent->touchBlock("options");
}
else
{
	$tplContent->setCurrentBlock("notfound");
	$tplContent->setVariable("MESSAGE","No Permission to read");
	$tplContent->parseCurrentBlock();
}

include_once "include/ilias_footer.inc";
?>