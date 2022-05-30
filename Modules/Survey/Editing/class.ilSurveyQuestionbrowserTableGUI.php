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
class ilSurveyQuestionbrowserTableGUI extends ilTable2GUI
{
    protected ilRbacReview $rbacreview;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected bool $editable = true;
    protected bool $writeAccess = false;
    protected array $browsercolumns = array();
    protected ?array $questionpools = null;
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

        $this->setFormName('surveyquestionbrowser');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn('', 'preview', '');
        $this->addColumn($this->lng->txt("description"), 'description', '');
        $this->addColumn($this->lng->txt("question_type"), 'ttype', '');
        $this->addColumn($this->lng->txt("author"), 'author', '');
        $this->addColumn($this->lng->txt("create_date"), 'created', '');
        $this->addColumn($this->lng->txt("last_update"), 'updated', '');
        $this->addColumn($this->lng->txt("obj_spl"), 'spl', '');

        $this->setPrefix('q_id');
        $this->setSelectAllCheckbox('q_id');
        
        $this->addMultiCommand('insertQuestions', $this->lng->txt('insert'));

        $this->setRowTemplate("tpl.il_svy_svy_questionbrowser_row.html", "Modules/Survey");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->questionpools = ilObjSurveyQuestionPool::_getAvailableQuestionpools(true, false, true);
        
        $this->enable('sort');
        $this->enable('header');
        $this->enable('select_all');
        $this->setFilterCommand('filterQuestionBrowser');
        $this->setResetCommand('resetfilterQuestionBrowser');

        $this->initFilter();
        $this->initData($a_object);
    }
    
    public function initData(ilObjSurvey $a_object) : void
    {
        $arrFilter = array();
        foreach ($this->getFilterItems() as $item) {
            if ($item->getValue() !== false) {
                $arrFilter[$item->getPostVar()] = $item->getValue();
            }
        }
        $data = $a_object->getQuestionsTable($arrFilter);
        
        // translate pools for proper sorting
        if (count($data)) {
            $pools = $this->getQuestionPools();
            foreach ($data as $idx => $row) {
                $data[$idx]["spl"] = $pools[$row["obj_fi"]];
            }
        }
        
        $this->setData($data);
    }
    
    public function getQuestionPools() : array
    {
        return $this->questionpools;
    }

    /**
    * Init filter
    */
    public function initFilter() : void
    {
        $lng = $this->lng;

        // title
        $ti = new ilTextInputGUI($lng->txt("survey_question_title"), "title");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["title"] = $ti->getValue();
        
        // description
        $ti = new ilTextInputGUI($lng->txt("description"), "description");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["description"] = $ti->getValue();
        
        // author
        $ti = new ilTextInputGUI($lng->txt("author"), "author");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["author"] = $ti->getValue();
        
        // questiontype
        $types = ilObjSurveyQuestionPool::_getQuestiontypes();
        $options = array();
        $options[""] = $lng->txt('filter_all_question_types');
        foreach ($types as $translation => $row) {
            $options[$row['type_tag']] = $translation;
        }

        $si = new ilSelectInputGUI($this->lng->txt("question_type"), "type");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["type"] = $si->getValue();
        
        
        // questionpool text
        $ti = new ilTextInputGUI($lng->txt("survey_question_pool_title"), "spl_txt");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["spl_txt"] = $ti->getValue();
        
        // questionpool select
        $options = array();
        $options[""] = $lng->txt('filter_all_questionpools');
        natcasesort($this->questionpools);
        foreach ($this->questionpools as $obj_id => $title) {
            $options[$obj_id] = $title;
        }
        $si = new ilSelectInputGUI($this->lng->txt("survey_available_question_pools"), "spl");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["type"] = $si->getValue();
    }
    
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('QUESTION_ID', $a_set["question_id"]);
        $this->tpl->setVariable("QUESTION_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));

        $this->tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
        $guiclass = strtolower($a_set['type_tag']) . "gui";
        $this->ctrl->setParameterByClass($guiclass, "q_id", $a_set["question_id"]);
        $this->tpl->setVariable("LINK_PREVIEW", "ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&amp;ref_id=" . $a_set["ref_id"] . "&amp;cmd=preview&amp;preview=" . $a_set["question_id"]);

        $this->tpl->setVariable(
            "QUESTION_DESCRIPTION",
            ilLegacyFormElementsUtil::prepareFormOutput(($a_set["description"] ?? '') !== '' ? $a_set["description"] : "")
        );
        $this->tpl->setVariable("QUESTION_TYPE", $a_set["ttype"]);
        $this->tpl->setVariable("QUESTION_AUTHOR", ilLegacyFormElementsUtil::prepareFormOutput($a_set["author"]));
        $this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($a_set['created'], IL_CAL_UNIX)));
        $this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($a_set["tstamp"], IL_CAL_UNIX)));
        $this->tpl->setVariable("QPL", ilLegacyFormElementsUtil::prepareFormOutput($a_set["spl"]));
    }
    
    public function setEditable(bool $value) : void
    {
        $this->editable = $value;
    }
    
    public function getEditable() : bool
    {
        return $this->editable;
    }

    public function setWriteAccess(bool $value) : void
    {
        $this->writeAccess = $value;
    }
    
    public function getWriteAccess() : bool
    {
        return $this->writeAccess;
    }
}
