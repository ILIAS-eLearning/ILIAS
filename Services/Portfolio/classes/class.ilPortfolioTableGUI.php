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
	protected $shared;

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_user_id, $a_shared = false)
	{
		global $ilCtrl, $lng;

		$this->user_id = (int)$a_user_id;
		$this->shared = (bool)$a_shared;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("prtf_portfolios"));

		if(!$this->shared)
		{
			$this->addColumn($this->lng->txt(""), "", "1");
		}
		$this->addColumn($this->lng->txt("title"), "title", "50%");
		if(!$this->shared)
		{
			$this->addColumn($this->lng->txt("online"), "is_online");
			$this->addColumn($this->lng->txt("prtf_default_portfolio"), "is_default");
		}
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.portfolio_row.html", "Services/Portfolio");

		if(!$this->shared)
		{
			$this->addMultiCommand("confirmPortfolioDeletion", $lng->txt("delete"));
			$this->addCommandButton("saveTitles",
				$lng->txt("prtf_save_status_and_titles"));
		}

		$this->getItems();
		
		$lng->loadLanguageModule("wsp");
		
		include_once('./Services/Link/classes/class.ilLink.php');
	}

	protected function getItems()
	{
		global $ilUser;
		
		include_once "Services/Portfolio/classes/class.ilPortfolioAccessHandler.php";
		$access_handler = new ilPortfolioAccessHandler();
		
		include_once "Services/Portfolio/classes/class.ilObjPortfolio.php";
		$data = ilObjPortfolio::getPortfoliosOfUser($this->user_id);
		
		// remove all portfolios which are not shared
		if($this->shared)
		{
			$other = $access_handler->getSharedObjects($this->user_id);
			foreach($data as $idx => $item)
			{
				if(!in_array($item["id"], $other))
				{
					unset($data[$idx]);
				}	
				else
				{
					// #9848: flag if current share access is password-protected 
					$perms = $access_handler->getPermissions($item["id"]);
					$data[$idx]["password"] = (!in_array($ilUser->getId(), $perms) &&
						!in_array(ilWorkspaceAccessGUI::PERMISSION_REGISTERED, $perms) &&
						!in_array(ilWorkspaceAccessGUI::PERMISSION_ALL, $perms) &&
						in_array(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, $perms));										
				}
			}			
		}
		else
		{
			$this->shared_objects = $access_handler->getObjectsIShare();
		}
		
		$this->setData($data);				
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		// owner
		if(!$this->shared)
		{			
			$this->tpl->setCurrentBlock("title_form");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_set["title"]));
			$this->tpl->parseCurrentBlock();
			
			if(in_array($a_set["id"], $this->shared_objects))
			{
				$this->tpl->setCurrentBlock("shared");
				$this->tpl->setVariable("TXT_SHARED", $lng->txt("wsp_status_shared"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("chck");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("edit");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->setVariable("STATUS_ONLINE",
				($a_set["is_online"]) ? " checked=\"checked\"" : "");
			$this->tpl->setVariable("VAL_DEFAULT",
				($a_set["is_default"]) ? $lng->txt("yes") : "");
			$this->tpl->parseCurrentBlock();
						
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
						
			if($a_set["is_online"])
			{
				if(!$a_set["is_default"])
				{
					$this->tpl->setVariable("URL_ACTION",
						$ilCtrl->getLinkTarget($this->parent_obj, "setDefaultConfirmation"));
					$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_set_as_default"));
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->setVariable("URL_ACTION",
						$ilCtrl->getLinkTarget($this->parent_obj, "unsetDefault"));
					$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_unset_as_default"));
					$this->tpl->parseCurrentBlock();
				}
			}

			$ilCtrl->setParameter($this->parent_obj, "prt_id", "");
		}
		// shared
		else
		{
			$this->tpl->setCurrentBlock("title_static");
			$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
			$this->tpl->parseCurrentBlock();
			
			if($a_set["password"])
			{
				$this->tpl->setCurrentBlock("shared");
				$this->tpl->setVariable("TXT_SHARED", $lng->txt("wsp_password_protected_resource"));
				$this->tpl->parseCurrentBlock();
			}
			
			$link = ilLink::_getStaticLink($a_set["id"], "prtf", true);
		
			$this->tpl->setCurrentBlock("action");
			$this->tpl->setVariable("URL_ACTION", $link);
			$this->tpl->setVariable("TXT_ACTION", $lng->txt("view"));
			$this->tpl->parseCurrentBlock();
		}

	}
	
}?>
