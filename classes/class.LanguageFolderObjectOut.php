<?php
/**
* Class LanguageFolderObjectOut
*
* @author	Stefan Meyer <smeyer@databay.de> 
* @version	$Id$Id: class.LanguageFolderObjectOut.php,v 1.4 2003/02/06 15:34:16 shofmann Exp $
* 
* @extends	Object
* @package	ilias-core
*/

class LanguageFolderObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function LanguageFolderObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
	}

	/**
	* Overwritten method from class.Object.php
	* It handles all button commands from Learning Modules
	* 
	* @access public
	*/
	function gatewayObject()
	{
		global $lng;

		switch(key($_POST["cmd"]))
		{
			case "install":
				return $this->out();
				break;
				
			case "uninstall":
				return $this->out();
				break;

			case "refresh":
				return $this->refreshObject();
				break;

			case "set_system_language":
				return $this->out();
				break;

			case "change_language":
				return $this->out();
				break;

			case "check_language":
				return $this->out();
				break;

		}
		parent::gatewayObject();
	}

	function viewObject()
	{
		$this->getTemplateFile("view");
		$num = 0;


		$this->tpl->setVariable("FORMACTION", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
								$_GET["parent"]."&cmd=gateway");

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
			
				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
				
				$this->tpl->setCurrentBlock("table_cell");
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
		} //if is_array

		// SHOW VALID ACTIONS
		$this->showActions();
	}

	function installObject()
	{
		$this->out();	
	}

	function uninstallObject()
	{
		$this->out();	
	}

	function refreshObject()
	{
		$this->out();
	}
	
	function setuserlangObject()
	{
		$this->out();
	}
	
	function setsyslangObject ()
	{
		$this->out();
	}

	function checklangObject ()
	{
		$this->out();	
	}

	function out()
	{
		$this->ilias->error_obj->sendInfo($this->data);
		header("location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
		exit();	
	}
} // END class.LanguageFolderObjectOut
?>