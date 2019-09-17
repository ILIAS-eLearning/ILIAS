<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");
include_once './Services/Calendar/classes/class.ilCalendarCategories.php';

/**
* Calendar blocks, displayed in different contexts, e.g. groups and courses
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilCalendarBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilCalendarBlockGUI: ilCalendarAppointmentGUI, ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarDayGUI
* @ilCtrl_Calls ilCalendarBlockGUI: ilConsultationHoursGUI, ilCalendarAppointmentPresentationGUI
*
* @ingroup ServicesCalendar
*/
class ilCalendarBlockGUI extends ilBlockGUI
{
	/**
	 * @var ilCtrl|null
	 */
	public $ctrl = null;
	protected $mode;
	protected $display_mode;

	static $block_type = "cal";
	static $st_data;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var
	 */
	protected $obj_data_cache;

	protected $parent_gui = "ilcolumngui";

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	protected $force_month_view = false;

	/**
	* Constructor
	*
	* @param	boolean		skip initialisation (is called by derived PDCalendarBlockGUI class)
	*/
	function __construct()
	{
		global $DIC;

		parent::__construct();

		$this->tabs = $DIC->tabs();
		$this->obj_data_cache = $DIC["ilObjDataCache"];
		$this->ui = $DIC->ui();

		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$tpl = $this->main_tpl;
		$ilUser = $this->user;
		$ilHelp = $DIC["ilHelp"];


		$lng->loadLanguageModule("dateplaner");
		$ilHelp->addHelpSection("cal_block");
		
		include_once("./Services/News/classes/class.ilNewsItem.php");

		$ilCtrl->saveParameter($this, 'bkid');

		$this->setBlockId($ilCtrl->getContextObjId());

		$this->setLimit(5);			// @todo: needed?
		
		// alex: original detail level 1 did not work anymore
		$this->setAvailableDetailLevels(1);
		$this->setEnableNumInfo(false);

		//if(!isset($_GET["bkid"]))
		//{
			$title = $lng->txt("calendar");
		//}
		//else
		//{
		//	$title = $lng->txt("cal_consultation_hours_for")." ".ilObjUser::_lookupFullname($_GET["bkid"]);
		//}
				
		$this->setTitle($title);
		//$this->setData($data);
		$this->allow_moving = false;
		//$this->handleView();
		
		include_once('Services/Calendar/classes/class.ilDate.php');
		include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
		
		$seed_str = "";
		if ((!isset($_GET["seed"]) || $_GET["seed"] == "") &&
			isset($_SESSION["il_cal_block_".$this->getBlockType()."_".$this->getBlockId()."_seed"]))
		{
			$seed_str = $_SESSION["il_cal_block_".$this->getBlockType()."_".$this->getBlockId()."_seed"];
		}
		else if (isset($_GET["seed"]))
		{
			$seed_str =  $_GET["seed"];
		}
			
		if (isset($_GET["seed"]) && $_GET["seed"] != "")
		{
			$_SESSION["il_cal_block_".$this->getBlockType()."_".$this->getBlockId()."_seed"]
				= $_GET["seed"];
		}

		if ($seed_str == "")
		{
			$this->seed = new ilDate(time(),IL_CAL_UNIX);	// @todo: check this
		}
		else
		{
			$this->seed = new ilDate($seed_str,IL_CAL_DATE);	// @todo: check this
		}

		$this->settings = ilCalendarSettings::_getInstance();
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
		
		$mode = $ilUser->getPref("il_pd_cal_mode");
		$this->display_mode = $mode ? $mode : "mmon";
	}

	/**
	 * Show weeks column
	 *
	 * @param
	 * @return
	 */
	function getShowWeeksColumn()
	{
		return ($this->settings->getShowWeeks() && $this->user_settings->getShowWeeks());
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
	 * Set parent gui
	 *
	 * @param  $a_val
	 */
	function setParentGUI($a_val)
	{
		$this->parent_gui = $a_val;
	}

	/**
	 * Get  parent gui
	 *
	 * @return
	 */
	function getParentGUI()
	{
		return $this->parent_gui;
	}

	/**
	 * Set force month view
	 *
	 * @param bool $a_val force month view	
	 */
	function setForceMonthView($a_val)
	{
		$this->force_month_view = $a_val;
		if ($a_val)
		{
			$this->display_mode = "mmon";
		}
	}
	
	/**
	 * Get force month view
	 *
	 * @return bool force month view
	 */
	function getForceMonthView()
	{
		return $this->force_month_view;
	}

	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		global $DIC;

		$ilCtrl = $DIC->ctrl();

		$cmd_class = $ilCtrl->getCmdClass();
		
		if ($cmd_class == "ilcalendarappointmentgui" ||
			$cmd_class == "ilconsultationhoursgui" ||
			$_GET['cmd'] == 'showCalendarSubscription')
		{
			return IL_SCREEN_CENTER;
		}
		
		switch($ilCtrl->getCmd())
		{
			case "kkk":
			// return IL_SCREEN_CENTER;
			// return IL_SCREEN_FULL;
			
			default:
				//return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		$ilTabs = $this->tabs;


		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");
		
		$this->setSubTabs();
		
		switch ($next_class)
		{
			case "ilcalendarappointmentgui":
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app_gui = new ilCalendarAppointmentGUI($this->seed,$this->seed);
				$ilCtrl->forwardCommand($app_gui);
				break;
				
			case "ilconsultationhoursgui":
				include_once('./Services/Calendar/classes/ConsultationHours/class.ilConsultationHoursGUI.php');
				$hours = new ilConsultationHoursGUI($this->seed);
				$ilCtrl->forwardCommand($hours);
				break;

			case "ilcalendarappointmentpresentationgui":
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php');
				$presentation = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $this->appointment);
				$ilCtrl->forwardCommand($presentation);
				break;

			case "ilcalendarmonthgui":
				$ilTabs->setSubTabActive('app_month');
				include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
				$month_gui = new ilCalendarMonthGUI($this->seed);
				$ilCtrl->forwardCommand($month_gui);
				break;

			default:
				return $this->$cmd();
		}
	}

	/**
	* Set EnableEdit.
	*
	* @param	boolean	$a_enable_edit	Edit mode on/off
	*/
	public function setEnableEdit($a_enable_edit = 0)
	{
		$this->enable_edit = $a_enable_edit;
	}

	/**
	* Get EnableEdit.
	*
	* @return	boolean	Edit mode on/off
	*/
	public function getEnableEdit()
	{
		return $this->enable_edit;
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		// alex: changed from > 1 to > 0 - original detail level 1 did not work anymore
		if ($this->getCurrentDetailLevel() > 0 && $this->display_mode != "mmon")
		{
			$this->setColSpan(1);
			$this->setRowTemplate("tpl.pd_event_list.html", "Services/Calendar");

			ilBlockGUI::fillDataSection();
		}
		else
		{
			// alex: changed from > 1 to > 0 - original detail level 1 did not work anymore
			if ($this->getCurrentDetailLevel() > 0)
			{
				$tpl = new ilTemplate("tpl.calendar_block.html", true, true,
					"Services/Calendar");

				$this->addMiniMonth($tpl);
				$this->setDataSection($tpl->get());
			}
			else
			{
				$this->setDataSection($this->getOverview());
			}
		}		
	}

	/**
	 * Get target gui class path (for presenting the calendar)
	 *
	 * @param
	 * @return
	 */
	function getTargetGUIClassPath()
	{
		$target_class = array();
		if (!$this->getRepositoryMode())
		{
			$target_class = array("ilpersonaldesktopgui", "ilcalendarpresentationgui");
		}
		else
		{
			switch (ilObject::_lookupType((int) $_GET["ref_id"], true))
			{
				case "crs":
					$target_class = array("ilobjcoursegui", "ilcalendarpresentationgui");
					break;

				case "grp":
					$target_class = array("ilobjgroupgui", "ilcalendarpresentationgui");
					break;
			}
		}
		return $target_class;
	}

	/**
	* Add mini version of monthly overview
	* (Maybe extracted to another class, if used in pd calendar tab
	*/
	function addMiniMonth($a_tpl)
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		$ui = $this->ui;


		// weekdays
		include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
		if ($this->getShowWeeksColumn())
		{
			$a_tpl->setCurrentBlock('month_header_col');
			$a_tpl->setVariable('TXT_WEEKDAY', $lng->txt("cal_week_abbrev"));
			$a_tpl->parseCurrentBlock();
		}
		for($i = (int) $this->user_settings->getWeekStart();$i < (7 + (int) $this->user_settings->getWeekStart());$i++)
		{
			$a_tpl->setCurrentBlock('month_header_col');
			$a_tpl->setVariable('TXT_WEEKDAY',ilCalendarUtil::_numericDayToString($i,false));
			$a_tpl->parseCurrentBlock();
		}

		if(isset($_GET["bkid"]))
		{
			$user_id = $_GET["bkid"];
			$disable_empty = true;
		}
		else
		{
			$user_id = $ilUser->getId();
			$disable_empty = false;
		}
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_MONTH,$user_id);
		$this->scheduler->addSubitemCalendars(true);		
		$this->scheduler->calculate();
		
		$counter = 0;
		foreach(ilCalendarUtil::_buildMonthDayList($this->seed->get(IL_CAL_FKT_DATE,'m'),
			$this->seed->get(IL_CAL_FKT_DATE,'Y'),
			$this->user_settings->getWeekStart())->get() as $date)
		{
			$counter++;

			$events = $this->scheduler->getByDay($date,$ilUser->getTimeZone());
			$has_events = (bool)count($events);
			if($has_events || !$disable_empty)
			{
				$a_tpl->setCurrentBlock('month_col_link');
			}
			else
			{
				$a_tpl->setCurrentBlock('month_col_no_link');
			}

			if($disable_empty)
			{
				if(!$has_events)
				{
					$a_tpl->setVariable('DAY_CLASS','calminiinactive');
				}
				else
				{
					$week_has_events = true;
					include_once 'Services/Booking/classes/class.ilBookingEntry.php';
					foreach($events as $event)
					{
						$booking = new ilBookingEntry($event['event']->getContextId());
						if($booking->hasBooked($event['event']->getEntryId()))
						{
							$a_tpl->setVariable('DAY_CLASS','calminiapp');
							break;
						}
					}
				}
			}
			elseif($has_events)
			{
				$week_has_events = true;
				$a_tpl->setVariable('DAY_CLASS','calminiapp');
			}
			
			
			$day = $date->get(IL_CAL_FKT_DATE,'j');
			$month = $date->get(IL_CAL_FKT_DATE,'n');
			
			$month_day = $day;

			$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()),'seed',$date->get(IL_CAL_DATE));
			if($agenda_view_type = (int) $_GET['cal_agenda_per'])
			{
				$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "cal_agenda_per", $agenda_view_type);
			}
			$a_tpl->setVariable('OPEN_DAY_VIEW', $ilCtrl->getLinkTargetByClass($this->getTargetGUIClassPath(), ''));

			$a_tpl->setVariable('MONTH_DAY',$month_day);

			$a_tpl->parseCurrentBlock();


			$a_tpl->setCurrentBlock('month_col');

			include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
			if(ilCalendarUtil::_isToday($date))
			{
				$a_tpl->setVariable('TD_CLASS','calminitoday');
			}
			#elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_DAY))
			#{
			#	$a_tpl->setVariable('TD_CLASS','calmininow');
			#}
			elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_MONTH))
			{
				$a_tpl->setVariable('TD_CLASS','calministd');
			}
			elseif(ilDateTime::_before($date,$this->seed,IL_CAL_MONTH))
			{
				$a_tpl->setVariable('TD_CLASS','calminiprev');
			}
			else
			{
				$a_tpl->setVariable('TD_CLASS','calmininext');
			}
			
			$a_tpl->parseCurrentBlock();

			
			if($counter and !($counter % 7))
			{
				if ($this->getShowWeeksColumn())
				{
					$a_tpl->setCurrentBlock('week');
					$a_tpl->setVariable('WEEK',
						$date->get(IL_CAL_FKT_DATE, 'W'));
					$a_tpl->parseCurrentBlock();
				}

				$a_tpl->setCurrentBlock('month_row');
				//$a_tpl->setVariable('TD_CLASS','calminiweek');
				$a_tpl->parseCurrentBlock();

				$week_has_events = false;
			}
		}
		$a_tpl->setCurrentBlock('mini_month');
		$a_tpl->setVariable('TXT_MONTH_OVERVIEW', $lng->txt("cal_month_overview"));

		$first_of_month = substr($this->seed->get(IL_CAL_DATE), 0, 7)."-01";
		$myseed = new ilDate($first_of_month, IL_CAL_DATE);

		$myseed->increment(ilDateTime::MONTH, -1);
		$ilCtrl->setParameter($this,'seed',$myseed->get(IL_CAL_DATE));
		
		$prev_link = $ilCtrl->getLinkTarget($this, "setSeed", "", true);

		$myseed->increment(ilDateTime::MONTH, 2);
		$ilCtrl->setParameter($this,'seed',$myseed->get(IL_CAL_DATE));
		$next_link = $ilCtrl->getLinkTarget($this, "setSeed", "", true);

		$ilCtrl->setParameter($this, 'seed', "");

		$blockgui = $this;

		// view control
		// ... previous button
		$b1 = $ui->factory()->button()->standard($lng->txt("previous"), "#")->withOnLoadCode(function($id) use($prev_link, $blockgui) {
			return
				"$('#".$id."').click(function() { ilBlockJSHandler('block_".$blockgui->getBlockType().
				"_".$blockgui->getBlockId()."','".$prev_link."'); return false;});";
		});

		// ... month button
		$ilCtrl->clearParameterByClass("ilcalendarblockgui",'seed');
		$month_link = $ilCtrl->getLinkTarget($this, "setSeed", "", true, false);
		$seed_parts = explode("-", $this->seed->get(IL_CAL_DATE));
		$b2 = $ui->factory()->button()->month($seed_parts[1]."-".$seed_parts[0])->withOnLoadCode(function($id) use ($month_link, $blockgui) {
			return "$('#".$id."').on('il.ui.button.month.changed', function(el, id, month) { var m = month.split('-'); ilBlockJSHandler('block_".$blockgui->getBlockType().
				"_".$blockgui->getBlockId()."','".$month_link."' + '&seed=' + m[1] + '-' + m[0] + '-01'); return false;});";
		});
		// ... next button
		$b3 = $ui->factory()->button()->standard($lng->txt("next"), "#")->withOnLoadCode(function($id) use($next_link, $blockgui) {
			return
				"$('#".$id."').click(function() { ilBlockJSHandler('block_".$blockgui->getBlockType().
				"_".$blockgui->getBlockId()."','".$next_link."'); return false;});";
		});


		$vc = $ui->factory()->viewControl()->section($b1,$b2,$b3);
		$a_tpl->setVariable("VIEW_CTRL_SECTION", $ui->renderer()->render($vc));

		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Add subscription block command
	 */
	protected function addSubscriptionBlockCommand()
	{
		global $DIC;

		$lng = $DIC->language();

		$ui_factory = $DIC->ui()->factory();
		$ui_renderer = $DIC->ui()->renderer();

		$gui_path = $this->getTargetGUIClassPath();
		$gui_path[] = "ilcalendarsubscriptiongui";
		$url = $this->ctrl->getLinkTargetByClass($gui_path, 'getModalForSubscription', "", true, false);

		$roundtrip = $ui_factory->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

		$standard_button = $ui_factory->button()->standard($lng->txt('btn_ical'), '')->withOnClick(
			$roundtrip->getShowSignal()
		);
		$components = [
			$roundtrip,
			$standard_button
		];

		$out = $ui_renderer->render($components);
		$this->addBlockCommand(
			'',
			'',
			"", "",
			true,
			false,
			'<span class="ilIcalIcon">'.$out.'</span>'
		);
	}


	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		$this->initCategories();
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilAccess = $this->access;
		$ilObjDataCache = $this->obj_data_cache;
		$user = $this->user;

		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}

		$this->addSubscriptionBlockCommand();

		if($this->mode == ilCalendarCategories::MODE_REPOSITORY)
		{
			if(!isset($_GET["bkid"]))
			{
				/*
				if($ilAccess->checkAccess('edit_event','',(int) $_GET['ref_id']))
				{
					$ilCtrl->setParameter($this, "add_mode", "block");
					$this->addBlockCommand(
						$ilCtrl->getLinkTargetByClass("ilCalendarAppointmentGUI",
							"add"),
						$lng->txt("add_appointment"));
					$ilCtrl->setParameter($this, "add_mode", "");
				}*/

				include_once "Modules/Course/classes/class.ilCourseParticipants.php";
				$obj_id = $ilObjDataCache->lookupObjId((int) $_GET['ref_id']);
				$participants = ilCourseParticipants::_getInstanceByObjId($obj_id);
				$users = array_unique(array_merge($participants->getTutors(), $participants->getAdmins()));
				//$users = $participants->getParticipants();
				include_once 'Services/Booking/classes/class.ilBookingEntry.php';
				$users = ilBookingEntry::lookupBookableUsersForObject($obj_id,$users);
				foreach($users as $user_id)
				{
					if(!isset($_GET["bkid"]))
					{
						include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
						$now = new ilDateTime(time(), IL_CAL_UNIX);
						
						// default to last booking entry
						$appointments = ilConsultationHourAppointments::getAppointments($user_id);
						$next_app = end($appointments);
						reset($appointments);
						
						foreach($appointments as $entry)
						{
							// find next entry
							if(ilDateTime::_before($entry->getStart(), $now, IL_CAL_DAY))
							{
								continue;
							}
							include_once 'Services/Booking/classes/class.ilBookingEntry.php';
							$booking_entry = new ilBookingEntry($entry->getContextId());
							if(!in_array($obj_id, $booking_entry->getTargetObjIds()))
							{
								continue;
							}
							
							if(!$booking_entry->isAppointmentBookableForUser($entry->getEntryId(), $user->getId()))
							{
								continue;
							}
							$next_app = $entry;
							break;
						}

						/*
						$ilCtrl->setParameter($this, "bkid", $user_id);
						if($next_app)
						{
							$ilCtrl->setParameter(
								$this,
								'seed',
								(string) $next_app->getStart()->get(IL_CAL_DATE)
							);
						}*/

						//$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "bkid", $user_id);

						$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "ch_user_id", $user_id);

						if($next_app)
						{
							// this does not work correctly
							/*$ilCtrl->setParameterByClass(
								end($this->getTargetGUIClassPath()),
								'seed',
								(string) $next_app->getStart()->get(IL_CAL_DATE)
							);*/
						}

						if (!$this->getForceMonthView())
						{
							$this->cal_footer[] = array(
								'link' => $ilCtrl->getLinkTargetByClass($this->getTargetGUIClassPath(), 'selectCHCalendarOfUser'),
								'txt' => str_replace("%1", ilObjUser::_lookupFullname($user_id), $lng->txt("cal_consultation_hours_for_user"))
							);
						}
						$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "ch_user_id", "");
						$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "bkid", $_GET["bkid"]);
						$ilCtrl->setParameterByClass(end($this->getTargetGUIClassPath()), "seed", $_GET["seed"]);
					}
				}
				$ilCtrl->setParameter($this, "bkid", "");
				$ilCtrl->setParameter($this, 'seed', '');
			}
			else
			{
				$ilCtrl->setParameter($this, "bkid", "");
				$this->addBlockCommand(
							$ilCtrl->getLinkTarget($this),
							$lng->txt("back"));
				$ilCtrl->setParameter($this, "bkid", (int)$_GET["bkid"]);
			}
		}

		if ($this->getProperty("settings") == true)
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "editSettings"),
				$lng->txt("settings"));
		}

		$ilCtrl->setParameterByClass($this->getParentGUI(), "seed", isset($_GET["seed"]) ? $_GET["seed"] : "");
		$ret = parent::getHTML();
		$ilCtrl->setParameterByClass($this->getParentGUI(), "seed", "");

		// workaround to include asynch code from ui only one time, see #20853
		if ($ilCtrl->isAsynch())
		{
			global $DIC;
			$f = $DIC->ui()->factory()->legacy("");
			$ret.= $DIC->ui()->renderer()->renderAsync($f);
		}

		return $ret;
	}
	
	/**
	* Get overview.
	*/
	function getOverview()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;


		include_once('./Services/Calendar/classes/class.ilCalendarSchedule.php');
		$schedule = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_INBOX);	
		$events = $schedule->getChangedEvents(true);

		$ilCtrl->setParameterByClass('ilcalendarinboxgui', 'changed', 1);
		$link = '<a href='.$ilCtrl->getLinkTargetByClass('ilcalendarinboxgui','').'>';
		$ilCtrl->setParameterByClass('ilcalendarinboxgui', 'changed', '');
		$text = '<div class="small">'.((int) count($events))." ".$lng->txt("cal_changed_events_header")."</div>";
		$end_link = '</a>';
		
		return $link.$text.$end_link;
	}

	function addCloseCommand($a_content_block)
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		$a_content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);
	}
	
	/**
	 * init categories
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initCategories()
	{
		$this->mode = ilCalendarCategories::MODE_REPOSITORY;

		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');

		//if(!isset($_GET['bkid']))
		//{
		if ($this->getForceMonthView())	// in full container calendar presentation (allows selection of other calendars)
		{
			//ilCalendarCategories::_getInstance()->initialize(ilCalendarCategories::MODE_REPOSITORY, (int)$_GET['ref_id'], true);
		}
		else							// side block in container content view -> focus on container events only
		{
			ilCalendarCategories::_getInstance()->initialize(ilCalendarCategories::MODE_REPOSITORY_CONTAINER_ONLY, (int)$_GET['ref_id'], true);
		}
		//}
		//else
		//{
		//	// display consultation hours only (in course/group)
		//	ilCalendarCategories::_getInstance()->setCHUserId((int) $_GET['bkid']);
		//	ilCalendarCategories::_getInstance()->initialize(ilCalendarCategories::MODE_CONSULTATION,(int) $_GET['ref_id'],true);
		//}
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	protected function setSubTabs()
	{
		$ilTabs = $this->tabs;

		$ilTabs->clearSubTabs();
	}

	/**
	* Set seed
	*/
	function setSeed()
	{
		$ilCtrl = $this->ctrl;

		//$ilUser->writePref("il_pd_bkm_mode", 'flat');
		$_SESSION["il_cal_block_".$this->getBlockType()."_".$this->getBlockId()."_seed"] =
			$_GET["seed"];
		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$this->returnToUpperContext();
		}
	}
	
	/**
	* Return to upper context
	*/
	function returnToUpperContext()
	{
		$ilCtrl = $this->ctrl;

		$ilCtrl->returnToParent($this);
	}


	/**
	 * @deprecated ilCalendarSubscriptionGUI should be used
	 * @return string
	 */
	public function showCalendarSubscription()
	{
		return;
		$lng = $this->lng;
		$ilUser = $this->user;

		$tpl = new ilTemplate('tpl.show_calendar_subscription.html',true,true,'Services/Calendar');
		
		$tpl->setVariable('TXT_TITLE',$lng->txt('cal_subscription_header'));
		$tpl->setVariable('TXT_INFO',$lng->txt('cal_subscription_info'));
		$tpl->setVariable('TXT_CAL_URL',$lng->txt('cal_subscription_url'));
		
		include_once './Services/Calendar/classes/class.ilCalendarAuthenticationToken.php';
		
		switch($this->mode)
		{
			case ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS:
			case ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP:
				$selection = ilCalendarAuthenticationToken::SELECTION_PD;
				$calendar = 0;
				break;
			
			default:
				$selection = ilCalendarAuthenticationToken::SELECTION_CATEGORY;
				// TODO: calendar id
				$calendar = ilObject::_lookupObjId((int) $_GET['ref_id']);	
				break;		 
		}
		if($hash = ilCalendarAuthenticationToken::lookupAuthToken($ilUser->getId(), $selection, $calendar))
		{
			
		}
		else
		{
			$token = new ilCalendarAuthenticationToken($ilUser->getId());
			$token->setSelectionType($selection);
			$token->setCalendar($calendar);
			$hash = $token->add();
		}
		$url = ILIAS_HTTP_PATH.'/calendar.php?client_id='.CLIENT_ID.'&token='.$hash;
		
		$tpl->setVariable('VAL_CAL_URL',$url);
		$tpl->setVariable('VAL_CAL_URL_TXT',$url);
		
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setContent($tpl->get());
		$content_block->setTitle($lng->txt("calendar"));
		$content_block->addHeaderCommand($this->ctrl->getParentReturn($this),
			$lng->txt("selected_items_back"));

		return $content_block->getHTML();
		
	}
	
	function fillFooter()
	{
		// begin-patch ch
		foreach((array) $this->cal_footer as $link_info)
		{
			$this->tpl->setCurrentBlock('data_section');
			$this->tpl->setVariable('DATA',
					sprintf('<a href="%s">%s</a>',$link_info['link'],$link_info['txt'])

			);
			$this->tpl->parseCurrentBlock();
		}
		// end-patch ch

		$this->setFooterLinks();
		$this->fillFooterLinks();
		$this->tpl->setVariable("FCOLSPAN", $this->getColSpan());
		if ($this->tpl->blockExists("block_footer"))
		{
			$this->tpl->setCurrentBlock("block_footer");
			$this->tpl->parseCurrentBlock();
		}
		
	}
	
	function setFooterLinks()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;


		// alex: changed from < 2 to < 1 - original detail level 1 did not work anymore
		if ($this->getCurrentDetailLevel() < 1)
		{
			return;
		}

		if (!$this->getForceMonthView())
		{
			$this->addFooterLink($lng->txt("cal_upcoming_events_header"),
				$ilCtrl->getLinkTarget($this, "setPdModeEvents"),
				$ilCtrl->getLinkTarget($this, "setPdModeEvents", "", true),
				"block_" . $this->getBlockType() . "_" . $this->block_id,
				false, false, ($this->display_mode != 'mmon'));

			$this->addFooterLink($lng->txt("app_month"),
				$ilCtrl->getLinkTarget($this, "setPdModeMonth"),
				$ilCtrl->getLinkTarget($this, "setPdModeMonth", "", true),
				"block_" . $this->getBlockType() . "_" . $this->block_id,
				false, false, ($this->display_mode == 'mmon'));

			if ($this->getRepositoryMode())
			{
				#23921
				$ilCtrl->setParameterByClass('ilcalendarpresentationgui','seed', '');
				$this->addFooterLink($lng->txt("cal_open_calendar"),
					$ilCtrl->getLinkTargetByClass($this->getTargetGUIClassPath(), ""),
					"",
					"block_" . $this->getBlockType() . "_" . $this->block_id,
					false, false);

				$ilCtrl->setParameter($this, "add_mode", "");
				$this->addFooterLink($lng->txt("add_appointment"),
					$ilCtrl->getLinkTargetByClass("ilCalendarAppointmentGUI", "add"),
					"",
					"block_" . $this->getBlockType() . "_" . $this->block_id,
					false, false);
				$ilCtrl->setParameter($this, "add_mode", "");
			}
		}
	}
	
	function setPdModeEvents()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;


		$ilUser->writePref("il_pd_cal_mode", "evt");
		$this->display_mode = "evt";
		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
		}
	}
	
	function setPdModeMonth()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		$ilUser->writePref("il_pd_cal_mode", "mmon");
		$this->display_mode = "mmon";
		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
		}
	}

	/**
	 * Get events
	 *
	 * @param
	 * @return
	 */
	function getEvents()
	{
		$seed = new ilDate(date('Y-m-d',time()),IL_CAL_DATE);

		include_once('./Services/Calendar/classes/class.ilCalendarSchedule.php');
		$schedule = new ilCalendarSchedule($seed, ilCalendarSchedule::TYPE_PD_UPCOMING);
		$schedule->addSubitemCalendars(true); // #12007
		$schedule->setEventsLimit(20);
		$schedule->calculate();
		$ev = $schedule->getScheduledEvents(); // #13809
		return ($ev);
	}

	function getData()
	{
		$lng = $this->lng;
		$ui = $this->ui;

		$f = $ui->factory();
							
		$events = $this->getEvents();
		
		$data = array();
		if(sizeof($events))
		{
			foreach($events as $item)
			{
				$this->ctrl->setParameter($this, "app_id", $item["event"]->getEntryId());
				$this->ctrl->setParameter($this,'dt',$item['dstart']);
				$url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
				$this->ctrl->setParameter($this, "app_id", $_GET["app_id"]);
				$this->ctrl->setParameter($this, "dt", $_GET["dt"]);
				$modal = $f->modal()->roundtrip('', [])->withAsyncRenderUrl($url);

				$dates = $this->getDatesForItem($item);

				$comps = [$f->button()->shy($item["event"]->getPresentationTitle(), "")->withOnClick($modal->getShowSignal()), $modal];
				$renderer = $ui->renderer();
				$shy = $renderer->render($comps);

				$data[] = array(
					"date" =>  ilDatePresentation::formatPeriod($dates["start"], $dates["end"]),
					"title" => $item["event"]->getPresentationTitle(),			
					"url" => "#",
					"shy_button" => $shy
					);
			}
			$this->setEnableNumInfo(true);
		}
		else
		{
			$data[] = array(	
					"date" => $lng->txt("msg_no_search_result"),
					"title" => "",			
					"url" => ""
					);		
			
			$this->setEnableNumInfo(false);
		}
		
		return $data;
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
	 * Get modal for appointment (see similar code in ilCalendarAgendaListGUI)
	 * todo use all this methods from ilcalendarviewgui.php
	 */
	function getModalForApp()
	{
		$this->initCategories();
		$ui = $this->ui;
		$ilCtrl = $this->ctrl;

		$f = $ui->factory();
		$r = $ui->renderer();

		// @todo: this needs optimization
		$events = $this->getEvents();
		foreach ($events as $item)
		{
			if ($item["event"]->getEntryId() == (int) $_GET["app_id"] && $item['dstart'] == (int) $_GET['dt'])
			{
				$dates = $this->getDatesForItem($item);

				// content of modal
				include_once("./Services/Calendar/classes/class.ilCalendarAppointmentPresentationGUI.php");
				$next_gui = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $item);
				$content = $ilCtrl->getHTML($next_gui);

				$modal = $f->modal()->roundtrip(ilDatePresentation::formatPeriod($dates["start"], $dates["end"]),$f->legacy($content));
				echo $r->renderAsync($modal);
			}
		}
		exit();
	}
}

?>
