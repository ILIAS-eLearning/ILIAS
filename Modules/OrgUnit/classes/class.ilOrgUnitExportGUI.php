<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOrgUnitExportGUI
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitExportGUI extends ilExportGUI
{
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilObjOrgUnit $ilObjOrgUnit;

    public function __construct(ilObjOrgUnitGUI $a_parent_gui, null|ilObject|ilObjOrgUnit $a_main_obj = null)
    {
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        parent::__construct($a_parent_gui, $a_main_obj);

        $this->toolbar = $ilToolbar;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->ilObjOrgUnit = $a_parent_gui->object;

        if ($this->ilObjOrgUnit->getRefId() === ilObjOrgUnit::getRootOrgRefId()) {
            //Simple XML and Simple XLS Export should only be available in the root orgunit folder as it always exports the whole tree
            $this->extendExportGUI();
        }
    }

    public function listExportFiles() : void
    {
        if ($this->ilObjOrgUnit->getRefId() != ilObjOrgUnit::getRootOrgRefId()) {
            parent::listExportFiles();
        }
    }

    private function extendExportGUI() : void
    {
        $this->toolbar->addButton($this->lng->txt("simple_xml"), $this->ctrl->getLinkTarget($this, "simpleExport"));
        $this->toolbar->addButton($this->lng->txt("simple_xls"),
            $this->ctrl->getLinkTarget($this, "simpleExportExcel"));
    }

    public function simpleExport() : void
    {
        $ilOrgUnitExporter = new ilOrgUnitExporter();
        $ilOrgUnitExporter->sendAndCreateSimpleExportFile();
    }

    public function simpleExportExcel() : void
    {
        $ilOrgUnitExporter = new ilOrgUnitExporter();
        $ilOrgUnitExporter->simpleExportExcel(ilObjOrgUnit::getRootOrgRefId());
    }
}
