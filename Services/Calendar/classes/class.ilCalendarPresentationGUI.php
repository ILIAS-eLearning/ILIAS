<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Calendar/classes/class.ilCalendarSettings.php';

/**
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * 
 * @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarMonthGUI, ilCalendarUserSettingsGUI, ilCalendarCategoryGUI, ilCalendarWeekGUI
 * @ilCtrl_Calls ilCalendarPresentationGUI: ilCalendarAppointmentGUI, ilCalendarDayGUI, ilCalendarInboxGUI, ilCalendarSubscriptionGUI
 * @ilCtrl_Calls ilCalendarPresentationGUI: ilConsultationHoursGUI, ilCalendarBlockGUI, ilPDCalendarBlockGUI, ilPublicUserProfileGUI
 * @ingroup ServicesCalendar
 */
class ilCalendarPresentationGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var mixed
	 */
	protected $tpl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilHelp
	 */
	protected $help;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	protected $repository_mode = false;

	/**
	 * @var string seed string yyyy-mm-dd
	 */
	protected $seed;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_ref_id = 0)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->lng->loadLanguageModule('dateplaner');
		
		$this->tpl = $DIC["tpl"];
		$this->tabs_gui = $DIC->tabs();
		$this->user = $DIC->user();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->help = $DIC["ilHelp"];
		$this->ui = $DIC->ui();
		$this->toolbar = $DIC->toolbar();
		$this->ref_id = $a_ref_id;
		$this->category_id = $_GET["category_id"];
		$this->ctrl->saveParameter($this, "category_id");


		// show back to pd
		$this->ctrl->saveParameter($this, 'backpd');
		
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($this->user->getId());
		
		include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
		if ($a_ref_id > 0)
		{
			$this->repository_mode = true;
		}
		if ($this->category_id > 0)		// single calendar view
		{
			// ensure activation of this category
			include_once("./Services/Calendar/classes/class.ilCalendarVisibility.php");
			$vis = ilCalendarVisibility::_getInstanceByUserId($this->user->getId(), $a_ref_id);
			$vis->forceVisibility($this->category_id);

			$cats->initialize(ilCalendarCategories::MODE_SINGLE_CALENDAR, 0, false, $this->category_id);
		}
		else
		{
			if ($a_ref_id > 0)
			{
				$cats->initialize(ilCalendarCategories::MODE_REPOSITORY, (int) $a_ref_id, true);
			}
			else
			{
				if(ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
				{
					$cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP);
				}
				else
				{
					$cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS);
				}
			}
		}

		include_once("./Services/Calendar/classes/class.ilCalendarActions.php");
		$this->actions = ilCalendarActions::getInstance();

		$this->cats = $cats;
	}

	/**
	 * Get RepositoryMode
	 *
	 * @return bool repository mode
	 */
	function getRepositoryMode()
	{
		return $this->repository_mode;
	}
	
	/**
	 * Init and redirect to consultation hours
	 */
	protected function initAndRedirectToConsultationHours()
	{
		$visibility = ilCalendarVisibility::_getInstanceByUserId($this->user->getId(), $this->ref_id);
		foreach ($this->cats->getCategoriesInfo() as $info)
		{
			if (
				$info["type"] == ilCalendarCategory::TYPE_CH && 
				$info["obj_id"] == $_GET["ch_user_id"]
			)
			{
				$v = $visibility->getVisible();
				if(!in_array($info["cat_id"], $v))
				{
					$v[] = $info["cat_id"];
				}
				$visibility->showSelected($v);
				$visibility->save();
				$this->ctrl->redirect($this, "");
			}
		}
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		$ilUser = $this->user;

		$cmd = $this->ctrl->getCmd();
		// now next class is not empty, which breaks old consultation hour implementation
		$next_class = $this->getNextClass();

		include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
		if(!ilCalendarSettings::_getInstance()->isEnabled())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'),true);
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		$this->initSeed();
		$this->prepareOutput();
		
		switch($cmd)
		{
			case 'selectCHCalendarOfUser':
				$this->initAndRedirectToConsultationHours();
				break;
		}

		switch($next_class)
		{
			case 'ilcalendarinboxgui':
				$this->tabs_gui->activateTab('cal_agenda');
				$inbox_gui = $this->forwardToClass('ilcalendarinboxgui');
				if($this->showToolbarAndSidebar()){
					$this->showViewSelection("cal_list");
					$this->showSideBlocks();
					$inbox_gui->addToolbarActions();
				}

				break;
				
			case 'ilconsultationhoursgui':
				$this->tabs_gui->activateTab('app_consultation_hours');
				$this->tabs_gui->clearTargets();

				// No side blocks
				$this->tabs_gui->setBackTarget(
					$this->lng->txt('cal_back_to_cal'),
					$this->ctrl->getLinkTargetByClass($this->readLastClass())
				);

				include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHoursGUI.php';
				$gui = new ilConsultationHoursGUI();
				$this->ctrl->forwardCommand($gui);
				if($this->showToolbarAndSidebar())
				{
					$this->showSideBlocks();
				}
				return true;
			
			case 'ilcalendarmonthgui':
				$this->tabs_gui->activateTab('cal_agenda');
				$month_gui = $this->forwardToClass('ilcalendarmonthgui');

				if($this->showToolbarAndSidebar()) {
					$this->showViewSelection("app_month");
					$this->showSideBlocks();
					$month_gui->addToolbarActions();
				}
				break;
				
			case 'ilcalendarweekgui':
				$this->tabs_gui->activateTab('cal_agenda');
				$week_gui = $this->forwardToClass('ilcalendarweekgui');
				if($this->showToolbarAndSidebar()) {
					$this->showViewSelection("app_week");
					$this->showSideBlocks();
					$week_gui->addToolbarActions();
				}

				break;

			case 'ilcalendardaygui':
				$this->tabs_gui->activateTab('cal_agenda');
				$day_gui = $this->forwardToClass('ilcalendardaygui');
				if($this->showToolbarAndSidebar())
				{
					$this->showViewSelection("app_day");
					$this->showSideBlocks();
					$day_gui->addToolbarActions();
				}
				break;

			case 'ilcalendarusersettingsgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->activateTab('settings');
				$this->setCmdClass('ilcalendarusersettingsgui');
				
				include_once('./Services/Calendar/classes/class.ilCalendarUserSettingsGUI.php');
				$user_settings = new ilCalendarUserSettingsGUI();
				$this->ctrl->forwardCommand($user_settings);
				// No side blocks
				return true;
				
			case 'ilcalendarappointmentgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->activateTab($_SESSION['cal_last_tab']);
				
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed, $this->seed,(int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;

			case 'ilcalendarsubscriptiongui':
				$this->ctrl->setReturn($this,'');
				//$this->tabs_gui->activateTab($_SESSION['cal_last_tab']);
				$this->tabs_gui->activateTab("cal_agenda");
				include_once './Services/Calendar/classes/class.ilCalendarSubscriptionGUI.php';
				$sub = new ilCalendarSubscriptionGUI((int) $_REQUEST['category_id'], (int) $_GET["ref_id"]);
				$this->ctrl->forwardCommand($sub);
				if($this->showToolbarAndSidebar()) {
					$this->showSideBlocks();
				}
				break;
				
			case 'ilcalendarcategorygui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->activateTab("cal_manage");
				include_once('Services/Calendar/classes/class.ilCalendarCategoryGUI.php');				
				$category = new ilCalendarCategoryGUI($ilUser->getId(),$this->seed, $this->ref_id);
				if($this->ctrl->forwardCommand($category))
				{
					//$this->tabs_gui->activateTab("cal_manage");

					// no side blocks
					return;
				}
				else
				{
					if ($this->getRepositoryMode())
					{
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, ""));
					}
					//$this->tabs_gui->activateTab($_SESSION['cal_last_tab']);
					$this->showSideBlocks();
					break;
				}

			case 'ilcalendarblockgui':
				$side_cal = new ilCalendarBlockGUI();
				$side_cal->setAvailableDetailLevels(2,2);
				$side_cal->setRepositoryMode($this->getRepositoryMode());
				$side_cal->setForceMonthView(true);
				$this->ctrl->forwardCommand($side_cal);
				$this->showSideBlocks();
				break;

			case 'ilpdcalendarblockgui':
				$side_cal = new ilPDCalendarBlockGUI();
				$side_cal->setAvailableDetailLevels(2,2);
				$side_cal->setRepositoryMode($this->getRepositoryMode());
				$side_cal->setForceMonthView(true);
				$this->ctrl->forwardCommand($side_cal);
				$this->showSideBlocks();
				break;

			case 'ilpublicuserprofilegui':
				include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
				$user_profile = new ilPublicUserProfileGUI($_GET["user_id"]);
				$html = $this->ctrl->forwardCommand($user_profile);
				$this->tpl->setContent($html);
				break;

			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				$this->showSideBlocks();
				break;
		}


		$this->synchroniseExternalCalendars();
		
		return true;
	}

	/**
	 * Show view selection
	 *
	 * @param string $a_active
	 */
	function showViewSelection($a_active = "cal_list")
	{
		$ui = $this->ui;
		$ctrl = $this->ctrl;
		$lng = $this->lng;
		$toolbar = $this->toolbar;

		$f = $ui->factory();

		$actions = array (
			$lng->txt("app_day") => $ctrl->getLinkTargetByClass('ilCalendarDayGUI',''),
			$lng->txt("app_week") => $ctrl->getLinkTargetByClass('ilCalendarWeekGUI',''),
			$lng->txt("app_month") => $ctrl->getLinkTargetByClass('ilCalendarMonthGUI',''),
			$lng->txt("cal_list") => $ctrl->getLinkTargetByClass('ilCalendarInboxGUI','')
		);

		$aria_label = $lng->txt("cal_change_calendar_view");
		$view_control = $f->viewControl()->mode($actions, $aria_label)->withActive($lng->txt($a_active));

		$toolbar->addComponent($view_control);

		$ctrl->setParameterByClass("ilcalendarappointmentgui", "seed", $this->seed->get(IL_CAL_DATE, ''));
		$ctrl->setParameterByClass("ilcalendarappointmentgui", "app_id", "");
		$ctrl->setParameterByClass("ilcalendarappointmentgui", "dt", "");

		$extra_button_added = false;
		// add appointment
		if ($this->category_id == 0 || $this->actions->checkAddEvent($this->category_id))
		{

			$toolbar->addSeparator();
			$extra_button_added = true;
			$add_button = $f->button()->standard($lng->txt("cal_add_appointment"),
				$ctrl->getLinkTargetByClass("ilcalendarappointmentgui", "add"));
			$toolbar->addComponent($add_button);
		}

		// import appointments
		if ($this->category_id > 0 && $this->actions->checkAddEvent($this->category_id))
		{
			if(!$extra_button_added) {
				$toolbar->addSeparator();
			}
			$add_button = $f->button()->standard($lng->txt("cal_import_appointments"),
				$ctrl->getLinkTargetByClass("ilcalendarcategorygui", "importAppointments"));
			$toolbar->addComponent($add_button);
		}
	}


	/**
	 * get next class
	 *
	 * @access public
	 */
	public function getNextClass()
	{
		if(strlen($next_class = $this->ctrl->getNextClass()))
		{
			return $next_class;
		}
		if($this->ctrl->getCmdClass() == strtolower(get_class($this)) or $this->ctrl->getCmdClass() == '')
		{
			return $this->readLastClass();
		}
	}
	
	/**
	 * Read last class from history
	 * @return 
	 */
	public function readLastClass()
	{
		$ilUser = $this->user;
		
		return $ilUser->getPref('cal_last_class') ? $ilUser->getPref('cal_last_class') : 'ilcalendarinboxgui';
				
	}
	
	public function setCmdClass($a_class)
	{
		// If cmd class == 'ilcalendarpresentationgui' the cmd class is set to the the new forwarded class
		// otherwise e.g ilcalendarmonthgui tries to forward (back) to ilcalendargui.

		if($this->ctrl->getCmdClass() == strtolower(get_class($this)))
		{
			$this->ctrl->setCmdClass(strtolower($a_class));
		}
		return true;
	}
	
	/**
	 * forward to class
	 *
	 * @access protected
	 */
	protected function forwardToClass($a_class)
	{
		$ilUser = $this->user;

		switch($a_class)
		{
			case 'ilcalendarmonthgui':
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'app_month'; 
				$this->setCmdClass('ilcalendarmonthgui');
				include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
				$month_gui = new ilCalendarMonthGUI($this->seed);
				$this->ctrl->forwardCommand($month_gui);
				return $month_gui;
				break;
				
			case 'ilcalendarweekgui':
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'app_week'; 
				$this->setCmdClass('ilcalendarweekgui');
				include_once('./Services/Calendar/classes/class.ilCalendarWeekGUI.php');
				$week_gui = new ilCalendarWeekGUI($this->seed);
				$this->ctrl->forwardCommand($week_gui);
				return $week_gui;
				break;

			case 'ilcalendardaygui':
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'app_day'; 
				$this->setCmdClass('ilcalendardaygui');
				include_once('./Services/Calendar/classes/class.ilCalendarDayGUI.php');
				$day_gui = new ilCalendarDayGUI($this->seed);
				$this->ctrl->forwardCommand($day_gui);
				return $day_gui;
				break;
				
			case 'ilcalendarinboxgui':
				#21479
				if($view_option = $_GET['cal_agenda_per']) {
					ilSession::set("cal_list_view",$view_option);
				} elseif ($view_option = ilSession::get('cal_list_view')) {
					ilSession::set("cal_list_view",$view_option);
				}
				$ilUser->writePref('cal_last_class',$a_class);
				$_SESSION['cal_last_tab'] = 'cal_upcoming_events_header';
				$this->setCmdClass('ilcalendarinboxgui');
				include_once('./Services/Calendar/classes/class.ilCalendarInboxGUI.php');
				$inbox_gui = new ilCalendarinboxGUI($this->seed);
				$this->ctrl->forwardCommand($inbox_gui);
				return $inbox_gui;
				break;
		}
	}
	
	/**
	 * forward to last presentation class
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function loadHistory()
	{
		$ilUser = $this->user;
		
		$this->ctrl->setCmd('');
		$history = $ilUser->getPref('cal_last_class') ? $ilUser->getPref('cal_last_class') : 'ilcalendarmonthgui';
		$this->forwardToClass($history);
	}
	
	/**
	 * show side blocks
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function showSideBlocks()
	{
		$ilUser = $this->user;
		$ilCtrl = $this->ctrl;

		$tpl =  new ilTemplate('tpl.cal_side_block.html',true,true,'Services/Calendar');

		/*include_once('./Services/Calendar/classes/class.ilMiniCalendarGUI.php');
		$mini = new ilMiniCalendarGUI($this->seed, $this);
		$tpl->setVariable('MINICAL',$mini->getHTML());*/

		/*
		include_once("./Services/Calendar/classes/class.ilCalendarSelectionBlockGUI.php");
		$block_gui = new ilCalendarSelectionBlockGUI($this->seed);
		$html = $ilCtrl->getHTML($block_gui);
		return $html;*/

		if ($this->getRepositoryMode())
		{
			include_once("./Services/Calendar/classes/class.ilCalendarBlockGUI.php");
			$side_cal = new ilCalendarBlockGUI();

		}
		else
		{
			include_once("./Services/Calendar/classes/class.ilPDCalendarBlockGUI.php");
			$side_cal = new ilPDCalendarBlockGUI();
		}
		$side_cal->setAvailableDetailLevels(2,2);
		$side_cal->setParentGUI("ilCalendarPresentationGUI");
		$side_cal->setForceMonthView(true);
		$side_cal->setRepositoryMode($this->getRepositoryMode());
		$tpl->setVariable('MINICAL', $ilCtrl->getHTML($side_cal));

		if ($this->category_id == 0)
		{
			include_once('./Services/Calendar/classes/class.ilCalendarCategoryGUI.php');
			$cat = new ilCalendarCategoryGUI($ilUser->getId(), $this->seed, $this->ref_id);
			$tpl->setVariable('CATEGORIES', $ilCtrl->getHTML($cat));
		}

		$this->tpl->setRightContent($tpl->get());
	}
	
	
	/**
	 * Show
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function show()
	{
		$this->tpl->addCss(ilUtil::getStyleSheetLocation('filesystem','delos.css','Services/Calendar'));
	}
	
	
	/**
	 * get tabs
	 *
	 * @access public
	 */
	protected function prepareOutput()
	{
		global $DIC;

		$tpl = $DIC["tpl"];
		//$tpl->setHeaderActionMenu(null);

		$rbacsystem = $this->rbacsystem;
		$ilHelp = $this->help;

		$ilHelp->setScreenIdComponent("cal");

		if ($this->getRepositoryMode())
		{
			$this->tabs_gui->clearTargets();

			$this->lng->loadLanguageModule('dateplaner');
			if($_REQUEST['backpd'])
			{
				$this->tabs_gui->setBackTarget(
					$this->lng->txt('back_to_pd'),
					$this->ctrl->getLinkTargetByClass(ilPersonalDesktopGUI::class, 'jumpToCalendar')
				);
				switch(ilObject::_lookupType($this->ref_id, true))
				{
					case 'crs':
					case 'grp':
						$label = $this->lng->txt('back_to_'. ilObject::_lookupType($this->ref_id,true));
						break;
					default:
						$label = $this->lng->txt('back');
				}
				$this->tabs_gui->setBack2Target($label, $this->ctrl->getParentReturn($this));
			}
			else
			{
				$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getParentReturn($this));
			}
		}
		else
		{
			$this->tabs_gui->addTab('cal_agenda',
				$this->lng->txt("cal_agenda"),
				$this->ctrl->getLinkTarget($this, ''));
			//$this->tabs_gui->addTarget('cal_upcoming_events_header',$this->ctrl->getLinkTargetByClass('ilCalendarInboxGUI',''));

			if (
				$rbacsystem->checkAccess('add_consultation_hours', ilCalendarSettings::_getInstance()->getCalendarSettingsId()) and
				ilCalendarSettings::_getInstance()->areConsultationHoursEnabled()
			)
			{
				$this->tabs_gui->addTarget('app_consultation_hours', $this->ctrl->getLinkTargetByClass('ilConsultationHoursGUI', ''));
			}
			$this->tabs_gui->addTarget('cal_manage', $this->ctrl->getLinkTargetByClass('ilCalendarCategoryGUI', 'manage'));
			$this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass('ilCalendarUserSettingsGUI', ''));
		}

		// if we are in single calendar view
		if ($this->category_id > 0)
		{
			global $DIC;

			$tabs = $this->tabs_gui;
			$lng = $this->lng;
			$ctrl = $this->ctrl;
			$tpl = $DIC["tpl"];

			$category = new ilCalendarCategory($this->category_id);

			// Set header
			$header = "";
			switch($category->getType())
			{
				case ilCalendarCategory::TYPE_USR:
					$header = $this->lng->txt('cal_type_personal').": ".$category->getTitle();
					break;

				case ilCalendarCategory::TYPE_GLOBAL:
					$header = $this->lng->txt('cal_type_system').": ".$category->getTitle();
					break;

				case ilCalendarCategory::TYPE_OBJ:
					$header = $this->lng->txt('cal_type_'.$category->getObjType()).": ".$category->getTitle();
					break;

				case ilCalendarCategory::TYPE_CH:
					$header = str_replace("%1", ilObjUser::_lookupFullname($category->getObjId()), $lng->txt("cal_consultation_hours_for_user"));
					break;

				case ilCalendarCategory::TYPE_BOOK:
					$header = $category->getTitle();
					break;
			}
			$tpl->setTitleIcon(ilUtil::getImagePath("icon_cal.svg"));
			$tpl->setTitle($header);

			$this->action_menu = new ilAdvancedSelectionListGUI();
			$this->action_menu->setAsynch(false);
			$this->action_menu->setAsynchUrl('');
			$this->action_menu->setListTitle($lng->txt('actions'));
			$this->action_menu->setId('act_cal');
			$this->action_menu->setSelectionHeaderClass('small');
			$this->action_menu->setItemLinkClass('xsmall');
			$this->action_menu->setLinksMode('il_ContainerItemCommand2');
			$this->action_menu->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
			$this->action_menu->setUseImages(false);

			// iCal-Url
			$ctrl->setParameterByClass("ilcalendarsubscriptiongui", "category_id", $this->category_id);
			$this->action_menu->addItem($lng->txt("cal_ical_url"), "", $ctrl->getLinkTargetByClass("ilcalendarsubscriptiongui", ""));

			// delete action
			if ($this->actions->checkDeleteCal($this->category_id))
			{
				$ctrl->setParameterByClass("ilcalendarcategorygui", "category_id", $this->category_id);
				$ctrl->setParameterByClass("ilcalendarcategorygui", "backv", "1");
				$this->action_menu->addItem($lng->txt("cal_delete_cal"), "", $ctrl->getLinkTargetByClass("ilcalendarcategorygui", "confirmDelete"));
			}

			$tpl->setHeaderActionMenu($this->action_menu->getHTML());


			$tabs->clearTargets();
			$ctrl->setParameterByClass("ilcalendarcategorygui", "category_id", "");
			$ctrl->setParameterByClass("ilcalendarpresentationgui", "category_id", "");

			if ($this->ref_id > 0)
			{
				$tabs->setBackTarget($lng->txt("back"), $ctrl->getLinkTargetByClass("ilcalendarpresentationgui", ""));
			}
			else
			{
				$tabs->setBackTarget($lng->txt("back"), $ctrl->getLinkTargetByClass("ilcalendarcategorygui", "manage"));
			}
			$ctrl->setParameterByClass("ilcalendarcategorygui", "category_id", $_GET["category_id"]);
			$ctrl->setParameterByClass("ilcalendarpresentationgui", "category_id", $_GET["category_id"]);

			// agenda
			$tabs->addTab("cal_agenda", $lng->txt("cal_agenda"), $ctrl->getLinkTargetByClass("ilcalendarpresentationgui", ""));

			// share
			if ($this->actions->checkShareCal($this->category_id))
			{
				$tabs->addTab("share", $lng->txt("cal_share"), $ctrl->getLinkTargetByClass("ilcalendarcategorygui", "shareSearch"));
			}

			// edit settings
			if ($this->actions->checkSettingsCal($this->category_id))
			{
				$tabs->addTab("edit", $lng->txt("settings"), $ctrl->getLinkTargetByClass("ilcalendarcategorygui", "edit"));
			}
			$tabs->activateTab("cal_agenda");
		}

	}

	/**
	 * init the seed date for presentations (month view, minicalendar)
	 *
	 * @access public
	 */
	public function initSeed()
	{
		include_once('Services/Calendar/classes/class.ilDate.php');
		$this->seed = $_REQUEST['seed'] ? new ilDate($_REQUEST['seed'],IL_CAL_DATE) : new ilDate(date('Y-m-d',time()),IL_CAL_DATE);
		$_GET['seed'] = $this->seed->get(IL_CAL_DATE,'');
		$this->ctrl->saveParameter($this,array('seed'));
 	}
	
	/**
	 * Sync external calendars
	 */
	protected function synchroniseExternalCalendars()
	{
		$ilUser = $this->user;

		if(!ilCalendarSettings::_getInstance()->isWebCalSyncEnabled())
		{
			return false;
		}
		// @todo make this thread safe
		
		$limit = new ilDateTime(time(),IL_CAL_UNIX);
		$limit->increment(IL_CAL_HOUR, -1 * ilCalendarSettings::_getInstance()->getWebCalSyncHours());
		
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		foreach($cats->getCategoriesInfo() as $cat_id => $info)
		{
			if($info['remote'])
			{
				// Check for execution
				$category = new ilCalendarCategory($cat_id);
				
				if(ilDateTime::_before($category->getRemoteSyncLastExecution(), $limit))
				{
					// update in any case to avoid multiple updates of invalid calendar sources.
					$category->setRemoteSyncLastExecution(new ilDateTime(time(),IL_CAL_UNIX));
					$category->update();

					include_once './Services/Calendar/classes/class.ilCalendarRemoteReader.php';
					$remote = new ilCalendarRemoteReader($category->getRemoteUrl());
					$remote->setUser($category->getRemoteUser());
					$remote->setPass($category->getRemotePass());
					$remote->read();
					$remote->import($category);
					break;
				}
			}
		}
	}

	#21613
	function showToolbarAndSidebar()
	{
		#21783
		if($this->ctrl->getCmdClass() == "ilcalendarappointmentgui" || $this->ctrl->getCmdClass() == 'ilconsultationhoursgui')
		{
			return false;
		}
		return true;
	}
	
}
?>