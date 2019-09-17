<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Badge/classes/class.ilBadge.php";

/**
 * Class ilBadgeRenderer
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeRenderer
{
	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	protected $assignment; // [ilBadgeAssignment]
	protected $badge; // [ilBadge]
	
	protected static $init; // [bool]
	
	public function __construct(ilBadgeAssignment $a_assignment = null, ilBadge $a_badge = null)
	{
		global $DIC;

		$this->tpl = $DIC["tpl"];
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		if($a_assignment)
		{
			$this->assignment = $a_assignment;					
			$this->badge = new ilBadge($this->assignment->getBadgeId());
		}
		else
		{
			$this->badge = $a_badge;
		}
	}
	
	public static function initFromId($a_id)
	{
		$id = explode("_", $_GET["id"]);
		if(sizeof($id) == 3)
		{
			$user_id = $id[0];
			$badge_id = $id[1];
			$hash = $id[2];
			
			if($user_id)
			{		
				include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
				$assignment = new ilBadgeAssignment($badge_id, $user_id);
				if($assignment->getTimestamp())
				{
					$obj = new self($assignment);							
				}
			}
			else
			{
				include_once "Services/Badge/classes/class.ilBadge.php";
				$badge = new ilBadge($badge_id);
				$obj = new self(null, $badge);
			}
			if($hash == $obj->getBadgeHash())
			{
				return $obj;
			}		
		}
	}
	
	public function getHTML()
	{				
		$tpl = $this->tpl;
		$ilCtrl = $this->ctrl;
		
		if(!self::$init)
		{
			self::$init = true;
			
			$url = $ilCtrl->getLinkTargetByClass("ilBadgeHandlerGUI", 
				"render", "", true, false);
			
			$tpl->addJavaScript("Services/Badge/js/ilBadgeRenderer.js");
			$tpl->addOnLoadCode('il.BadgeRenderer.init("'.$url.'");');
		}
				
		$hash = $this->getBadgeHash();

		$btpl = new ilTemplate("tpl.badge_renderer.html", true, true, "Services/Badge");
        $image_path = ilWACSignedPath::signFile($this->badge->getImagePath());
		$btpl->setVariable("BADGE_IMG", $image_path);
		$btpl->setVariable("BADGE_TXT", $this->badge->getTitle());
		$btpl->setVariable("BADGE_ID", "badge_".
			($this->assignment 
				? $this->assignment->getUserId()
				: "")."_".
			$this->badge->getId()."_".
			$hash);	
		return $btpl->get();
	}
	
	public function getHref()
	{
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		
		if(!self::$init)
		{
			self::$init = true;
			
			$url = $ilCtrl->getLinkTargetByClass("ilBadgeHandlerGUI", 
				"render", "", true, false);
			
			$tpl->addJavaScript("Services/Badge/js/ilBadgeRenderer.js");
			$tpl->addOnLoadCode('il.BadgeRenderer.init("'.$url.'");');
		}
				
		$hash = $this->getBadgeHash();
		
		return "#\" data-id=\"badge_".
			($this->assignment 
				? $this->assignment->getUserId()
				: "")."_".
			$this->badge->getId()."_".
			$hash;	
	}
	
	protected function getBadgeHash()
	{
		return md5("bdg-".
			($this->assignment 
				? $this->assignment->getUserId()
				: "")."-".
			$this->badge->getId());
	}
	
	public function renderModal()
	{
		$lng = $this->lng;
		
		include_once "Services/UIComponent/Modal/classes/class.ilModalGUI.php";
		
		// only needed for modal-js-calls
		// ilModalGUI::initJS();
		
		$modal = ilModalGUI::getInstance();
		$modal->setId("badge_modal_".$this->getBadgeHash());
		$modal->setType(ilModalGUI::TYPE_SMALL);
		$modal->setHeading($this->badge->getTitle());
		
		$lng->loadLanguageModule("badge");
		
		$tpl = new ilTemplate("tpl.badge_modal.html", true, true, "Services/Badge");
		
		$tpl->setVariable("IMG_SRC", $this->badge->getImagePath());
		$tpl->setVariable("IMG_TXT", $this->badge->getImage());
		
		$tpl->setVariable("TXT_DESC", $lng->txt("description"));	
		$tpl->setVariable("DESC", nl2br($this->badge->getDescription()));
		
		$tpl->setVariable("TXT_CRITERIA", $lng->txt("badge_criteria"));	
		$tpl->setVariable("CRITERIA", nl2br($this->badge->getCriteria()));
		
		if($this->assignment)
		{
			$tpl->setVariable("TXT_TSTAMP", $lng->txt("badge_issued_on"));	
			$tpl->setVariable("TSTAMP", 
				ilDatePresentation::formatDate(new ilDateTime($this->assignment->getTimestamp(), IL_CAL_UNIX)));		
		}

		if($this->badge->getParentId())
		{
			$parent = $this->badge->getParentMeta();	
			if($parent["type"] != "bdga")
			{												
				$tpl->setVariable("TXT_PARENT", $lng->txt("object"));			
				$tpl->setVariable("PARENT", $parent["title"]);
				$tpl->setVariable("PARENT_TYPE", $lng->txt("obj_".$parent["type"]));
				$tpl->setVariable("PARENT_ICON", 
					ilObject::_getIcon($parent["id"], "big", $parent["type"]));
			}				
		}
		
		if($this->badge->getValid())
		{
			$tpl->setVariable("TXT_VALID", $lng->txt("badge_valid"));		
			$tpl->setVariable("VALID", $this->badge->getValid());		
		}
		
		$modal->setBody($tpl->get());
		
		return $modal->getHTML();
	}
}