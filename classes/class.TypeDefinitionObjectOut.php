<?php
/**
* Class TypeDefinitionObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.TypeDefinitionObjectOut.php,v 1.2 2002/12/19 00:13:45 shofmann Exp $
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
	function TypeDefinitionObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
	}

	function viewObject()
	{
		$this->getTemplateFile("view");
		$num = 0;

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");

		foreach ($this->data["cols"] as $key)
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
		
		if (is_array($this->data["data"][0]))
		{
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
				$this->tpl->parseCurrentBlock();
			
				//data
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