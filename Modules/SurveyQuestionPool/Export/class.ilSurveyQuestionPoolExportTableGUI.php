<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyQuestionPoolExportTableGUI extends ilTable2GUI
{
    protected bool $confirmdelete;
    protected int $counter;
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $confirmdelete = false
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->confirmdelete = $confirmdelete;
        $this->counter = 0;
        
        $this->setFormName('phrases');
        $this->setTitle($this->lng->txt('svy_export_files'));
        $this->setStyle('table', 'fullwidth');
        if (!$confirmdelete) {
            $this->addColumn('', 'f', '1%');
        }
        $this->addColumn($this->lng->txt("file"), 'file', '');
        $this->addColumn($this->lng->txt("size"), 'size', '');
        $this->addColumn($this->lng->txt("date"), 'date', '');

        if ($confirmdelete) {
            $this->addCommandButton('deleteExportFile', $this->lng->txt('confirm'));
            $this->addCommandButton('cancelDeleteExportFile', $this->lng->txt('cancel'));
        } else {
            $this->addMultiCommand('downloadExportFile', $this->lng->txt('download'));
            $this->addMultiCommand('confirmDeleteExportFile', $this->lng->txt('delete'));
        }

        $this->setRowTemplate("tpl.il_svy_qpl_export_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("file");
        $this->setDefaultOrderDirection("asc");
        
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

    protected function fillRow(array $a_set) : void
    {
        if (!$this->confirmdelete) {
            $this->tpl->setCurrentBlock('checkbox');
            $this->tpl->setVariable('CB_ID', $this->counter);
            $this->tpl->setVariable('CB_FILENAME', ilLegacyFormElementsUtil::prepareFormOutput($a_set['file']));
        } else {
            $this->tpl->setCurrentBlock('hidden');
            $this->tpl->setVariable('HIDDEN_FILENAME', ilLegacyFormElementsUtil::prepareFormOutput($a_set['file']));
        }
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable('CB_ID', $this->counter);
        $this->tpl->setVariable("PHRASE", $a_set["phrase"] ?? "");
        $this->tpl->setVariable("FILENAME", ilLegacyFormElementsUtil::prepareFormOutput($a_set['file']));
        $this->tpl->setVariable("SIZE", $a_set["size"]);
        $this->tpl->setVariable("DATE", $a_set["date"]);
        $this->counter++;
    }
}
