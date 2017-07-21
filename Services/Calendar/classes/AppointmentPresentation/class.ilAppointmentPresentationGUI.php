<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';

/**
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
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

	public function getCatInfo($a_cat_id)
	{
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($a_cat_id);
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
	function addContainerInfo($a_ref_id)
	{
		$tree = $this->tree;
		$f = $this->ui->factory();
		$r = $this->ui->renderer();

		//parent course or group title
		$cont_ref_id = $tree->checkForParentType($a_ref_id, 'grp');
		if ($cont_ref_id == 0)
		{
			$cont_ref_id = $tree->checkForParentType($a_ref_id, 'crs');
		}

		if ($cont_ref_id > 0)
		{
			$type = ilObject::_lookupType($cont_ref_id, true);
			$href = ilLink::_getStaticLink($cont_ref_id);
			$parent_title = ilObject::_lookupTitle(ilObject::_lookupObjectId($cont_ref_id));
			$this->addInfoProperty($this->lng->txt("obj_" . $type), $r->render($f->button()->shy($parent_title, $href)));
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
		$cat_id = $this->getCatId($this->appointment['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		//we can move this to the factory.
		if($cat_info['editable'] and !$this->appointment['event']->isAutoGenerated())
		{
			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
//			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed', $this->getSeed()->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$this->appointment['event']->getEntryId());
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','dt',$this->appointment['dstart']);

			$this->addAction($this->lng->txt("edit"),
				$this->ctrl->getLinkTargetByClass(array('ilPersonalDesktopGUI', 'ilCalendarPresentationGUI', 'ilCalendarCategoryGUI', 'ilcalendarappointmentgui'), 'askEdit'));

			$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
//			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$this->getSeed()->get(IL_CAL_DATE));
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$this->appointment['event']->getEntryId());
			$this->ctrl->setParameterByClass('ilcalendarappointmentgui','dt',$this->appointment['dstart']);

			$this->addAction($this->lng->txt("delete"),
				$this->ctrl->getLinkTargetByClass(array('ilPersonalDesktopGUI', 'ilCalendarPresentationGUI', 'ilCalendarCategoryGUI', 'ilcalendarappointmentgui'), 'askDelete'));
		}

	}

	//TODO : SOME ELEMENTS CAN GET CUSTOM METADATA
}