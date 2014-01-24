<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Export/classes/class.ilExportGUI.php");
require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("class.ilOrgUnitExporter.php");
/**
 * Class ilOrgUnitExportGUI
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilOrgUnitExportGUI extends ilExportGUI {
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilObjOrgUnit
	 */
	protected $ilObjOrgUnit;


	/**
	 * @param ilObjOrgUnitGUI $a_parent_gui
	 * @param null            $a_main_obj
	 */
	function __construct(ilObjOrgUnitGUI $a_parent_gui, $a_main_obj = null)
	{
		global $ilToolbar, $lng, $ilCtrl;

		parent::__construct($a_parent_gui, $a_main_obj);

		$this->toolbar = $ilToolbar;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->ilObjOrgUnit = $a_parent_gui->object;

		if ($this->ilObjOrgUnit->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
			//Simple XML and Simple XLS Export should only be available in the root orgunit folder as it always exports the whole tree
				$this->extendExportGUI();
		}
	}

	private function extendExportGUI() {
		$this->toolbar->addButton($this->lng->txt("simple_xml"), $this->ctrl->getLinkTarget($this, "simpleExport"));
		$this->toolbar->addButton($this->lng->txt("simple_xls"), $this->ctrl->getLinkTarget($this, "simpleExportExcel"));
	}


	public function simpleExport() {
		$ilOrgUnitExporter = new ilOrgUnitExporter();
		$ilOrgUnitExporter->sendAndCreateSimpleExportFile();
	}


	public function simpleExportExcel() {
		$ilOrgUnitExporter = new ilOrgUnitExporter();
		$ilOrgUnitExporter->simpleExportExcel(ilObjOrgUnit::getRootOrgRefId());
	}

}
?>