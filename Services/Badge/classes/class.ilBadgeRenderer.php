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
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var \ILIAS\UI\Factory
	 */
	protected $factory;

	/**
	 * @var \ILIAS\UI\Renderer
	 */
	protected $renderer;

	protected $assignment; // [ilBadgeAssignment]
	protected $badge; // [ilBadge]
	
	public function __construct(ilBadgeAssignment $a_assignment = null, ilBadge $a_badge = null)
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->factory = $DIC->ui()->factory();
		$this->renderer = $DIC->ui()->renderer();
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
	
	public function getHTML()
	{				
		$components = array();

		$modal = $this->factory->modal()->roundtrip(
			$this->badge->getTitle(), $this->factory->legacy($this->renderModalContent())
		)->withCancelButtonLabel("ok");
		$components[] = $modal;

		$image_path = ilWACSignedPath::signFile($this->badge->getImagePath());
		$image = $this->factory->image()->responsive($image_path, $this->badge->getTitle())
			->withAction($modal->getShowSignal());
		$components[] = $image;

		return $this->renderer->render($components);
	}
	
	public function renderModalContent()
	{
		$lng = $this->lng;
		$lng->loadLanguageModule("badge");

		$modal_content = array();

		$image = $this->factory->image()->responsive($this->badge->getImagePath(), $this->badge->getImage());
		$modal_content[] = $image;

		$badge_information = [
			$lng->txt("description")=>$this->badge->getDescription(),
			$lng->txt("badge_criteria")=>$this->badge->getCriteria(),
		];

		if($this->assignment)
		{
			$badge_information[$lng->txt("badge_issued_on")] = ilDatePresentation::formatDate(
				new ilDateTime($this->assignment->getTimestamp(), IL_CAL_UNIX)
			);
		}

		if($this->badge->getParentId())
		{
			$parent = $this->badge->getParentMeta();	
			if($parent["type"] != "bdga")
			{
				$parent_icon = $this->factory->symbol()->icon()->custom(
					ilObject::_getIcon($parent["id"], "big", $parent["type"]), $lng->txt("obj_".$parent["type"])
				)->withSize("medium");

				$parent_icon_with_text = $this->factory->legacy($this->renderer->render($parent_icon) . $parent["title"]);
				$badge_information[$lng->txt("object")] = $parent_icon_with_text;
			}				
		}
		
		if($this->badge->getValid())
		{
			$badge_information[$lng->txt("badge_valid")] = $this->badge->getValid();
		}

		$list = $this->factory->listing()->descriptive($badge_information);
		$modal_content[] = $list;

		return $this->renderer->render($modal_content);
	}
}