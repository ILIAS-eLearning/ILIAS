<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Blog/classes/class.ilObjBlog.php");

/**
 * Portfolio page table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolioPageTableGUI extends ilTable2GUI
{
	protected $portfolio;
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilObjPortfolio $a_portfolio)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilUser;

		$this->portfolio = $a_portfolio;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("pages"));

		$this->addColumn($this->lng->txt(""), "", "1");
		$this->addColumn($this->lng->txt("user_order"));
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.portfolio_page_row.html", "Services/Portfolio");

		$this->addMultiCommand("confirmPortfolioPageDeletion", $lng->txt("delete"));
		$this->addMultiCommand("copyPageForm", $lng->txt("prtf_copy_page"));		
		
		$this->addCommandButton("savePortfolioPagesOrdering",
			$lng->txt("user_save_ordering_and_titles"));

		$this->getItems();
		
		$lng->loadLanguageModule("blog");
	}

	function getItems()
	{
		global $ilUser;
		
		include_once("./Services/Portfolio/classes/class.ilPortfolioPage.php");
		$data = ilPortfolioPage::getAllPages($this->portfolio->getId());
		$this->setData($data);
		
		$this->blogs = array();
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		$root = $tree->readRootId();
		if($root)
		{
			$root = $tree->getNodeData($root);
			foreach ($tree->getSubTree($root) as $node)
			{
				if ($node["type"] == "blog")
				{
					$this->blogs[$node["obj_id"]] = $node["wsp_id"];
				}
			}		
		}
		
	    include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";	
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $lng, $ilCtrl;

		switch($a_set["type"])
		{
			case ilPortfolioPage::TYPE_PAGE:
				$this->tpl->setCurrentBlock("title_field");
				$this->tpl->setVariable("ID", $a_set["id"]);
				$this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_set["title"]));
				$this->tpl->parseCurrentBlock();
				
				$this->tpl->setCurrentBlock("action");
				$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit_page"));
				$ilCtrl->setParameterByClass("ilportfoliopagegui",
					"ppage", $a_set["id"]);
				$this->tpl->setVariable("CMD_EDIT",
					$ilCtrl->getLinkTargetByClass("ilportfoliopagegui", "edit"));	
				$this->tpl->parseCurrentBlock();
				break;
			
			case ilPortfolioPage::TYPE_BLOG:
				$this->tpl->setCurrentBlock("title_static");
				$this->tpl->setVariable("VAL_TITLE", $lng->txt("obj_blog").": ".ilObjBlog::_lookupTitle($a_set["title"]));
				$this->tpl->parseCurrentBlock();
												
				$obj_id = (int)$a_set["title"];
				if(isset($this->blogs[$obj_id]))
				{
					$node_id = $this->blogs[$obj_id];
					$link = ilWorkspaceAccessHandler::getGotoLink($node_id, $obj_id);
					
					$ilCtrl->setParameterByClass("ilportfoliopagegui",
						"ppage", $a_set["id"]);
					$link = $ilCtrl->getLinkTargetByClass(array("ilportfoliopagegui", "ilobjbloggui"), "edit");

					$this->tpl->setCurrentBlock("action");
					$this->tpl->setVariable("TXT_EDIT", $lng->txt("blog_edit"));
					$this->tpl->setVariable("CMD_EDIT", $link);	
					$this->tpl->parseCurrentBlock();
				}
				break;
		}
		
		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("VAL_ORDER_NR", $a_set["order_nr"]);
	}
}

?>