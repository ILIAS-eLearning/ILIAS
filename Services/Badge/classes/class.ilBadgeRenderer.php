<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	protected $assignment; // [ilBadgeAssignment]
	
	public function __construct(ilBadgeAssignment $a_assignment)
	{
		$this->assignment = $a_assignment;
	}
	
	public function getHTML()
	{
		global $lng;
		
		include_once "Services/Badge/classes/class.ilBadge.php";
		include_once "Services/UIComponent/Modal/classes/class.ilModalGUI.php";
		// ilModalGUI::initJS();
		
		$badge = new ilBadge($this->assignment->getBadgeId());

		$modal_hash = md5("bdg-".$this->assignment->getUserId()."-".$badge->getId());

		$modal = ilModalGUI::getInstance();
		$modal->setId($modal_hash);
		$modal->setType(ilModalGUI::TYPE_SMALL);
		$modal->setHeading($badge->getTitle());
		
		$lng->loadLanguageModule("badge");
		
		$tpl = new ilTemplate("tpl.badge_modal.html", true, true, "Services/Badge");
		
		$tpl->setVariable("IMG_SRC", $badge->getImagePath());
		$tpl->setVariable("IMG_TXT", $badge->getImage());
		
		$tpl->setVariable("DESC", nl2br($badge->getDescription()));
		
		$tpl->setVariable("TXT_VALID", $lng->txt("badge_valid"));		
		$tpl->setVariable("VALID", $badge->getValid());		
		
		$tpl->setVariable("TXT_TSTAMP", $lng->txt("created"));	
		$tpl->setVariable("TSTAMP", 
			ilDatePresentation::formatDate(new ilDateTime($this->assignment->getTimestamp(), IL_CAL_UNIX)));						
		$tpl->setVariable("TXT_PARENT", $lng->txt("container"));	
		
		$parent = $badge->getParentMeta();
		$tpl->setVariable("PARENT", 
			"(".$parent["type"]."/".$parent["id"].") ".$parent["title"]);
		
		$modal->setBody($tpl->get());

		$tpl = new ilTemplate("tpl.badge_renderer.html", true, true, "Services/Badge");		
		$tpl->setVariable("BADGE_IMG", $badge->getImagePath());
		$tpl->setVariable("BADGE_TXT", $badge->getTitle());
		$tpl->setVariable("BADGE_HASH", $modal_hash);
		$tpl->setVariable("BADGE_META", $modal->getHTML());		
		return $tpl->get();
	}
}

