<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarAgendaListGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var \ILIAS\UI\Factory
	 */
	protected $ui_fac;

	/**
	 * @var \ILIAS\UI\Renderer
	 */
	protected $ui_ren;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->ui_fac = $DIC->ui()->factory();
		$this->ui_ren = $DIC->ui()->renderer();
		$this->user = $DIC->user();
		$this->lng = $DIC->language();
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("getHTML")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Get output
	 *
	 * @param
	 * @return
	 */
	function getHTML()
	{
		// get events
		$schedule = new ilCalendarSchedule(new ilDate(time(),IL_CAL_UNIX),ilCalendarSchedule::TYPE_INBOX);
		$schedule->setEventsLimit(50);
		$schedule->addSubitemCalendars(true);
		$schedule->calculate();

		$events = $schedule->getScheduledEvents();

		$df = new \ILIAS\Data\Factory();
		$items = array();
		$groups = array();
		$cday = "";
		foreach ($events as $e)
		{
			$begin = new ilDatetime($e['dstart'],IL_CAL_UNIX);
			$end = new ilDatetime($e['dend'],IL_CAL_UNIX);
			$day = ilDatePresentation::formatDate(new ilDate($e['dstart'],IL_CAL_UNIX));

			// new group starts
			if ($cday != $day)
			{
				// terminate preceding group
				if ($cday != "")
				{
					$groups[] = $this->ui_fac->item()->group($cday, $items);
				}
				$cday = $day;
				$items = array();
			}

			// get calendar
			$cat_id = ilCalendarCategoryAssignments::_lookupCategory($e["event"]->getEntryId());
			$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);



				//var_dump($e); exit;
			/*
			$actions = $f->dropdown()->standard(array(
				$f->button()->shy("ILIAS", "https://www.ilias.de"),
				$f->button()->shy("GitHub", "https://www.github.com")
			));*/

			/*$list_item2 = $f->item()->standard("Tech VC")
				->withActions($actions)
				->withProperties(array(
					"Origin" => "Course Title 1",
					"Last Update" => "24.11.2011",
					"Location" => "Room 123, Main Street 44, 3012 Bern"))
				->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
				->withColor($df->color('#F9F9D0'))
				->withLeadText("13:00 - 14:00");*/

			$properties = array();

			// properties: origin
			if ($cat_info['type'] == ilCalendarCategory::TYPE_OBJ)
			{
				//$type = ilObject::_lookupType($cat_info['obj_id']);
				//$refs = ilObject::_getAllReferences($cat_info['obj_id']);
				//include_once('./Services/Link/classes/class.ilLink.php');
				//$href = ilLink::_getStaticLink(current($refs),ilObject::_lookupType($cat_info['obj_id']),true);
			}
			$properties[$this->lng->txt('cal_origin')] = $cat_info["title"];

			// properties: last update

			$update = new ilDateTime($e["event"]->getLastUpdate()->get(IL_CAL_UNIX), IL_CAL_UNIX, $this->user->getTimeZone());
			$properties[$this->lng->txt('last_update')] = ilDatePresentation::formatDate($update);

			// properties: location
			if ($e["event"]->getLocation() != "")
			{
				$properties[$this->lng->txt('location')] = $e["event"]->getLocation();
			}

			$items[] = $this->ui_fac->item()->standard($e["event"]->getPresentationTitle())
				->withDescription($e["event"]->getDescription())
				->withLeadText(ilDatePresentation::formatPeriod($begin, $end, true))
				->withProperties($properties)
				->withColor($df->color('#'.$cat_info["color"]));
		}
		// terminate last group
		if ($cday != "")
		{
			$groups[] = $this->ui_fac->item()->group($cday, $items);
		}

		$list = $this->ui_fac->panel()->listing()->standard("Upcoming Events", $groups);


		return $this->ui_ren->render($list);

	}

}

?>