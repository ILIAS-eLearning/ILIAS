<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSCORM2004ExportTableGUI extends ilTable2GUI
{
    protected $confirmdelete;
    protected $counter;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $confirmdelete = false)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->confirmdelete = $confirmdelete;
        $this->counter = 0;

        $lng->loadLanguageModule("exp");
        $this->setTitle($lng->txt("exp_export_files"));
        
        $this->setFormName('phrases');
        $this->setStyle('table', 'fullwidth');
        if (!$confirmdelete) {
            $this->addColumn('', '', '1%');
        }
        $this->addColumn($this->lng->txt("type"), 'type');
        $this->addColumn($this->lng->txt("file"), 'file');
        $this->addColumn($this->lng->txt("size"), 'size');
        $this->addColumn($this->lng->txt("date"), 'date');

        if ($confirmdelete) {
            $this->addCommandButton('deleteExportFile', $this->lng->txt('confirm'));
            $this->addCommandButton('cancelDeleteExportFile', $this->lng->txt('cancel'));
        } else {
            $this->addColumn($this->lng->txt("action"));
            //$this->addMultiCommand('downloadExportFile', $this->lng->txt('download'));
            $this->addMultiCommand('confirmDeleteExportFile', $this->lng->txt('delete'));
        }

        $this->setRowTemplate("tpl.scorm2004_export_row.html", "Modules/Scorm2004");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("file");
        $this->setDefaultOrderDirection("desc");
        
        if ($confirmdelete) {
            $this->disable('sort');
            $this->disable('select_all');
        } else {
            $this->setPrefix('file');
            $this->setSelectAllCheckbox('file');
            $this->enable('sort');
            $this->enable('select_all');
        }
        $this->enable('header');
    }

    /**
     * fill row
     *
     * @param array $data data array
     */
    public function fillRow($data)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->confirmdelete) {
            $this->tpl->setCurrentBlock('checkbox');
            $this->tpl->setVariable('CB_ID', $this->counter);
            $this->tpl->setVariable('CB_FILENAME', ilUtil::prepareFormOutput($data['file']));
            $this->tpl->setVariable("FILETYPE", $data["filetype"]);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('action');
            $ilCtrl->setParameter($this->getParentObject(), "file", rawurlencode($data['file']));
            $ilCtrl->setParameter($this->getParentObject(), "type", rawurlencode($data["filetype"]));
            $this->tpl->setVariable("DOWNLOAD_HREF", $ilCtrl->getLinkTarget($this->getParentObject(), "downloadExportFile"));
            $this->tpl->setVariable("DOWNLOAD_TXT", $lng->txt("download"));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('hidden');
            $this->tpl->setVariable('HIDDEN_FILENAME', ilUtil::prepareFormOutput($data['file']));
            $this->tpl->setVariable('HIDDEN_TYPE', ilUtil::prepareFormOutput($data['type']));
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable('CB_ID', $this->counter);
        $this->tpl->setVariable("FILENAME", ilUtil::prepareFormOutput($data['file']));
        $this->tpl->setVariable("SIZE", $data["size"]);
        $this->tpl->setVariable("DATE", $data["date"]);
        $this->tpl->setVariable("TYPE", $data["type"]);
        $this->counter++;
    }
}
