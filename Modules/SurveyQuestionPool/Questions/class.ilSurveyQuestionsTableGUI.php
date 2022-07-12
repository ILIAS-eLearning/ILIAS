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
class ilSurveyQuestionsTableGUI extends ilTable2GUI
{
    protected \ILIAS\SurveyQuestionPool\Editing\EditManager $edit_manager;
    protected ilRbacReview $rbacreview;
    protected ilObjUser $user;
    protected bool $editable = true;
    protected bool $writeAccess = false;
    protected array $filter = [];
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_write_access = false
    ) {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->user = $DIC->user();
        $this->setId("spl");
        $this->setPrefix('q_id'); // #16982
        $this->edit_manager = $DIC->surveyQuestionPool()
                                  ->internal()
                                  ->domain()
                                  ->editing();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
    
        $this->setWriteAccess($a_write_access);

        if ($this->getWriteAccess()) {
            $this->addColumn('', '', '1%');
        }
        
        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn($this->lng->txt("obligatory"), "");
        
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'description') === 0) {
                $this->addColumn($this->lng->txt("description"), 'description', '');
            }
            if (strcmp($c, 'type') === 0) {
                $this->addColumn($this->lng->txt("question_type"), 'type', '');
            }
            if (strcmp($c, 'author') === 0) {
                $this->addColumn($this->lng->txt("author"), 'author', '');
            }
            if (strcmp($c, 'created') === 0) {
                $this->addColumn($this->lng->txt("create_date"), 'created', '');
            }
            if (strcmp($c, 'updated') === 0) {
                $this->addColumn($this->lng->txt("last_update"), 'tstamp', '');
            }
        }
        
        $this->addColumn("", "");

        $clip_questions = $this->edit_manager->getQuestionsFromClipboard();
        if ($this->getWriteAccess()) {
            $this->setSelectAllCheckbox('q_id');
        
            $this->addMultiCommand('copy', $this->lng->txt('copy'));
            $this->addMultiCommand('move', $this->lng->txt('move'));
            $this->addMultiCommand('exportQuestion', $this->lng->txt('export'));
            $this->addMultiCommand('deleteQuestions', $this->lng->txt('delete'));
            
            if (count($clip_questions) > 0) {
                $this->addCommandButton('paste', $this->lng->txt('paste'));
            }
            
            $this->addCommandButton("saveObligatory", $this->lng->txt("spl_save_obligatory_state"));
        }


        $this->setRowTemplate("tpl.il_svy_qpl_questions_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        $this->setShowRowsSelector(true);
        
        $this->setFilterCommand('filterQuestionBrowser');
        $this->setResetCommand('resetfilterQuestionBrowser');
        
        $this->initFilter();
    }

    public function initFilter() : void
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
        
        // description
        $ti = new ilTextInputGUI($lng->txt("description"), "description");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["description"] = $ti->getValue();
        
        // author
        $ti = new ilTextInputGUI($lng->txt("author"), "author");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]+$/is');
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
    }

    public function getSelectableColumns() : array
    {
        $lng = $this->lng;
        $cols["description"] = array(
            "txt" => $lng->txt("description"),
            "default" => true
        );
        $cols["type"] = array(
            "txt" => $lng->txt("question_type"),
            "default" => true
        );
        $cols["author"] = array(
            "txt" => $lng->txt("author"),
            "default" => true
        );
        $cols["created"] = array(
            "txt" => $lng->txt("create_date"),
            "default" => true
        );
        $cols["updated"] = array(
            "txt" => $lng->txt("last_update"),
            "default" => true
        );
        return $cols;
    }

    protected function fillRow(array $a_set) : void
    {
        $class = strtolower(SurveyQuestionGUI::_getGUIClassNameForId($a_set["question_id"]));
        $guiclass = $class . "GUI";
        $this->ctrl->setParameterByClass(strtolower($guiclass), "q_id", $a_set["question_id"]);
        $url_edit = "";
        $obligatory = "";
        if ($this->getEditable()) {
            $url_edit = $this->ctrl->getLinkTargetByClass(strtolower($guiclass), "editQuestion");
            
            $this->tpl->setCurrentBlock("title_link_bl");
            $this->tpl->setVariable("QUESTION_TITLE_LINK", $a_set["title"]);
            $this->tpl->setVariable("URL_TITLE", $url_edit);
        } else {
            $this->tpl->setCurrentBlock("title_nolink_bl");
            $this->tpl->setVariable("QUESTION_TITLE", $a_set["title"]);
        }
        $this->tpl->parseCurrentBlock();

        if ((int) $a_set["complete"] === 0) {
            $this->tpl->setCurrentBlock("qpl_warning");
            $this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
            $this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
            $this->tpl->parseCurrentBlock();
        }
        
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'description') === 0) {
                $this->tpl->setCurrentBlock('description');
                $this->tpl->setVariable("QUESTION_COMMENT", ($a_set["description"] ?? '') !== '' ? $a_set["description"] : "&nbsp;");
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'type') === 0) {
                $this->tpl->setCurrentBlock('type');
                $this->tpl->setVariable("QUESTION_TYPE", SurveyQuestion::_getQuestionTypeName($a_set["type_tag"]));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'author') === 0) {
                $this->tpl->setCurrentBlock('author');
                $this->tpl->setVariable("QUESTION_AUTHOR", $a_set["author"]);
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'created') === 0) {
                $this->tpl->setCurrentBlock('created');
                $this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($a_set['created'], IL_CAL_UNIX)));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'updated') === 0) {
                $this->tpl->setCurrentBlock('updated');
                $this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($a_set["tstamp"], IL_CAL_UNIX)));
                $this->tpl->parseCurrentBlock();
            }
        }
        
        // actions
        $list = new ilAdvancedSelectionListGUI();
        $list->setId($a_set["question_id"]);
        $list->setListTitle($this->lng->txt("actions"));
        if ($url_edit) {
            $list->addItem($this->lng->txt("edit"), "", $url_edit);
        }
        $list->addItem($this->lng->txt("preview"), "", $this->ctrl->getLinkTargetByClass(strtolower($guiclass), "preview"));
        $this->tpl->setVariable("ACTION", $list->getHTML());
        $this->tpl->parseCurrentBlock();
            
        // obligatory
        if ($this->getEditable()) {
            $checked = $a_set["obligatory"] ? " checked=\"checked\"" : "";
            $obligatory = "<input type=\"checkbox\" name=\"obligatory[" .
                $a_set["question_id"] . "]\" value=\"1\"" . $checked . " />";
        } elseif ($a_set["obligatory"]) {
            $obligatory = "<img src=\"" . ilUtil::getImagePath("obligatory.png", "Modules/Survey") .
                "\" alt=\"" . $this->lng->txt("question_obligatory") .
                "\" title=\"" . $this->lng->txt("question_obligatory") . "\" />";
        }
        $this->tpl->setVariable("OBLIGATORY", $obligatory);
                    
        if ($this->getWriteAccess()) {
            $this->tpl->setVariable('CBOX_ID', $a_set["question_id"]);
        }
        $this->tpl->setVariable('QUESTION_ID', $a_set["question_id"]);
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
