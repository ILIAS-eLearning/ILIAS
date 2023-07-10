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
class ilSurveyQuestionblockbrowserTableGUI extends ilTable2GUI
{
    protected ilRbacReview $rbacreview;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected bool $editable = true;
    protected bool $writeAccess = false;
    protected array $browsercolumns = array();
    protected array $filter = [];

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjSurvey $a_object,
        bool $a_write_access = false
    ) {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->setWriteAccess($a_write_access);

        $this->setFormName('surveyquestionblockbrowser');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn($this->lng->txt("contains"), 'contains', '');
        $this->addColumn($this->lng->txt("obj_svy"), 'svy', '');

        $this->setPrefix('cb');
        $this->setSelectAllCheckbox('cb');

        $this->addMultiCommand('insertQuestionblocks', $this->lng->txt('insert'));

        $this->setRowTemplate("tpl.il_svy_svy_questionblockbrowser_row.html", "Modules/Survey");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->enable('sort');
        $this->enable('header');
        $this->enable('select_all');
        $this->setFilterCommand('filterQuestionblockBrowser');
        $this->setResetCommand('resetfilterQuestionblockBrowser');

        $this->initFilter();
        $this->initData($a_object);
    }

    public function initData(ilObjSurvey $a_object): void
    {
        $arrFilter = array();
        foreach ($this->getFilterItems() as $item) {
            if ($item->getValue() !== false) {
                $arrFilter[$item->getPostVar()] = $item->getValue();
            }
        }
        $data = $a_object->getQuestionblocksTable($arrFilter);

        $this->setData($data);
    }

    public function initFilter(): void
    {
        $lng = $this->lng;

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["title"] = $ti->getValue();
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('QUESTIONBLOCK_ID', $a_set["questionblock_id"]);
        $this->tpl->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));
        $this->tpl->setVariable("CONTAINS", ilLegacyFormElementsUtil::prepareFormOutput($a_set["contains"]));
        $this->tpl->setVariable("SVY", ilLegacyFormElementsUtil::prepareFormOutput($a_set['svy']));
    }

    public function setEditable(bool $value): void
    {
        $this->editable = $value;
    }

    public function getEditable(): bool
    {
        return $this->editable;
    }

    public function setWriteAccess(bool $value): void
    {
        $this->writeAccess = $value;
    }

    public function getWriteAccess(): bool
    {
        return $this->writeAccess;
    }
}
