<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Bibliographic Administration Settings.
 *
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilPermissionGUI, ilObjBibliographicAdminLibrariesGUI
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilBiblAdminFieldGUI
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilBiblAdminRisFieldGUI, ilBiblAdminBibtexFieldGUI
 *
 * @ingroup      ModulesBibliographic
 */
class ilObjBibliographicAdminGUI extends ilObjectGUI {

	const TAB_FIELDS = 'fields';
	const TAB_SETTINGS = 'settings';
	/**
	 * @var ilObjBibliographicAdmin
	 */
	public $object;
	/**
	 * @var \ilBiblTranslationFactory
	 */
	protected $translation_factory;
	/**
	 * @var \ilBiblFieldFactory
	 */
	protected $field_factory;
	/**
	 * @var \ilBiblFieldFilterFactory
	 */
	protected $filter_factory;
	/**
	 * @var \ilBiblTypeFactory
	 */
	protected $type_factory;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;


	/**
	 * @param      $a_data
	 * @param      $a_id
	 * @param bool $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true) {
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->type = 'bibs';
		$this->type_factory = new ilBiblTypeFactory();
		$this->filter_factory = new ilBiblFieldFilterFactory();
		$this->lng->loadLanguageModule('bibl');
		//Check Permissions globally for all SubGUIs. We only check write permissions
		$this->checkPermission('write');
	}


	/**
	 * @return bool|void
	 * @throws ilCtrlException
	 */
	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass($this);
		switch ($next_class) {
			case 'ilpermissiongui':
				$this->prepareOutput();
				$this->tabs_gui->activateTab('perm_settings');
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			case strtolower(ilBiblAdminRisFieldGUI::class):
				$this->prepareOutput();
				$this->tabs_gui->activateTab(self::TAB_FIELDS);

				$type = $this->type_factory->getInstanceForType(ilBiblTypeFactoryInterface::DATA_TYPE_RIS);
				$field_factory = new ilBiblFieldFactory($type);

				$ilbibladminrisfieldgui = new ilBiblAdminRisFieldGUI($field_factory, $this->type_factory, new ilBiblTranslationFactory($field_factory));
				$this->ctrl->forwardCommand($ilbibladminrisfieldgui);
				break;
			case strtolower(ilBiblAdminBibtexFieldGUI::class):
				$this->prepareOutput();
				$this->tabs_gui->activateTab(self::TAB_FIELDS);

				$type = $this->type_factory->getInstanceForType(ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX);
				$field_factory = new ilBiblFieldFactory($type);

				$ilbibladminbibtexfieldgui = new ilBiblAdminBibtexFieldGUI($field_factory, $this->type_factory, new ilBiblTranslationFactory($field_factory));
				$this->ctrl->forwardCommand($ilbibladminbibtexfieldgui);
				break;
			default:
				$this->prepareOutput();
				$this->tabs_gui->activateTab(self::TAB_SETTINGS);
				$ilObjBibliographicAdminLibrariesGUI = new ilObjBibliographicAdminLibrariesGUI($this);
				$this->ctrl->forwardCommand($ilObjBibliographicAdminLibrariesGUI);
				break;
		}
	}


	public function getAdminTabs() {
		global $DIC;
		$rbacsystem = $DIC['rbacsystem'];
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		if ($rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
			$this->tabs_gui->addTab(self::TAB_SETTINGS, $this->lng->txt('settings'), $this->ctrl->getLinkTargetByClass(array(
				ilObjBibliographicAdminGUI::class,
				ilObjBibliographicAdminLibrariesGUI::class,
			), 'view'));
		}
		if ($rbacsystem->checkAccess('write', $this->object->getRefId())) {
			$this->tabs_gui->addTab('fields', $this->lng->txt('fields'), $this->ctrl->getLinkTargetByClass(array(
				ilObjBibliographicAdminGUI::class,
				ilBiblAdminRisFieldGUI::class,
			), ilBiblAdminRisFieldGUI::CMD_STANDARD));
		}
		if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
		}
	}


	/**
	 * @return \ilTabsGUI
	 */
	public function getTabsGui() {
		return $this->tabs_gui;
	}


	/**
	 * @param \ilTabsGUI $tabs_gui
	 */
	public function setTabsGui($tabs_gui) {
		$this->tabs_gui = $tabs_gui;
	}
}