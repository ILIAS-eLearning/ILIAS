<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCContentInclude.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCContentIncludeGUI
*
* User Interface for Content Includes (Snippets) Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCContentIncludeGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCContentIncludeGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* Insert new resources component form.
	*/
	function insert()
	{
		switch ($_GET["subCmd"])
		{
			case "selectPool":
				$this->selectPool();
				break;

			case "poolSelection":
				$this->poolSelection();
				break;

			default:
				$this->insertFromPool($a_post_cmd, $a_submit_cmd);
				break;
		}
	}
	
	/**
	* Insert page snippet from media pool
	*/
	function insertFromPool($a_post_cmd = "edpost", $a_submit_cmd = "create_mob")
	{
		global $ilCtrl, $ilAccess, $ilTabs, $tpl, $lng;
		

		if ($_SESSION["cont_media_pool"] != "" &&
			$ilAccess->checkAccess("write", "", $_SESSION["cont_media_pool"])
			&& ilObject::_lookupType(ilObject::_lookupObjId($_SESSION["cont_media_pool"])) == "mep")
		{
			$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
			$tpl->setCurrentBlock("btn_cell");
			$ilCtrl->setParameter($this, "subCmd", "poolSelection");
			$tpl->setVariable("BTN_LINK",
				$ilCtrl->getLinkTarget($this, "insert"));
			$ilCtrl->setParameter($this, "subCmd", "");
			$tpl->setVariable("BTN_TXT", $lng->txt("cont_select_media_pool"));
			$tpl->parseCurrentBlock();

//			$this->getTabs($ilTabs, true);
//			$ilTabs->setSubTabActive("cont_mob_from_media_pool");
			
			include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
			include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
			$pool = new ilObjMediaPool($_SESSION["cont_media_pool"]);
			$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
			$mpool_table = new ilMediaPoolTableGUI($this, "insert", $pool, "mep_folder",
				ilMediaPoolTableGUI::IL_MEP_SELECT_CONTENT);
			$mpool_table->setInsertCommand("create_incl");
			
			$tpl->setContent($mpool_table->getHTML());
		}
		else
		{
			$this->poolSelection();
		}
	}

	/**
	* Pool Selection
	*/
	function poolSelection()
	{
		global $ilCtrl, $tree, $tpl, $ilTabs;

//		$this->getTabs($ilTabs, true);
//		$ilTabs->setSubTabActive("cont_mob_from_media_pool");

		include_once "./Services/COPage/classes/class.ilPoolSelectorGUI.php";
		$exp = new ilPoolSelectorGUI($this->ctrl->getLinkTarget($this, "insert"));
		$exp->setContentGUIClass(get_class($this));
		if ($_GET["expand"] == "")
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setExpand($expanded);

		$exp->setTargetGet("sel_id");
		$this->ctrl->setParameter($this, "target_type", $a_type);
		$ilCtrl->setParameter($this, "subCmd", "poolSelection");
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "insert"));
		
		// filter
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("fold");
		$exp->addFilter("crs");
		$exp->addFilter("mep");

		$sel_types = array('mep');

		$exp->setOutput(0);

		$tpl->setContent($exp->getOutput());
	}

	/**
	* create new content include in dom and update page in db
	*/
	function create()
	{
		global $ilCtrl, $lng;
		
		if (is_array($_POST["id"]))
		{
			for($i = count($_POST["id"]) - 1; $i>=0; $i--)
			{
				// similar code in ilpageeditorgui::insertFromClipboard
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
				include_once("./Services/COPage/classes/class.ilPCMediaObject.php");
				$this->content_obj = new ilPCContentInclude($this->dom);
				$this->content_obj->create($this->pg_obj, $_GET["hier_id"], $this->pc_id);
				$this->content_obj->setContentType("mep");
				$this->content_obj->setContentId($_POST["id"][$i]);
			}
			$this->updated = $this->pg_obj->update();
		}
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}

	/**
	* Select concrete pool
	*/
	function selectPool()
	{
		global $ilCtrl;
		
		$_SESSION["cont_media_pool"] = $_GET["pool_ref_id"];
		$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
		$ilCtrl->redirect($this, "insert");
	}
}
?>
