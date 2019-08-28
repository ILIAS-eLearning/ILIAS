<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSCORM2004NodeGUI
*
* Base GUI class for scorm nodes (Chapter, SCO and Page)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004NodeGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilLocatorGUI
	 */
	protected $locator;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	var $node_object;
	var $slm_object;

	/**
	* constructor
	*
	* @param	object		$a_content_obj		node object
	*/
	function __construct($a_slm_obj, $a_node_id = 0)
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->tabs = $DIC->tabs();
		$this->locator = $DIC["ilLocator"];
		$this->user = $DIC->user();
		$this->slm_object = $a_slm_obj;
		$this->node_object = null;

		if ($a_node_id > 0)
		{
			$this->getNodeObject($a_node_id);
		}
	}

	/**
	* Set Parent GUI class (ilObjSCORM2004LearningModuleGUI).
	*
	* @param	object	$a_parentgui	Parent GUI class
	*/
	function setParentGUI($a_parentgui)
	{
		$this->parentgui = $a_parentgui;
	}

	/**
	* Get Parent GUI class (ilObjSCORM2004LearningModuleGUI).
	*
	* @return	object	Parent GUI class
	*/
	function getParentGUI()
	{
		return $this->parentgui;
	}

	/**
	* Get node object (chapter/sco/page)
	*/
	function getNodeObject($a_node_id)
	{
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
		$this->node_object = ilSCORM2004NodeFactory::getInstance($this->slm_object,
			$a_node_id, false);
	}
	
	/**
	* put this object into content object tree
	*
	* @todo: move to application class
	*/
//	function putInTree($a_parent_id, $a_target)
//	{
//		$tree = new ilTree($this->slm_object->getId());
//		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
//		$tree->setTreeTablePK("slm_id");
//
//		/*$parent_id = (!empty($_GET["obj_id"]))
//			? $_GET["obj_id"]
//			: $tree->getRootId();*/
//
///*		if (!empty($_GET["target"]))
//		{
//			$target = $_GET["target"];
//		}
//		else
//		{
//			// determine last child of current type
//			$childs =& $tree->getChildsByType($parent_id, $this->obj->getType());
//			if (count($childs) == 0)
//			{
//				$target = IL_FIRST_NODE;
//			}
//			else
//			{
//				$target = $childs[count($childs) - 1]["obj_id"];
//			}
//		}*/
//		if (!$tree->isInTree($this->node_obj->getId()))
//		{
//			$tree->insertNode($this->node_obj->getId(), $parent_id, $target);
//		}
//	}


	/**
	* Confirm deletion screen (delete page or structure objects)
	*
	* @todo: check if we need this
	*/
/*	function delete()
	{
		$this->setTabs();

		$cont_obj_gui = new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->delete($this->obj->getId());
	}
*/


	/**
	* cancel deletion of page/structure objects
	*
	* @todo: check if we need this
	*/
/*	function cancelDelete()
	{
		ilSession::clear("saved_post");
		$this->ctrl->redirect($this, $_GET["backcmd"]);
	}*/


	/**
	* page and structure object deletion
	*
	* @todo: check if we need this
	*/
/*	function confirmedDelete()
	{
		$cont_obj_gui = new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->confirmedDelete($this->obj->getId());
		$this->ctrl->redirect($this, $_GET["backcmd"]);
	}
*/


	/**
	* check the content object tree
	*
	* @todo: check if we need this
	*/
/*	function checkTree()
	{
		$this->content_object->checkTree();
	}
*/

	/**
	* Show subhiearchy of pages and subchapters
	*/
	function showOrganization()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		
		$this->setTabs();
		$ilTabs->setTabActive("sahs_organization");
		$this->setLocator();
		$this->getParentGUI()->showOrganization($this->node_object->getId(),
			$ilCtrl->getFormAction($this), $this->node_object->getTitle(),
			ilUtil::getImagePath("icon_".$this->node_object->getType().".svg"),
			$this, "showOrganization");
	}
	
	/**
	* Insert Chapter
	*/
	function insertChapter()
	{
		$ilCtrl = $this->ctrl;
		
		$res = $this->getParentGUI()->insertChapter(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}

	/**
	 * Insert Sco
	 */
	function insertSco()
	{
		$ilCtrl = $this->ctrl;
		
		$res = $this->getParentGUI()->insertSco(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}

	/**
	 * Insert Asset
	 */
	function insertAsset()
	{
		$ilCtrl = $this->ctrl;

		$res = $this->getParentGUI()->insertAsset(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}

	/**
	* Insert Page
	*/
	function insertPage()
	{
		$ilCtrl = $this->ctrl;
		
		$res = $this->getParentGUI()->insertPage(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}

	/**
	 * Insert Page with Layout
	 */
	function insertTemplateGUI()
	{
		$ilCtrl = $this->ctrl;
		$this->getParentGUI()->insertTemplateGUI(true);
	}
	
	/**
	 * Insert special page
	 */
	function insertSpecialPage()
	{
		$ilCtrl = $this->ctrl;
		$this->getParentGUI()->insertSpecialPage(true);
	}
	
	/**
	* Collapse all
	*/
	function collapseAll()
	{
		$ilCtrl = $this->ctrl;
		
		$this->getParentGUI()->collapseAll(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	* Expand all
	*/
	function ExpandAll()
	{
		$ilCtrl = $this->ctrl;
		
		$this->getParentGUI()->expandAll(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	* Save Titles
	*/
	function saveAllTitles()
	{
		$ilCtrl = $this->ctrl;
		
		$this->getParentGUI()->saveAllTitles(false);
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	* Delete nodes in the hierarchy
	*/
	function deleteNodes()
	{
		$ilCtrl = $this->ctrl;

		$ilCtrl->setParameter($this, "backcmd", $_GET["backcmd"]);
		$this->getParentGUI()->deleteNodes($ilCtrl->getFormAction($this));
	}

	/**
	* cancel delete
	*/
	function cancelDelete()
	{
		$ilCtrl = $this->ctrl;
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	* confirmed delete
	*/
	function confirmedDelete()
	{
		$ilCtrl = $this->ctrl;
		
		$this->getParentGUI()->confirmedDelete(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	* Set Locator Items
	*/
	function setLocator()
	{
		$ilLocator = $this->locator;
		$tpl = $this->tpl;
		$ilCtrl = $this->ctrl;
		
		$ilLocator->addRepositoryItems($_GET["ref_id"]);
		$this->getParentGUI()->addLocatorItems();

		if ($_GET["obj_id"] > 0)
		{
			$tree = new ilTree($this->slm_object->getId());
			$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
			$tree->setTreeTablePK("slm_id");
			$path = $tree->getPathFull($_GET["obj_id"]);
			for( $i =  1; $i < count($path); $i++)
			{
//var_dump($path[$i]);
				switch($path[$i]["type"])
				{
					case "chap":
						$ilCtrl->setParameterByClass("ilscorm2004chaptergui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilscorm2004chaptergui",
							"showOrganization"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_chap.svg"));
						break;
						
					case "seqc":
						$ilCtrl->setParameterByClass("ilscorm2004seqchaptergui", "obj_id",
								$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilscorm2004seqchaptergui",
							"showOrganization"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_chap.svg"));
						break;	
						
					case "sco":
						$ilCtrl->setParameterByClass("ilscorm2004scogui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilscorm2004scogui",
							"showOrganization"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_sco.svg"));
						break;

					case "ass":
						$ilCtrl->setParameterByClass("ilscorm2004assetgui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilscorm2004assetgui",
							"showOrganization"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_sca.svg"));
						break;
						
					case "page":
						$ilCtrl->setParameterByClass("ilscorm2004pagegui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilscorm2004pagegui",
							"edit"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_pg.svg"));
						break;
				}
			}
		}
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		
		$tpl->setLocator();
	}

	/**
	 * Set content style sheet
	 */
	function setContentStyle()
	{
		$tpl = $this->tpl;
		
		// content styles
		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->slm_object->getStyleSheetId()));
		$tpl->setVariable("LOCATION_ADDITIONAL_STYLESHEET",
			ilObjStyleSheet::getPlaceHolderStylePath());
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
	}

		/**
	* Copy items to clipboard
	*/
	function copyItems($a_return = "showOrganization")
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;

		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();				// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}
		if (!ilSCORM2004Node::uniqueTypesCheck($items))
		{
			ilUtil::sendFailure($lng->txt("sahs_choose_pages_chap_scos_ass_only"), true);
			$ilCtrl->redirect($this, $a_return);
		}
		ilSCORM2004Node::clipboardCopy($this->slm_object->getId(), $items);

		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("copy");
		ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_copied"), true);

		$ilCtrl->redirect($this, $a_return);
	}

	/**
	* Copy items to clipboard, then cut them from the current tree
	*/
	function cutItems($a_return = "showOrganization")
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();			// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}
		
		if (!ilSCORM2004Node::uniqueTypesCheck($items))
		{
			ilUtil::sendFailure($lng->txt("sahs_choose_pages_chap_scos_ass_only"), true);
			$ilCtrl->redirect($this, $a_return);
		}

		ilSCORM2004Node::clipboardCut($this->slm_object->getId(), $items);
		
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("cut");

		ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_cut"), true);

		$ilCtrl->redirect($this, $a_return);
	}

	/**
	* Insert pages from clipboard
	*/
	function insertPageClip()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		
		ilSCORM2004Node::insertPageClip($this->slm_object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	 * Insert scos from clipboard
	 */
	function insertScoClip()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		
		ilSCORM2004Node::insertScoClip($this->slm_object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	 * Insert assets from clipboard
	 */
	function insertAssetClip()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		
		ilSCORM2004Node::insertAssetClip($this->slm_object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	 * Insert scos from clipboard
	 */
	function insertLMChapterClip()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		$this->setLocator();
		$this->setTabs();
		$this->getParentGUI()->insertLMChapterClip();
	}


}
?>
