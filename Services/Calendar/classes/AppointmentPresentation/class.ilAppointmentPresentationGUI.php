<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/class.ilCalendarViewGUI.php';

/**
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationGUI  implements ilCalendarAppointmentPresentation
{
	protected static $instance; // [ilCalendarAppointmentPresentationFactory]

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var
	 */

	protected $appointment;

	/**
	 * @var ilInfoScreenGUI
	 */
	protected $infoscreen;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var \ILIAS\UI\Component\Item\Standard
	 */
	protected $list_item = null;

	/**
	 * @var array
	 */
	protected $info_items = array();

	/**
	 * @var array
	 */
	protected $list_properties = array();

	/**
	 * @var array
	 */
	protected $actions = array();

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var array readable ref ids for an object id
	 */
	protected $readable_ref_ids;

	/**
	 * @var bool if the appointment contains files.
	 */
	protected $has_files = false;

	/**
	 * 
	 *
	 * @param
	 */
	function __construct($a_appointment, $a_info_screen, $a_toolbar, $a_list_item)
	{
		global $DIC;
		$this->appointment = $a_appointment;
		$this->infoscreen = $a_info_screen;
		$this->toolbar = $a_toolbar;
		$this->lng = $DIC->language();
		$this->lng->loadLanguageModule("dateplaner");
		$this->tree = $DIC->repositoryTree();
		$this->ui = $DIC->ui();
		$this->list_item = $a_list_item;
		$this->ctrl = $DIC->ctrl();
		$this->access = $DIC->access();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->user = $DIC->user();
	}
	
	
	/**
	 *
	 * @return self
	 */
	public static function getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item)
	{
		return new static($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
	}

	/**
	 * @return ilToolbarGUI
	 */
	public function getToolbar()
	{
		return $this->toolbar;
	}

	/**
	 * Get list item
	 *
	 * @return \ILIAS\UI\Component\Item\Standard
	 */
	public function getListItem()
	{
		return $this->list_item;
	}


	/**
	 * @return ilInfoScreenGUI
	 */
	public function getInfoScreen()
	{
		return $this->infoscreen;
	}

	public function getCatId($a_entry_id)
	{
		return ilCalendarCategoryAssignments::_lookupCategory($a_entry_id);
	}

	public function getCatInfo()
	{
		$cat_id = $this->getCatId($this->appointment['event']->getEntryId());
		//$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);

		$cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
		$cat_info = array();
		$cat_info["type"] = $cat->getType();
		$cat_info["obj_id"] = $cat->getObjId();
		$cat_info["title"] = $cat->getTitle();
		$cat_info["cat_id"] = $cat_id;
		$cat_info["editable"] = false;

		switch ($cat_info["type"])
		{
			case ilCalendarCategory::TYPE_USR:
				if ($cat_info["obj_id"] == $this->user->getId())
				{
					$cat_info["editable"] = true;
				}
				break;

			case ilCalendarCategory::TYPE_OBJ:
				$obj_type = ilObject::_lookupType($cat_info["obj_id"]);
				if ($obj_type == 'crs' or $obj_type == 'grp')
				{
					if (ilCalendarSettings::_getInstance()->lookupCalendarActivated($cat_info["obj_id"]))
					{
						foreach (ilObject::_getAllReferences($cat_info["obj_id"]) as $ref_id)
						{
							if ($this->access->checkAccess('edit_event', '', $ref_id))
							{
								$cat_info["editable"] = true;
							}
						}
					}
				}
				break;

			case ilCalendarCategory::TYPE_GLOBAL:
				if ($this->rbacsystem->checkAccess('edit_event',ilCalendarSettings::_getInstance()->getCalendarSettingsId()))
				{
					$cat_info["editable"] = true;
				}
				break;
		}

		return $cat_info;
	}

	function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");
		
		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	 * Get HTML
	 */
	function getHTML()
	{
		$this->collectStandardPropertiesAndActions();
		$this->collectPropertiesAndActions();
		$ui = $this->ui;

		$infoscreen = $this->getInfoScreen();
		if ($infoscreen instanceof ilInfoScreenGUI)
		{
			foreach ($this->info_items as $i)
			{
				switch ($i["type"])
				{
					case "section":
						$infoscreen->addSection($i["txt"]);
						break;
					case "property":
						$infoscreen->addProperty($i["txt"], $i["val"]);
						break;
				}
			}
		}

		$toolbar = $this->getToolbar();
		if ($toolbar instanceof ilToolbarGUI)
		{
			//todo: duplicated from ilcalendarviewgui.
			$settings = ilCalendarSettings::_getInstance();
			if($settings->isBatchFileDownloadsEnabled() && $this->has_files)
			{
				// file download
				$this->ctrl->setParameter($this, "app_id", $this->appointment['event']->getEntryId());
				$add_button = $this->ui->factory()->button()->standard($this->lng->txt("cal_download_files"),
					$this->ctrl->getLinkTarget($this, "downloadFiles"));
				$this->ctrl->setParameter($this, "app_id", $_GET["app_id"]);
				$toolbar->addComponent($add_button);
				$toolbar->addSeparator();
			}

			foreach ($this->actions as $a)
			{
				$btn = ilLinkButton::getInstance();
				$btn->setCaption($a["txt"], false);
				$btn->setUrl($a["link"]);
				$toolbar->addButtonInstance($btn);
			}
		}

		$list_item = $this->getListItem();
		if ($list_item instanceof \ILIAS\UI\Component\Item\Standard)
		{
			$dd = $list_item->getActions();
			if ($dd === null)
			{
				$actions = array();
				$label = "";
			}
			else
			{
				$actions = $dd->getItems();
				$label = $dd->getLabel();
			}
			$properties = $list_item->getProperties();

			foreach ($this->actions as $a)
			{
				$actions[] = $ui->factory()->button()->shy($a["txt"], $a["link"]);
			}
			foreach ($this->list_properties as $lp)
			{
				$properties[$lp["txt"]] = $lp["val"];
			}

			$new_dd =  $ui->factory()->dropdown()->standard($actions)
				->withLabel($label);
			$this->list_item = $list_item->withActions($new_dd)->withProperties($properties);
		}
	}

	/**
	 * Add course/group container info
	 *
	 * @param int $a_ref_id
	 */
	function addContainerInfo($a_obj_id)
	{
		$refs = $this->getReadableRefIds($a_obj_id);
		$ref_id = current($refs);
		if (count($refs) == 1 && $ref_id > 0)
		{
			$tree = $this->tree;
			$f = $this->ui->factory();
			$r = $this->ui->renderer();

			//parent course or group title
			$cont_ref_id = $tree->checkForParentType($ref_id, 'grp');
			if ($cont_ref_id == 0)
			{
				$cont_ref_id = $tree->checkForParentType($ref_id, 'crs');
			}

			if ($cont_ref_id > 0)
			{
				$type = ilObject::_lookupType($cont_ref_id, true);
				$href = ilLink::_getStaticLink($cont_ref_id);
				$parent_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($cont_ref_id));
				$this->addInfoProperty($this->lng->txt("obj_" . $type), $r->render($f->button()->shy($parent_title, $href)));
				$this->addListItemProperty($this->lng->txt("obj_" . $type), $r->render($f->button()->shy($parent_title, $href)));
			}
		}
	}

	/**
	 * Add info section
	 *
	 * @param string $a_txt
	 */
	function addInfoSection($a_txt)
	{
		$this->info_items[] = array ("type" => "section", "txt" => $a_txt);
	}

	/**
	 * Add info property
	 *
	 * @param string $a_txt
	 * @param string $a_val
	 */
	function addInfoProperty($a_txt, $a_val)
	{
		$this->info_items[] = array ("type" => "property", "txt" => $a_txt, "val" => $a_val);
	}

	/**
	 * Add list item property
	 *
	 * @param string $a_txt
	 * @param string $a_val
	 */
	function addListItemProperty($a_txt, $a_val)
	{
		#22638
		$this->list_properties[] = array("txt" => $a_txt, "val" => $a_val);
	}
	
	/**
	 * Add action
	 *
	 * @param string $a_txt
	 * @param string $a_link
	 */
	function addAction($a_txt, $a_link)
	{
		$this->actions[] = array ("txt" => $a_txt, "link" => $a_link);
	}
	

	/**
	 * Collect properties and actions
	 */
	function collectPropertiesAndActions()
	{

	}
	
	/**
	 * Collect standard properties and actions
	 */
	function collectStandardPropertiesAndActions()
	{
		$cat_info = $this->getCatInfo();

		//we can move this to the factory.
		if($cat_info['editable'] and !$this->appointment['event']->isAutoGenerated())
		{
			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
//			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed', $this->getSeed()->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$this->appointment['event']->getEntryId());
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','dt',$this->appointment['dstart']);

			$this->addAction($this->lng->txt("edit"),
				$this->ctrl->getLinkTargetByClass(array('ilcalendarappointmentgui'), 'askEdit'));

			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
//			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->getSeed()->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$this->appointment['event']->getEntryId());
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','dt',$this->appointment['dstart']);

			$this->addAction($this->lng->txt("delete"),
				$this->ctrl->getLinkTargetByClass(array('ilcalendarappointmentgui'), 'askDelete'));
		}

	}

	/**
	 * Add object link
	 *
	 * @param int $ojb_id
	 */
	function addObjectLinks($obj_id)
	{
		$refs = $this->getReadableRefIds($obj_id);
		reset($refs);
		$title = ilObject::_lookupTitle($obj_id);
		$buttons = array();
		foreach ($refs as $ref_id)
		{
			$link_title = $title;
			if (count($refs) > 1)
			{
				$par_ref = $this->tree->getParentId($ref_id);
				$link_title.= " (".ilObject::_lookupTitle(ilObject::_lookupObjId($par_ref)).")";
			}
			$buttons[] = $this->ui->renderer()->render(
				$this->ui->factory()->button()->shy($link_title, ilLink::_getStaticLink($ref_id)));
		}
		if ($refs == 0)
		{
			$prop_value = $title;
		}
		else
		{
			$prop_value = implode("<br>", $buttons);
		}
		if($prop_value != '')
		{
			$this->addInfoProperty($this->lng->txt("obj_".ilObject::_lookupType($obj_id)), $prop_value);
			$this->addListItemProperty($this->lng->txt("obj_".ilObject::_lookupType($obj_id)), $prop_value);
		}
	}

	/**
	 * Get readable ref ids
	 *
	 * @param
	 * @return
	 */
	function getReadableRefIds($a_obj_id)
	{
		if (!isset($this->readable_ref_ids[$a_obj_id]))
		{
			$ref_ids = array();
			foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id)
			{
				if ($this->access->checkAccess("read", "", $ref_id))
				{
					$ref_ids[] = $ref_id;
				}
			}
			$this->readable_ref_ids[$a_obj_id] = $ref_ids;
		}
		return $this->readable_ref_ids[$a_obj_id];
	}

	/**
	 * Add event description
	 *
	 * @param array $a_app
	 */
	function addEventDescription($a_app)
	{
		if ($a_app['event']->getDescription()) {
			$this->addInfoProperty($this->lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
		}
	}

	/**
	 * Add event location
	 *
	 * @param array $a_app
	 */
	function addEventLocation($a_app)
	{
		if ($a_app['event']->getLocation()) {
			$this->addInfoProperty($this->lng->txt("cal_where"), $a_app['event']->getLocation());
			$this->addListItemProperty($this->lng->txt("location"), $a_app['event']->getLocation());
		}
	}

	/**
	 * Add last update
	 *
	 * @param array $a_app
	 */
	function addLastUpdate($a_app)
	{
		$update = new ilDateTime($a_app["event"]->getLastUpdate()->get(IL_CAL_UNIX), IL_CAL_UNIX, $this->user->getTimeZone());
		$this->addListItemProperty($this->lng->txt('last_update'), ilDatePresentation::formatDate($update));
	}

	/**
	 * Add calendar info
	 *
	 * @param array $cat_info
	 */
	function addCalendarInfo($cat_info)
	{
		$this->ctrl->setParameterByClass("ilCalendarPresentationGUI", "category_id", $cat_info["cat_id"]);

		$link = $this->ui->renderer()->render(
			$this->ui->factory()->button()->shy($cat_info["title"],
				$this->ctrl->getLinkTargetByClass(array("ilPersonalDesktopGUI", "ilCalendarPresentationGUI"), "")));

		$this->ctrl->setParameterByClass("ilCalendarPresentationGUI", "category_id", $_GET["category_id"]);

		$this->addInfoProperty($this->lng->txt("calendar"), $link);
		$this->addListItemProperty($this->lng->txt("calendar"), $link);
	}

	/**
	 * Add common section
	 *
	 * @param array $a_app
	 * @param int $a_obj_id
	 */
	function addCommonSection($a_app, $a_obj_id = 0, $cat_info = null, $a_container_info = false)
	{
		// event title
		$this->addInfoSection($a_app["event"]->getPresentationTitle());

		// event description
		$this->addEventDescription($a_app);

		// course title (linked of accessible)
		if ($a_obj_id > 0)
		{
			$this->addObjectLinks($a_obj_id);
		}

		// container info (course groups)
		if ($a_container_info)
		{
			$this->addContainerInfo($a_obj_id);
		}

		// event location
		$this->addEventLocation($a_app);

		// calendar info
		if ($cat_info != null)
		{
			$this->addCalendarInfo($cat_info);
		}

	}

	/**
	 * Add metadata
	 */
	function addMetaData($a_obj_type, $a_obj_id, $a_sub_obj_type = null, $a_sub_obj_id = null)
	{
		//TODO: Remove the hack in ilADTActiveRecordByType.php.
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_APP_PRESENTATION, $a_obj_type, $a_obj_id, $a_sub_obj_type, $a_sub_obj_id);
		$md_items = $record_gui->parse();
		if(count($md_items))
		{
			foreach($md_items as $md_item)
			{
				$this->addInfoProperty($md_item['title'],$md_item['value']);
				$this->addListItemProperty($md_item['title'],$md_item['value']);
			}
		}
	}

	/**
	 * Get (linked if possible) user name
	 *
	 * @param int $a_user_id
	 * @return string
	 */
	function getUserName($a_user_id, $a_force_name = false)
	{
		$type = ilObject::_lookupType((int) $_GET["ref_id"], true);
		$ctrl_path = array();
		if ($type == "crs")
		{
			$ctrl_path[] = "ilobjcoursegui";
		}
		if ($type == "grp")
		{
			$ctrl_path[] = "ilobjgroupgui";
		}
		if (strtolower($_GET["baseClass"]) == "ilpersonaldesktopgui")
		{
			$ctrl_path[] = "ilpersonaldesktopgui";
		}
		$ctrl_path[] = "ilCalendarPresentationGUI";
		$ctrl_path[] = "ilpublicuserprofilegui";

		return ilUserUtil::getNamePresentation(
			$a_user_id, 
			false, 
			true, 
			$this->ctrl->getParentReturn($this),
			$a_force_name,
			false,
			true, 
			false, 
			$ctrl_path);
	}

	/**
	 * Download files from an appointment ( Modals )
	 */
	function downloadFiles()
	{
		$appointment = $this->appointment;

		//calendar in the sidebar (marginal calendar)
		if(empty($appointment))
		{
			$entry_id = (int)$_GET['app_id'];
			$entry = new ilCalendarEntry($entry_id);
			//if the entry exists
			if($entry->getStart())
			{
				$appointment = array(
					"event" => $entry,
					"dstart" => $entry->getStart(),
					"dend"	=> $entry->getEnd(),
					"fullday" => $entry->isFullday()
				);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("obj_not_found"), true);
				$this->ctrl->returnToParent($this);
			}
		}

		include_once './Services/Calendar/classes/BackgroundTasks/class.ilDownloadFilesBackgroundTask.php';
		$download_job = new ilDownloadFilesBackgroundTask($this->user->getId());

		$download_job->setBucketTitle($this->lng->txt("cal_calendar_download")." ".$appointment['event']->getTitle());
		$download_job->setEvents(array($appointment));
		if($download_job->run())
		{
			ilUtil::sendSuccess($this->lng->txt('cal_download_files_started'),true);
		}
		$this->ctrl->returnToParent($this);
	}

}