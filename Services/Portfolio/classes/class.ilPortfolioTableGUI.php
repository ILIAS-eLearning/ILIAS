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

		$this->setTitle($lng->txt("prtf_portfolios"));

		$this->addColumn($this->lng->txt(""), "", "1");
		$this->addColumn($this->lng->txt("title"), "title", "50%");
		$this->addColumn($this->lng->txt("online"), "is_online");
		$this->addColumn($this->lng->txt("prtf_default_portfolio"), "is_default");
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.portfolio_row.html", "Services/Portfolio");

		$this->addMultiCommand("confirmPortfolioDeletion", $lng->txt("delete"));
		$this->addCommandButton("saveTitles",
			$lng->txt("prtf_save_status_and_titles"));

		$this->getItems();
		
		$lng->loadLanguageModule("wsp");
	}

	protected function getItems()
	{
		global $ilUser;
		
		include_once "Services/Portfolio/classes/class.ilObjPortfolio.php";
		$data = ilObjPortfolio::getPortfoliosOfUser($this->user_id);
		$this->setData($data);
		
		include_once "Services/Portfolio/classes/class.ilPortfolioAccessHandler.php";
		$access_handler = new ilPortfolioAccessHandler($tree);
		$this->shared_objects = $access_handler->getObjectsIShare();
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		if(in_array($a_set["id"], $this->shared_objects))
		{
			$this->tpl->setCurrentBlock("shared");
			$this->tpl->setVariable("TXT_SHARED", $lng->txt("wsp_status_shared"));
			$this->tpl->parseCurrentBlock();
		}

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
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_edit_portfolio"));
		$this->tpl->parseCurrentBlock();
	
		/*
		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTarget($this->parent_obj, "export"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("export"));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTargetByClass("ilworkspaceaccessgui", "share"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("wsp_permissions"));
		$this->tpl->parseCurrentBlock();		 
		*/

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
