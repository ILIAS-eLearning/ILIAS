<?php
include_once './Services/Calendar/classes/class.ilCalendarViewGUI.php';
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar agenda list
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 * @ilCtrl_Calls ilCalendarAgendaListGUI: ilCalendarAppointmentPresentationGUI
 */
class ilCalendarAgendaListGUI extends ilCalendarViewGUI
{
	const PERIOD_DAY = 1;
	const PERIOD_WEEK = 2;
	const PERIOD_MONTH = 3;
	const PERIOD_HALF_YEAR = 4;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var int
	 */
	protected $period = self::PERIOD_WEEK;

	/**
	 * @var string
	 */
	protected $seed;

	/**
	 * Constructor
	 */
	function __construct()
	{
		//$DIC elements initialization
		$this->initialize(ilCalendarViewGUI::CAL_PRESENTATION_AGENDA_LIST);

		$this->ctrl->saveParameter($this, "cal_agenda_per");

		//$qp = $DIC->http()->request()->getQueryParams();
		$qp = $_GET;
		if ((int) $qp["cal_agenda_per"] > 0 && (int) $qp["cal_agenda_per"] <= 4)
		{
			$this->period = $qp["cal_agenda_per"];
		}

		$this->seed = $qp["seed"];
		$end_date = new ilDate($this->seed." 00:00:00", IL_CAL_DATETIME);
		switch ($this->period)
		{
			case self::PERIOD_DAY:
				$end_date->increment(IL_CAL_DAY, 1);
				break;

			case self::PERIOD_WEEK:
				$end_date->increment(IL_CAL_WEEK, 1);
				break;

			case self::PERIOD_MONTH:
				$end_date->increment(IL_CAL_MONTH, 1);
				break;

			case self::PERIOD_HALF_YEAR:
				$end_date->increment(IL_CAL_MONTH, 6);
				break;
		}
		$this->period_end_day = $end_date->get(IL_CAL_DATE);
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
			case "ilcalendarappointmentpresentationgui":
				$this->ctrl->setReturn($this, "");
				include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
				$gui = ilCalendarAppointmentPresentationGUI::_getInstance(new ilDate($this->seed, IL_CAL_DATE), $this->getCurrentApp());
				$this->ctrl->forwardCommand($gui);
				break;

			default:
				$this->ctrl->setReturn($this, "");
				if (in_array($cmd, array("getHTML", "getModalForApp")))
				{
					return $this->$cmd();
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
		$navigation = new ilCalendarHeaderNavigationGUI($this,new ilDate($this->seed, IL_CAL_DATE),ilDateTime::DAY);
		$navigation->getHTML();

		// set return now (after header navigation) to the list (e.g. for profile links)
		$this->ctrl->setReturn($this, "");

		// get events
		$events = $this->getEvents();
		$events = ilUtil::sortArray($events, "dstart", "asc", true);

		$df = new \ILIAS\Data\Factory();
		$items = array();
		$groups = array();
		$modals = array();
		$cday = "";
		foreach ($events as $e)
		{
			$begin = new ilDatetime($e['dstart'],IL_CAL_UNIX);
			$end = new ilDatetime($e['dend'],IL_CAL_UNIX);
			$day = ilDatePresentation::formatDate(new ilDate($e['dstart'],IL_CAL_UNIX), false, true);

			// new group starts
			if ($cday != $day)
			{
				// terminate preceding group
				if ($cday != "")
				{
					$groups[] = $this->ui_factory->item()->group($cday, $items);
				}
				$cday = $day;
				$items = array();
			}

			// get calendar
			$cat_id = ilCalendarCategoryAssignments::_lookupCategory($e["event"]->getEntryId());
			$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);

			$properties = array();

			// shy button for title
			$this->ctrl->setParameter($this, 'app_id', $e["event"]->getEntryId());
			$this->ctrl->setParameter($this,'dt',$e['dstart']);
			$this->ctrl->setParameter($this, 'seed', $this->seed);
			$url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
			$this->ctrl->setParameter($this, "app_id", $_GET["app_id"]);
			$this->ctrl->setParameter($this, "dt", $_GET["dt"]);
			$modal = $this->ui_factory->modal()->roundtrip('', [])->withAsyncRenderUrl($url);
			$shy = $this->ui_factory->button()->shy($e["event"]->getPresentationTitle(), "")->withOnClick($modal->getShowSignal());
			$modals[] = $modal;

			$li = $this->ui_factory->item()->standard($shy)
				->withDescription("".$e["event"]->getDescription())
				->withLeadText(ilDatePresentation::formatPeriod($begin, $end, true))
				->withProperties($properties)
				->withColor($df->color('#'.$cat_info["color"]));

			// add type specific actions/properties
			include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
			$app_gui = ilCalendarAppointmentPresentationGUI::_getInstance(new ilDate($this->seed, IL_CAL_DATE), $e);
			$app_gui->setListItemMode($li);
			$this->ctrl->getHTML($app_gui);
			$items[] = $app_gui->getListItem();

		}
		// terminate last group
		if ($cday != "")
		{
			$groups[] = $this->ui_factory->item()->group($cday, $items);
		}

		// list actions
		$items = array();
		$this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_DAY);
		$items[] = $this->ui_factory->button()->shy("1 ".$this->lng->txt("day"), $this->ctrl->getLinkTarget($this, "getHTML"));
		$this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_WEEK);
		$items[] = $this->ui_factory->button()->shy("1 ".$this->lng->txt("week"), $this->ctrl->getLinkTarget($this, "getHTML"));
		$this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_MONTH);
		$items[] = $this->ui_factory->button()->shy("1 ".$this->lng->txt("month"), $this->ctrl->getLinkTarget($this, "getHTML"));
		$this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_HALF_YEAR);
		$items[] = $this->ui_factory->button()->shy("6 ".$this->lng->txt("months"), $this->ctrl->getLinkTarget($this, "getHTML"));
		$this->ctrl->setParameter($this, "cal_agenda_per", $this->period);

		$actions = $this->ui_factory->dropdown()->standard($items)->withLabel($this->lng->txt("days"));

		$list_title = $this->lng->txt("cal_agenda").": ".ilDatePresentation::formatDate(new ilDate($this->seed, IL_CAL_DATE));
		if ($this->period != self::PERIOD_DAY)
		{
			$list_title.= " - ".ilDatePresentation::formatDate(new ilDate($this->period_end_day, IL_CAL_DATE));
		}

		$list = $this->ui_factory->panel()->listing()->standard($list_title, $groups)
			->withActions($actions);


		$comps = array_merge($modals, array($list));

		return $this->ui_renderer->render($comps);

	}

}

?>