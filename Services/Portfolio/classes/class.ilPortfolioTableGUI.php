<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Portfolio table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolioTableGUI extends ilTable2GUI
{
	protected $user_id;

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
	{
		global $ilCtrl, $lng;

		$this->user_id = (int)$a_user_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("portfolios"));

		$this->addColumn($this->lng->txt(""), "", "1");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("online"), "is_online");
		$this->addColumn($this->lng->txt("default"), "is_default");
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.portfolio_row.html", "Services/Portfolio");

		$this->addMultiCommand("confirmPortfolioDeletion", $lng->txt("delete"));
		$this->addCommandButton("saveTitles",
			$lng->txt("prtf_save_status_and_titles"));

		$this->getItems();
	}

	protected function getItems()
	{
		include_once "Services/Portfolio/classes/class.ilPortfolio.php";
		$data = ilPortfolio::getPortfoliosOfUser($this->user_id);
		$this->setData($data);
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		$this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_set["title"]));

		$this->tpl->setVariable("STATUS_ONLINE",
			($a_set["is_online"]) ? " checked=\"checked\"" : "");

		$this->tpl->setVariable("VAL_DEFAULT",
			($a_set["is_default"]) ? $lng->txt("yes") : "");


		$ilCtrl->setParameter($this->parent_obj, "prt_id", $a_set["id"]);
		$this->tpl->setCurrentBlock("action");
		
		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTarget($this->parent_obj, "preview"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("user_profile_preview"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTarget($this->parent_obj, "pages"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("pages"));
		$this->tpl->parseCurrentBlock();
		
		/*
		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTarget($this->parent_obj, "edit"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("settings"));
		$this->tpl->parseCurrentBlock();		 
		*/
		
		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTargetByClass("ilworkspaceaccessgui", "share"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("perm_settings"));
		$this->tpl->parseCurrentBlock();

		if(!$a_set["is_default"] && $a_set["is_online"])
		{
			$this->tpl->setVariable("URL_ACTION",
				$ilCtrl->getLinkTarget($this->parent_obj, "setDefault"));
			$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_set_as_default"));
			$this->tpl->parseCurrentBlock();
		}

		$ilCtrl->setParameter($this->parent_obj, "prt_id", "");
	}
	
}?>
