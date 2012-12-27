<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for lm menu items
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilLMMenuItemsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_lmme)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->lmme = $a_lmme;
		$entries = $this->lmme->getMenuEntries();

		$this->setData($entries);
		$this->setTitle($lng->txt("cont_custom_menu_entries"));
		$this->disable("footer");
		
//		$this->addColumn("", "", "1px", true);
		$this->addColumn($this->lng->txt("link"));
		$this->addColumn($this->lng->txt("active"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.lm_menu_entry_row.html", "Modules/LearningModule");

//		$this->addMultiCommand("deleteMenuEntry", $lng->txt("delete"));
		$this->addCommandButton("saveMenuProperties", $lng->txt("save"));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($entry)
	{
		global $lng, $ilCtrl;
		
		$ilCtrl->setParameter($this->parent_obj, "menu_entry", $entry["id"]);
		
		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj,"editMenuEntry"));
		$this->tpl->setVariable("CMD", $this->lng->txt("edit"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj,"deleteMenuEntry"));
		$this->tpl->setVariable("CMD", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		$ilCtrl->setParameter($this, "menu_entry", "");

		$this->tpl->setVariable("LINK_ID", $entry["id"]);
		
		if ($entry["type"] == "intern")
		{
			$entry["link"] = ILIAS_HTTP_PATH."/goto.php?target=".$entry["link"];
		}

		// add http:// prefix if not exist
		if (!strstr($entry["link"],'://') && !strstr($entry["link"],'mailto:'))
		{
			$entry["link"] = "http://".$entry["link"];
		}

		$this->tpl->setVariable("HREF_LINK", $entry["link"]);
		$this->tpl->setVariable("LINK", $entry["title"]);

		if (ilUtil::yn2tf($entry["active"]))
		{
			$this->tpl->setVariable("ACTIVE_CHECK", "checked=\"checked\"");
		}


	}

}
?>
