<?php
include_once "include/ilias_header.inc";


$tplMsg->setVariable(MESSAGE,"<br><br><br><br><br><br><br>Willkommen bei ILIAS");
$tplMsg->parseCurrentBlock();

$tplMain->setVariable(CONTENT,$tplMsg->get());

switch ($cmd)
{
	case "list":
        $tplContent = new Template("operation_list.html",true,true);
		
		$tplContent->setVariable($ilias->ini["layout"]);
        
		$tplContent->setCurrentBlock("row");

        $tplMain->setVariable("STATUSBAR","Operations");

        // count objects
        $res = $ilias->db->query("SELECT COUNT(*) as num FROM rbac_operations".$where_clause);

		if (DB::isError($res)) {
			die("<b>".$res->getMessage()."</b><br>Script: ".__FILE__."<br>Line: ".__LINE__);
		}
	
		if ($res->numRows() > 0)
		{
    		$data = $res->fetchRow();
        	$hitcount = $data[0];
		}
	
		if (empty($offset))
        {
            $offset = 0;
        }

        if (empty($limit))
        {
            $limit = 10;
        }
  
        if ($ops = getOperationList())
        {
        	$tplContent->touchBlock("header");

            foreach ($ops as $o)
            {
                $tplContent->setVariable(OPS_ID,$o["ops_id"]);
                $tplContent->setVariable(OPS_NAME,$o["operation"]);
                $tplContent->setVariable(OPS_DESC,$o["desc"]);
                $tplContent->parseCurrentBlock();
            }
        }
        else
        {
            $tplMsg->setVariable(MESSAGE,"No operations in database!");
            $tplMsg->parseCurrentBlock();
        }

        // Linkbar
        $tplContent->setCurrentBlock("linkbar");
        
        if($linkbar = TPrevNextNavBar::Linkbar("operation.php",$hitcount,$limit,$offset,$params))
        {
            $tplContent->setVariable(LINKBAR,$linkbar);
        }

		$tplContent->parseCurrentBlock();

		$tplMain->setVariable(CONTENT,$tplContent->get());
	break;

    case "create":

        $t->set_file("obj_form","object_form.html");

        $t->set_var("STATUSBAR","Create Operation");
        $t->set_var(CMD,"save");

        $t->parse("CONTENT","obj_form");
    break;

    case "save":
		$ops_id = createNewOperation($Fobject);
		
		header("Location: operation.php?cmd=list");
    break;
}

include_once "include/ilias_footer.inc";
?>
