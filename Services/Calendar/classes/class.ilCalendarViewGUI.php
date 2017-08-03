<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarViewGUI
{
	/**
	 * @var \ILIAS\UI\Factory
	 */
	protected $ui_factory;

	/**
	 * @var \ILIAS\UI\Renderer
	 */
	protected $ui_renderer;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	function __construct()
	{
		global $DIC;

		$this->ui_factory = $DIC->ui()->factory();
		$this->ui_renderer = $DIC->ui()->renderer();
		$this->ctrl = $DIC->ctrl();
	}

	/**
	 * Get app for id
	 *
	 * @param
	 * @return
	 */
	function getCurrentApp()
	{
		// @todo: this needs optimization
		$events = $this->getEvents();
		foreach ($events as $item)
		{
			if ($item["event"]->getEntryId() == (int) $_GET["app_id"])
			{
				return $item;
			}
		}
		return null;
	}

	/**
	 * Get events
	 *
	 * @param
	 * @return
	 */
	function getEvents()
	{
//		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
//		initialize($a_mode,$a_source_ref_id = 0,$a_use_cache = false)

		$schedule = new ilCalendarSchedule(new ilDate(time(),IL_CAL_UNIX),ilCalendarSchedule::TYPE_PD_UPCOMING);

		//todo: mmm find a best conditional. if period end day we are using a list otherwise day,month,week
		if($this->period_end_day)
		{
			$schedule->setPeriod(new ilDate($this->seed, IL_CAL_DATE),
				new ilDate($this->period_end_day, IL_CAL_DATE));
		}
		else
		{
			//todo: day, week month??¿?
			$schedule = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_WEEK);

		}

		//return $schedule->getChangedEvents(true);

		$schedule->addSubitemCalendars(true);
		$schedule->calculate();
		$ev = $schedule->getScheduledEvents();
		return $ev;
	}


	/**
	 * Get start/end date for item
	 *
	 * @param array $item item
	 * @return array
	 */
	function getDatesForItem($item)
	{
		$start = $item["dstart"];
		$end = $item["dend"];
		if($item["fullday"])
		{
			$start = new ilDate($start, IL_CAL_UNIX);
			$end = new ilDate($end, IL_CAL_UNIX);
		}
		else
		{
			$start = new ilDateTime($start, IL_CAL_UNIX);
			$end = new ilDateTime($end, IL_CAL_UNIX);
		}
		return array("start" => $start, "end" => $end);
	}

	/**
	 * Get modal for appointment (see similar code in ilCalendarBlockGUI)
	 */
	function getModalForApp()
	{
		global $DIC;

		$f = $DIC->ui()->factory();
		$r = $DIC->ui()->renderer();
		$ctrl = $DIC->ctrl();

		// @todo: this needs optimization
		$events = $this->getEvents();

		//item => array containing ilcalendary object, dstart of the event , dend etc.
		foreach ($events as $item)
		{
			$DIC->logger()->cal()->debug(" GET['dt'] => ".$_GET['dt']);
			$DIC->logger()->cal()->debug("item start => ".$item['event']->getTitle());
			$DIC->logger()->cal()->debug("item start => ".$item['dstart']);

			if ($item["event"]->getEntryId() == (int) $_GET["app_id"] && $item['dstart'] == (int) $_GET['dt'])
			{
				$dates = $this->getDatesForItem($item);
				// content of modal
				include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
				$next_gui = ilCalendarAppointmentPresentationGUI::_getInstance(new ilDate($this->seed, IL_CAL_DATE), $item);
				$content = $ctrl->getHTML($next_gui);
				$modal = $f->modal()->roundtrip(ilDatePresentation::formatPeriod($dates["start"], $dates["end"]),$f->legacy($content));
				echo $r->renderAsync($modal);
			}
		}
		exit();
	}

	function getAppointmentShyButton($a_appointment)
	{
		$f = $this->ui_factory;
		$r = $this->ui_renderer;

		$this->ctrl->setParameter($this, "app_id", $a_appointment["event"]->getEntryId());
		$this->ctrl->setParameter($this,'dt',$a_appointment['dstart']);
		$this->ctrl->setParameter($this,'seed',$this->seed->get(IL_CAL_DATE));
		$url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
		$this->ctrl->setParameter($this, "app_id", $_GET["app_id"]);
		$this->ctrl->setParameter($this, "dt", $_GET["dt"]);
		$this->ctrl->setParameter($this,'seed',$_GET["seed"]);

		$modal = $f->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

		$comps = [$f->button()->shy($a_appointment["event"]->getPresentationTitle(), "")->withOnClick($modal->getShowSignal()), $modal];

		return $r->render($comps);
	}

}