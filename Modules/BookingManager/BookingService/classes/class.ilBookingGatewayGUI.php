<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BookingManager;

/**
 * This class is used for inegration of the booking manager as a service
 * into other repository objects, e.g. courses.
 *
 * @ilCtrl_Calls ilBookingGatewayGUI: ilPropertyFormGUI, ilBookingObjectServiceGUI, ilBookingReservationsGUI
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingGatewayGUI
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
	 * @var ilTemplate
	 */
	protected $main_tpl;

	/**
	 * @var ilObjectGUI
	 */
	protected $parent_gui;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var ilObjBookingServiceSettings
	 */
	protected $current_settings;

	/**
	 * @var int
	 */
	protected $current_pool_ref_id;

	/**
	 * @var ilObjBookingPool|null
	 */
	protected $pool = null;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var int
	 */
	protected $main_host_ref_id = 0;

	/**
	 * Have any pools been already selected?
	 * @var bool
	 */
	protected $pools_selected = false;

	/**
	 * Constructor
	 */
	public function __construct(ilObjectGUI $parent_gui, $main_host_ref_id = 0)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->main_tpl = $DIC->ui()->mainTemplate();
		$this->parent_gui = $parent_gui;
		$this->tabs = $DIC->tabs();

		$this->lng->loadLanguageModule("book");

		// current parent context (e.g. session in course)
		$this->obj_id = (int) $parent_gui->object->getId();
		$this->ref_id = (int) $parent_gui->object->getRefId();

		$this->main_host_ref_id = ($main_host_ref_id == 0)
			? $this->ref_id
			: $main_host_ref_id;


		$this->seed = ilUtil::stripSlashes($_GET['seed']);
		$this->sseed = ilUtil::stripSlashes($_GET['sseed']);

		$this->toolbar = $DIC->toolbar();

		$this->use_book_repo = new ilObjUseBookDBRepository($DIC->database());

		if (in_array($_REQUEST["return_to"], ["ilbookingobjectservicegui", "ilbookingreservationsgui"]))
		{
			$this->return_to = $_REQUEST["return_to"];
		}

		// get current settings
		$handler = new BookingManager\getObjectSettingsCommandHandler(
			new BookingManager\getObjectSettingsCommand($this->obj_id),
			$this->use_book_repo
		);
		$this->current_settings = $handler->handle()->getSettings();

		$this->initPool();

		if (is_object($this->pool)) {
			$this->help = new ilBookingHelpAdapter($this->pool, $DIC["ilHelp"]);
			$DIC["ilHelp"]->setScreenIdComponent("book");
		}
	}

	/**
	 * Init pool. Determin the current pool in $this->current_pool_ref_id.
	 *
	 * Host objects (e.g. courses) may use multiple booking pools. This method determines the current selected
	 * pool (stored in request parameter "pool_ref_id") within the host object user interface.
	 *
	 * If no pool has been selected yet, the first one attached to the host object is choosen.
	 *
	 * If no pools are attached to the host object at all we get a 0 ID.
	 */
	protected function initPool()
	{
		$ctrl = $this->ctrl;

		$ctrl->saveParameter($this, "pool_ref_id");
		$pool_ref_id  = ($_POST["pool_ref_id"] > 0)
			? (int) $_POST["pool_ref_id"]
			: (int) $_GET["pool_ref_id"];

		$book_ref_ids = $this->use_book_repo->getUsedBookingPools(ilObject::_lookupObjId($this->main_host_ref_id));

		$this->pools_selected = (count($book_ref_ids) > 0);

		if (!in_array($pool_ref_id, $book_ref_ids))
		{
			if (count($book_ref_ids) > 0)
			{
				$pool_ref_id = current($book_ref_ids);
			}
			else
			{
				$pool_ref_id = 0;
			}
		}
		$this->current_pool_ref_id = $pool_ref_id;
		if ($this->current_pool_ref_id > 0)
		{
			$this->pool = new ilObjBookingPool($this->current_pool_ref_id);
			$ctrl->setParameter($this, "pool_ref_id", $this->current_pool_ref_id);
		}
	}

	/**
	 * Execute command
	 * @throws ilCtrlException
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;

		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("show");

		switch ($next_class)
		{
			case "ilpropertyformgui":
				$form = $this->initSettingsForm();
				$ctrl->setReturn($this, 'settings');
				$ctrl->forwardCommand($form);
				break;

			case "ilbookingobjectservicegui":
				$this->setSubTabs("book_obj");
				$this->showPoolSelector("ilbookingobjectservicegui");
				$book_ser_gui = new ilBookingObjectServiceGUI($this->ref_id,
					$this->current_pool_ref_id,
					$this->use_book_repo, $this->seed, $this->sseed, $this->help);
				$ctrl->forwardCommand($book_ser_gui);
				break;

			case "ilbookingreservationsgui":
				$this->showPoolSelector("ilbookingreservationsgui");
				$this->setSubTabs("reservations");
				$res_gui = new ilBookingReservationsGUI($this->pool, $this->help, $this->obj_id);
				$this->ctrl->forwardCommand($res_gui);
				break;


			default:
				if (in_array($cmd, array("show", "settings", "saveSettings", "selectPool")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Pool selector
	 */
	protected function showPoolSelector($return_to)
	{
		//
		$options = [];
		foreach ($this->use_book_repo->getUsedBookingPools(ilObject::_lookupObjectId($this->main_host_ref_id)) as $ref_id)
		{
			$options[$ref_id] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
		}

		$this->ctrl->setParameter($this,"return_to", $return_to);
		if (count($options) > 0) {
			$si = new ilSelectInputGUI("", "pool_ref_id");
			$si->setOptions($options);
			$si->setValue($this->current_pool_ref_id);
			$this->toolbar->setFormAction($this->ctrl->getFormAction($this));
			$this->toolbar->addInputItem($si, false);
			$this->toolbar->addFormButton($this->lng->txt("book_select_pool"), "selectPool");
		}
	}
	
	/**
	 * Select pool
	 */
	protected function selectPool()
	{
		if ($this->return_to != "") {
			$this->ctrl->redirectByClass($this->return_to);
		}
	}

	/**
	 * Set sub tabs
	 *
	 * @param $active
	 */
	protected function setSubTabs($active)
	{
		$tabs = $this->tabs;
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		if ($this->pools_selected) {
			$tabs->addSubTab("book_obj",
				$lng->txt("book_objects_list"),
				$ctrl->getLinkTargetByClass("ilbookingobjectservicegui", ""));
			$tabs->addSubTab("reservations",
				$lng->txt("book_log"),
				$ctrl->getLinkTargetByClass("ilbookingreservationsgui", ""));
		}
		if ($this->ref_id == $this->main_host_ref_id) {
			$tabs->addSubTab("settings",
				$lng->txt("settings"),
				$ctrl->getLinkTarget($this, "settings"));
		}

		$tabs->activateSubTab($active);
	}
	
	
	/**
	 * Show
	 */
	protected function show()
	{
		$ctrl = $this->ctrl;
		if ($this->pools_selected) {
			$ctrl->redirectByClass("ilbookingobjectservicegui");
		}
		else if ($this->ref_id == $this->main_host_ref_id) {
			$ctrl->redirect($this, "settings");
		}

		ilUtil::sendFailure($this->lng->txt("book_no_pools_selected"));
	}

	//
	// Settings
	//

	/**
	 * Settings
	 */
	protected function settings()
	{
		$this->setSubTabs("settings");
		$main_tpl = $this->main_tpl;
		$form = $this->initSettingsForm();
		$main_tpl->setContent($form->getHTML());
	}

	/**
	 * Init settings form.
	 */
	public function initSettingsForm()
	{
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$form = new ilPropertyFormGUI();

		// booking tools
		$repo = new ilRepositorySelector2InputGUI($this->lng->txt("objs_book"), "booking_obj_ids", true);
		$repo->getExplorerGUI()->setSelectableTypes(["book"]);
		$repo->getExplorerGUI()->setTypeWhiteList(
			["book", "root", "cat", "grp", "fold", "crs"]
		);
		$form->addItem($repo);
		$repo->setValue($this->current_settings->getUsedBookingObjectIds());

		$form->addCommandButton("saveSettings", $lng->txt("save"));

		$form->setTitle($lng->txt("book_pool_selection"));
		$form->setFormAction($ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save settings form
	 */
	public function saveSettings()
	{
		$ctrl = $this->ctrl;
		$lng = $this->lng;
		$main_tpl = $this->main_tpl;

		$form = $this->initSettingsForm();
		if ($form->checkInput())
		{
			$b_ids = $form->getInput("booking_obj_ids");
			$b_ids = is_array($b_ids)
				? array_map(function ($i) {
					return (int) $i;
				}, $b_ids)
				: [];

			if (!$this->checkBookingPoolsForSchedules($b_ids)) {
				ilUtil::sendFailure($lng->txt("book_all_pools_need_schedules"));
				$form->setValuesByPost();
				$main_tpl->setContent($form->getHtml());
				return;
			}

			$cmd = new BookingManager\saveObjectSettingsCommand(new ilObjBookingServiceSettings(
				$this->obj_id,
				$b_ids
			));

			$repo = $this->use_book_repo;
			$handler = new BookingManager\saveObjectSettingsCommandHandler($cmd, $repo);
			$handler->handle();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ctrl->redirect($this, "");
		}
		else
		{
			$form->setValuesByPost();
			$main_tpl->setContent($form->getHtml());
		}
	}

	/**
	 * Check if all pools have schedules
	 *
	 * @param int[] $ids pool ref ids
	 * @return bool
	 */
	protected function checkBookingPoolsForSchedules($ids)
	{
		foreach ($ids as $pool_ref_id) {
			if (!ilBookingSchedule::hasExistingSchedules(ilObject::_lookupObjectId($pool_ref_id))) {
				return false;
			}
		}
		return true;
	}


}