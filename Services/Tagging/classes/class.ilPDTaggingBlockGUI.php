<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* ilPDTaggingBlockGUI displays personal tag cloud on personal desktop.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDTaggingBlockGUI: ilColumnGUI
*/
class ilPDTaggingBlockGUI extends ilBlockGUI
{
	/**
	 * @var ilObjectDefinition
	 */
	protected $obj_definition;

	static $block_type = "pdtag";
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->obj_definition = $DIC["objDefinition"];
		$this->access = $DIC->access();
		$lng = $DIC->language();

		parent::__construct();
		
		$lng->loadLanguageModule("tagging");

		$this->setTitle($lng->txt("tagging_my_tags"));
		$this->setEnableNumInfo(false);
		$this->setLimit(99999);
	}

	/**
	 * @inheritdoc
	 */
	public function getBlockType(): string 
	{
		return self::$block_type;
	}

	/**
	 * @inheritdoc
	 */
	protected function isRepositoryObject(): bool 
	{
		return false;
	}

	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		switch($_GET["cmd"])
		{
			case "showResourcesForTag":
				return IL_SCREEN_CENTER;
				break;

			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		return $this->$cmd();
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		$ilUser = $this->user;
		
		include_once("./Services/Tagging/classes/class.ilTagging.php");
		$this->tags = ilTagging::getTagsForUser($ilUser->getId(), 1000000);

		if (count($this->tags) > 0)
		{
			$this->setDataSection($this->getTagCloud());
		}
		else
		{
			$this->setDataSection($this->getOverview());
		}
	}
	
	/**
	* get tree bookmark list for personal desktop
	*/
	function getTagCloud()
	{
		$ilCtrl = $this->ctrl;

		$tpl = new ilTemplate("tpl.tag_cloud.html", true, true,
			"Services/Tagging");
		$max = 1;
		foreach($this->tags as $tag)
		{
			$max = max($tag["cnt"], $max);
		}
		reset($this->tags);

		foreach($this->tags as $tag)
		{
			$tpl->setCurrentBlock("linked_tag");
			$ilCtrl->setParameter($this, "tag", rawurlencode($tag["tag"]));
			$tpl->setVariable("HREF_TAG",
				$ilCtrl->getLinkTarget($this, "showResourcesForTag"));
			$tpl->setVariable("TAG_TITLE", $tag["tag"]);
			$tpl->setVariable("REL_CLASS",
				ilTagging::getRelevanceClass($tag["cnt"], $max));
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}
	
	/**
	* List resources for tag
	*/
	function showResourcesForTag()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		$objDefinition = $this->obj_definition;
		
		$_GET["tag"] = str_replace("-->","", $_GET["tag"]);
		
		$tpl = new ilTemplate("tpl.resources_for_tag.html", true, true, "Services/Tagging");
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setColSpan(2);
		$content_block->setTitle(sprintf($lng->txt("tagging_resources_for_tag"),
			"<i>".$_GET["tag"]."</i>"));
		$content_block->setImage(ilUtil::getImagePath("icon_tag.svg"));

		// get resources
		include_once("./Services/Tagging/classes/class.ilTagging.php");
		$objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $_GET["tag"]);

		$unaccessible = false;
		foreach($objs as $key => $obj)
		{
			$ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
			foreach($ref_ids as $ref_id)
			{
				$type = $obj["obj_type"];
				
				if ($type == "") {
					$unaccessible = true;
					continue;
				}
				
				// get list gui class for each object type
				if (empty($this->item_list_gui[$type]))
				{
					$class = $objDefinition->getClassName($type);
					$location = $objDefinition->getLocation($type);
			
					$full_class = "ilObj".$class."ListGUI";
			
					include_once($location."/class.".$full_class.".php");
					$this->item_list_gui[$type] = new $full_class();
					$this->item_list_gui[$type]->enableDelete(false);
					$this->item_list_gui[$type]->enablePath(true);
					$this->item_list_gui[$type]->enableCut(false);
					$this->item_list_gui[$type]->enableCopy(false);
					$this->item_list_gui[$type]->enableSubscribe(false);
					$this->item_list_gui[$type]->enableLink(false);
					$this->item_list_gui[$type]->enableIcon(true);
				}
				$html = $this->item_list_gui[$type]->getListItemHTML(
					$ref_id,
					$obj["obj_id"], 
					ilObject::_lookupTitle($obj["obj_id"]),
					ilObject::_lookupDescription($obj["obj_id"]));
					
				if ($html != "")
				{
					$css = ($css != "tblrow1") ? "tblrow1" : "tblrow2";
						
					$tpl->setCurrentBlock("res_row");
					$tpl->setVariable("ROWCLASS", $css);
					$tpl->setVariable("RESOURCE_HTML", $html);
					$tpl->setVariable("ALT_TYPE", $lng->txt("obj_".$type));
					$tpl->setVariable("IMG_TYPE",
						ilUtil::getImagePath("icon_".$type.".svg"));
					$tpl->parseCurrentBlock();
				}
				else
				{
					$unaccessible = true;
				}
			}
		}

		if ($unaccessible)
		{
			$tpl->setCurrentBlock("no_access");
			$tpl->setVariable("SOME_OBJ_WITHOUT_ACCESS", $lng->txt("tag_some_obj_tagged_without_access"));
			$ilCtrl->saveParameter($this, "tag");
			$tpl->setVariable("HREF_REMOVE_TAGS", $ilCtrl->getLinkTarget($this, "removeTagsWithoutAccess"));
			$tpl->setVariable("REMOVE_TAGS", $lng->txt("tag_remove_tags_of_obj_without_access"));
			$tpl->parseCurrentBlock();
		}

		$content_block->setContent($tpl->get());
		//$content_block->setContent("test");

		return $content_block->getHTML();
	}

	/**
	 * Remove tasg without access
	 */
	function removeTagsWithoutAccess()
	{
		$ilCtrl = $this->ctrl;
		$ilAccess = $this->access;
		$ilUser = $this->user;
		$lng = $this->lng;

		// get resources
		include_once("./Services/Tagging/classes/class.ilTagging.php");
		$objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $_GET["tag"]);

		foreach($objs as $key => $obj)
		{
			$ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
			if (count($ref_ids) == 0)
			{
				$inaccessible = true;
			}
			else
			{
				$inaccessible = false;
			}
			foreach ($ref_ids as $ref_id)
			{
				$type = $obj["obj_type"];

				if ($type == "") {
					$inaccessible = true;
					continue;
				}
				if (!$ilAccess->checkAccess("visible", "", $ref_id) &&
					!$ilAccess->checkAccess("read", "", $ref_id) &&
					!$ilAccess->checkAccess("write", "", $ref_id))
				{
					$inaccessible = true;
				}
				if ($inaccessible)
				{
					ilTagging::deleteTagOfObjectForUser($ilUser->getId(), $obj["obj_id"], $obj["obj_type"], $obj["sub_obj_id"], $obj["sub_obj_type"], $_GET["tag"]);
				}
			}
		}

		ilUtil::sendSuccess($lng->txt("tag_tags_deleted"), true);

		$ilCtrl->returnToParent($this);
	}


	/**
	* block footer
	*/
	function fillFooter()
	{
		$this->fillFooterLinks();
		$this->tpl->setVariable("FCOLSPAN", $this->getColSpan());
		if ($this->tpl->blockExists("block_footer"))
		{
			$this->tpl->setCurrentBlock("block_footer");
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* Get overview.
	*/
	function getOverview()
	{
		$lng = $this->lng;

		return '<div class="small">'.$lng->txt("tagging_tag_info")."</div>";
	}

}

?>
