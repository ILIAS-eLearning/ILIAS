<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjectGUIAdapter
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObjectGUI
*/

// Whole class may be deprecated.

class ilObjectGUIAdapter
{
	var $gui_obj;
	var $ilias;
	var $tpl;
	var $lng;
	var $objDefinition;

	var $cmd;
	var $id;
	var $call_by_reference;

	/**
	* Constructor
	* @access public
	*/
	function ilObjectGUIAdapter($a_id,$a_call_by_reference,$a_prepare_output = true,$a_cmd = '')
	{
		global $ilias,$tpl,$objDefinition,$lng;


		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;

		$this->setCommand($a_cmd);
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;

		$this->__initGUIObject($a_call_by_reference,$a_prepare_output);
	}
	// SET/GET
	function getId()
	{
		return $this->id;
	}
	function setCommand($a_cmd)
	{
		if($a_cmd == "gateway" || $a_cmd == "post")
		{
			@$this->cmd = key($_POST["cmd"]);
		}
		else
		{
			$this->cmd = $a_cmd;
		}
	}
	function getCommand()
	{
		return $this->cmd;
	}
	function getType()
	{
		return $this->type;
	}
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	function performAction()
	{
		if($this->getCommand())
		{
			$method = $this->getCommand()."Object";
		}
		else
		{
			$method = $this->objDefinition->getFirstProperty($this->getType())."Object";
		}
		$this->gui_obj->$method();

		return true;
	}
	/**
	* set admin tabs
	* @access	public
	*/
/*	Deprecated
	function setAdminTabs()
	{
		global $rbacsystem;

		$tabs = array();
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

//		$properties = $this->objDefinition->getProperties($this->getType());

		foreach($properties as $key => $row)
		{
			$tabs[] = array($row["lng"], $row["name"]);
		}

		// check for call_by_reference too to avoid hacking
		if ($this->call_by_reference === false)
		{
			$object_link = "&obj_id=".$_GET["obj_id"];
		}

		foreach ($tabs as $row)
		{
			$i++;

			if ($row[1] == $this->getCommand())
			{
				$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}

			$show = true;

			// only check permissions for tabs if object is a permission object
			if($this->call_by_reference)
			{
				// only show tab when the corresponding permission is granted
				switch ($row[1])
				{
					case 'view':
						if (!$rbacsystem->checkAccess('visible',$this->getId()))
						{
							$show = false;
						}
						break;

					case 'edit':
						if (!$rbacsystem->checkAccess('write',$this->getId()))
						{
							$show = false;
						}
						break;

					case 'perm':
						if (!$rbacsystem->checkAccess('edit_permission',$this->getId()))
						{
							$show = false;
						}
						break;
					case 'trash':
						if (!$this->gui_obj->tree->getSavedNodeData($this->getId()))
						{
							$show = false;
						}
						break;

					case 'newmembers':
					case 'members':
						if (!$rbacsystem->checkAccess('write',$this->getId()))
						{
							$show = false;
						}
						break;

				} //switch
			}

			if (!$show)
			{
				continue;
			}

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $tabtype);
			$this->tpl->setVariable("TAB_TYPE2", $tab);
			$this->tpl->setVariable("IMG_LEFT", ilUtil::getImagePath("eck_l.gif"));
			$this->tpl->setVariable("IMG_RIGHT", ilUtil::getImagePath("eck_r.gif"));
			$this->tpl->setVariable("TAB_LINK", $this->gui_obj->tab_target_script."?ref_id=".$this->getId().$object_link."&cmd=".$row[1]);
			$this->tpl->setVariable("TAB_TEXT", $this->gui_obj->lng->txt($row[0]));
			$this->tpl->parseCurrentBlock();
		}
	}
*/

	// PRIVATE METHODS
	function __initGUIObject($a_call_by_reference,$a_prepare_output = true)
	{
		global $objDefinition;

		include_once "./classes/class.ilObjectFactory.php";

		// GET TYPE
		if($a_call_by_reference)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->getId());
		}
		else
		{
			$tmp_obj =& ilObjectFactory::getInstanceByObjId($this->getId());
		}
		$this->setType($tmp_obj->getType());

		// INITIATE GUI CLASS
		$class_name = $objDefinition->getClassName($this->getType());
		$location = $objDefinition->getLocation($this->getType());
		$class_constr = "ilObj".$class_name."GUI";

		//INCLUDE CLASS
		include_once $location."/class.ilObj".$class_name."GUI.php";

		// CALL CONSTRUCTOR
		$this->gui_obj =& new $class_constr(array(),$this->getId(),$a_call_by_reference,$a_prepare_output);
		
		return true;
	}
} // END class.ilObjectGUIAdapter
?>
