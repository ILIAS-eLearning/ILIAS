<?php
/**
* Class TypeDefinitionObjectOut
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$Id: class.TypeDefinitionObjectOut.php,v 1.4 2003/02/25 17:36:49 akill Exp $
*
* @extends Object
* @package ilias-core
*/

class TypeDefinitionObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function TypeDefinitionObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "typ";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* list operations of object type
	*/
	function viewObject()
	{
		global $rbacadmin;

		$this->getTemplateFile("view");
		$num = 0;

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");

		$cols = array("", "type", "operation", "description", "status");
		foreach ($cols as $key)
		{
			if ($key != "")
			{
			    $out = $this->lng->txt($key);
			}
			else
			{
				$out = "&nbsp;";
			}
			$this->tpl->setVariable("HEADER_TEXT", $out);
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
							  $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&order=type&direction=".
							  $_GET["dir"]."&cmd=".$_GET["cmd"]);
			$this->tpl->parseCurrentBlock();
		}

		$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);

		if ($ops_arr = getOperationList('', $_GET["order"], $_GET["direction"]))
		{
			foreach ($ops_arr as $key => $ops)
			{
				// BEGIN ROW
				if (in_array($ops["ops_id"],$ops_valid))
				{
					$ops_status = 'enabled';
				}
				else
				{
					$ops_status = 'disabled';
				}

				//visible data part
				$this->objectList["data"][] = array(
					"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_perm_b.gif\" border=\"0\">",
					"title" => $ops["operation"],
					"description" => $ops["desc"],
					"status" => $ops_status
				);

				//control information
				$this->objectList["ctrl"][] = array(
					"type" => "perm",
					"obj_id" => $ops["ops_id"],
					"parent" => $this->id,
					"parent_parent" => $this->parent
				);

		//if (is_array($this->data["data"][0]))
		//{
			//table cell
			//for ($i=0; $i< count($this->data["data"]); $i++)
			//{
				//$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				$num++;

				// color changing
				$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");

				$this->tpl->touchBlock("empty_cell");

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();

				//data
				$data = array(
					"type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_perm_b.gif\" border=\"0\">",
					"title" => $ops["operation"],
					"description" => $ops["desc"],
					"status" => $ops_status
				);

				foreach ($data as $key => $val)
				{
					// color for status
					if ($key == "status")
					{
						if ($val == "enabled")
						{
							$color = "green";
						}
						else
						{
							$color = "red";
						}

						$val = "<font color=\"".$color."\">".$this->lng->txt($val)."</font>";
					}

					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //foreach

				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		}

		// SHOW VALID ACTIONS
		$this->showActions();

		// SHOW POSSIBLE SUB OBJECTS
		$this->showPossibleSubObjects();
	}

	function editObject()
	{
		$this->getTemplateFile("edit");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?&cmd=save&obj_id=".$_GET["obj_id"]."&parent=".
						  $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);

		//table header
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header_cell");
			if ($key != "")
			    $out = $this->lng->txt($key);
			else
				$out = "&nbsp;";
			$this->tpl->setVariable("TEXT", $out);
			$this->tpl->setVariable("LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
							  $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&order=type&direction=".
							  $_GET["dir"]."&cmd=".$_GET["cmd"]);
			$this->tpl->parseCurrentBlock();
		}

		//table cell
		for ($i=0; $i< count($this->data["data"]); $i++)
		{
			$data = $this->data["data"][$i];
			$ctrl = $this->data["ctrl"][$i];

			$num++;

			// color changing
			$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");

			$this->tpl->touchBlock("empty_cell");
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->setVariable("TEXT", "");
			$this->tpl->parseCurrentBlock();

			//data
			foreach ($data as $key => $val)
			{
				$this->tpl->setCurrentBlock("text");
				$this->tpl->setVariable("TEXT_CONTENT", $val);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();
				} //foreach
			
			$this->tpl->setCurrentBlock("table_row");	
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
		} //for
		
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("save"));
	}
	

} // END class.TypeDefinitionObjectOut
?>