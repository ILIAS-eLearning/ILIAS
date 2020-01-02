<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewGUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTestQuestionPool
 *
 * @ilCtrl_Calls ilQuestionBrowserTableGUI: ilFormPropertyDispatchGUI
*/

class ilQuestionBrowserTableGUI extends ilTable2GUI
{
    protected $editable 		= true;
    protected $writeAccess 		= false;
    protected $totalPoints 		= 0;
    protected $totalWorkingTime = '00:00:00';
    protected $confirmdelete;
    
    protected $taxIds = array();
    
    /**
     * @var bool
     */
    protected $questionCommentingEnabled = false;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false, $confirmdelete = false, $taxIds = array(), $enableCommenting = false)
    {
        $this->setQuestionCommentingEnabled($enableCommenting);
        
        // Bugfix: #0019539
        if ($confirmdelete) {
            $this->setId("qpl_confirm_del_" . $a_parent_obj->object->getRefId());
        } else {
            $this->setId("qpl_qst_brows_" . $a_parent_obj->object->getRefId());
        }
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
    
        $this->confirmdelete = $confirmdelete;
        $this->setWriteAccess($a_write_access);
        $this->taxIds = $taxIds;

        $qplSetting = new ilSetting("qpl");
            
        $this->setFormName('questionbrowser');
        $this->setStyle('table', 'fullwidth');
        if (!$confirmdelete) {
            $this->addColumn('', '', '1%');
            $this->addColumn($this->lng->txt("title"), 'title', '');
            foreach ($this->getSelectedColumns() as $c) {
                if (strcmp($c, 'description') == 0) {
                    $this->addColumn($this->lng->txt("description"), 'description', '');
                }
                if (strcmp($c, 'type') == 0) {
                    $this->addColumn($this->lng->txt("question_type"), 'ttype', '');
                }
                // According to mantis #12713
                if (strcmp($c, 'points') == 0) {
                    $this->addColumn($this->lng->txt("points"), 'points', '', false, 'ilCenterForced');
                }
                if (strcmp($c, 'statistics') == 0) {
                    $this->addColumn($this->lng->txt('statistics'), '', '');
                }
                if (strcmp($c, 'author') == 0) {
                    $this->addColumn($this->lng->txt("author"), 'author', '');
                }
                if ($this->isQuestionCommentingEnabled() && $c == 'comments') {
                    $this->addColumn($this->lng->txt("ass_comments"), 'comments', '');
                }
                if (strcmp($c, 'created') == 0) {
                    $this->addColumn($this->lng->txt("create_date"), 'created', '');
                }
                if (strcmp($c, 'tstamp') == 0) {
                    $this->addColumn($this->lng->txt("last_update"), 'tstamp', '');
                }
                if (strcmp($c, 'working_time') == 0) {
                    $this->addColumn($this->lng->txt("working_time"), 'working_time', '');
                }
            }
            $this->addColumn($this->lng->txt('actions'), '');
            $this->setSelectAllCheckbox('q_id');
        } else {
            $this->addColumn($this->lng->txt("title"), 'title', '');
            foreach ($this->getSelectedColumns() as $c) {
                if (strcmp($c, 'description') == 0) {
                    $this->addColumn($this->lng->txt("description"), 'description', '');
                }
                if (strcmp($c, 'type') == 0) {
                    $this->addColumn($this->lng->txt("question_type"), 'ttype', '');
                }
            }
        }

        if ($this->getWriteAccess()) {
            if ($confirmdelete) {
                $this->addCommandButton('confirmDeleteQuestions', $this->lng->txt('confirm'));
                $this->addCommandButton('cancelDeleteQuestions', $this->lng->txt('cancel'));
            } else {
                $this->addMultiCommand('copy', $this->lng->txt('copy'));
                $this->addMultiCommand('move', $this->lng->txt('move'));
                $this->addMultiCommand('exportQuestion', $this->lng->txt('export'));
                $this->addMultiCommand('deleteQuestions', $this->lng->txt('delete'));
            }
        }

        $this->setRowTemplate("tpl.il_as_qpl_questionbrowser_row.html", "Modules/TestQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        $this->setShowRowsSelector(true);
        
        if ($confirmdelete) {
            $this->disable('sort');
            $this->disable('select_all');
        } else {
            $this->enable('sort');
            $this->enable('header');
            $this->enable('select_all');
            $this->setFilterCommand('filterQuestionBrowser');
            $this->setResetCommand('resetQuestionBrowser');
            $this->initFilter();
        }
        
        if ($this->isQuestionCommentingEnabled()) {
            global $DIC; /* @var ILIAS\DI\Container $DIC */
            
            $notesUrl = $this->ctrl->getLinkTargetByClass(
                array("ilcommonactiondispatchergui", "ilnotegui"),
                "",
                "",
                true,
                false
            );
            
            ilNoteGUI::initJavascript($notesUrl, IL_NOTE_PUBLIC, $DIC->ui()->mainTemplate());
        }
    }
    
    /**
     * @return bool
     */
    public function isQuestionCommentingEnabled() : bool
    {
        return $this->questionCommentingEnabled;
    }
    
    /**
     * @param bool $questionCommentingEnabled
     */
    public function setQuestionCommentingEnabled(bool $questionCommentingEnabled)
    {
        $this->questionCommentingEnabled = $questionCommentingEnabled;
    }
    
    protected function isCommentsColumnSelected()
    {
        return in_array('comments', $this->getSelectedColumns());
    }
    
    public function setQuestionData($questionData)
    {
        if ($this->isQuestionCommentingEnabled() && ($this->isCommentsColumnSelected() || $this->filter['commented'])) {
            foreach ($questionData as $key => $data) {
                $numComments = count(ilNote::_getNotesOfObject(
                    $this->parent_obj->object->getId(),
                    $data['question_id'],
                    'quest',
                    IL_NOTE_PUBLIC
                ));
                
                if ($this->filter['commented'] && !$numComments) {
                    unset($questionData[$key]);
                    continue;
                }
                
                $questionData[$key]['comments'] = $numComments;
            }
        }
        
        $this->setData($questionData);
    }

    public function getSelectableColumns()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $cols["description"] = array(
            "txt" => $lng->txt("description"),
            "default" => true
        );
        $cols["type"] = array(
            "txt" => $lng->txt("question_type"),
            "default" => true
        );
        if (!$this->confirmdelete) {
            $cols["points"] = array(
                "txt" => $lng->txt("points"),
                "default" => true
            );
            $cols["statistics"] = array(
                "txt" => $lng->txt("statistics"),
                "default" => true
            );
            $cols["author"] = array(
                "txt" => $lng->txt("author"),
                "default" => true
            );
            if ($this->isQuestionCommentingEnabled()) {
                $cols["comments"] = array(
                    "txt" => $lng->txt("comments"),
                    "default" => true
                );
            }
            $cols["created"] = array(
                "txt" => $lng->txt("create_date"),
                "default" => true
            );
            $cols["tstamp"] = array(
                "txt" => $lng->txt("last_update"),
                "default" => true
            );
            $cols["working_time"] = array(
                "txt" => $lng->txt("working_time"),
                "default" => true
            );
        }
        return $cols;
    }

    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        
        // title
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $ti->setSize(20);
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
        
        if (!$this->confirmdelete) {
            // author
            $ti = new ilTextInputGUI($lng->txt("author"), "author");
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $ti->setValidationRegexp('/^[^%]+$/is');
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["author"] = $ti->getValue();
        }
        // questiontype
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
        $types = ilObjQuestionPool::_getQuestionTypes();
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
        
        if ($this->parent_obj->object->getShowTaxonomies()) {
            require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';

            foreach ($this->taxIds as $taxId) {
                if ($taxId == $this->parent_obj->object->getNavTaxonomyId()) {
                    continue;
                }
                
                $postvar = "tax_$taxId";

                $inp = new ilTaxSelectInputGUI($taxId, $postvar, true);
                $this->addFilterItem($inp);
                $inp->readFromSession();
                $this->filter[$postvar] = $inp->getValue();
            }
        }
        
        // comments
        if ($this->isQuestionCommentingEnabled()) {
            $comments = new ilCheckboxInputGUI($lng->txt('ass_commented_questions_only'), 'commented');
            $this->addFilterItem($comments);
            $comments->readFromSession();
            $this->filter['commented'] = $comments->getChecked();
        }
    }
    
    public function fillHeader()
    {
        foreach ($this->column as $key => $column) {
            if (strcmp($column['text'], $this->lng->txt("points")) == 0) {
                $this->column[$key]['text'] = $this->lng->txt("points") . "&nbsp;(" . $this->totalPoints . ")";
            } elseif (strcmp($column['text'], $this->lng->txt("working_time")) == 0) {
                $this->column[$key]['text'] = $this->lng->txt("working_time") . "&nbsp;(" . $this->totalWorkingTime . ")";
            }
        }
        parent::fillHeader();
    }
    
    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
        $class = strtolower(assQuestionGUI::_getGUIClassNameForId($data["question_id"]));
        $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $data["question_id"]);
        $this->ctrl->setParameterByClass("ilAssQuestionPreviewGUI", "q_id", $data["question_id"]);
        $this->ctrl->setParameterByClass($class, "q_id", $data["question_id"]);
        $points = 0;

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setId('qst' . $data["question_id"]);
        $actions->setListTitle($this->lng->txt('actions'));

        if (!$this->confirmdelete) {
            $this->tpl->setCurrentBlock('checkbox');
            $this->tpl->setVariable('CB_QUESTION_ID', $data["question_id"]);
            $this->tpl->parseCurrentBlock();

            if ($data["complete"] == 0) {
                $this->tpl->setCurrentBlock("qpl_warning");
                $this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("icon_alert.svg"));
                $this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
                $this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
                $this->tpl->parseCurrentBlock();
            } else {
                $points = $data["points"];
                $this->totalWorkingTime = assQuestion::sumTimesInISO8601FormatH_i_s_Extended($this->totalWorkingTime, $data['working_time']);
            }
            $this->totalPoints += $points;

            foreach ($this->getSelectedColumns() as $c) {
                if (strcmp($c, 'points') == 0) {
                    $this->tpl->setCurrentBlock('points');
                    $this->tpl->setVariable("QUESTION_POINTS", $points);
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'statistics') == 0) {
                    $this->tpl->setCurrentBlock('statistics');
                    $this->tpl->setVariable("LINK_ASSESSMENT", $this->ctrl->getLinkTargetByClass($class, "assessment"));
                    $this->tpl->setVariable("TXT_ASSESSMENT", $this->lng->txt("statistics"));
                    include_once "./Services/Utilities/classes/class.ilUtil.php";
                    $this->tpl->setVariable("IMG_ASSESSMENT", ilUtil::getImagePath("assessment.gif", "Modules/TestQuestionPool"));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'author') == 0) {
                    $this->tpl->setCurrentBlock('author');
                    $this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
                    $this->tpl->parseCurrentBlock();
                }
                if ($c == 'comments' && $this->isQuestionCommentingEnabled()) {
                    $this->tpl->setCurrentBlock('comments');
                    $this->tpl->setVariable("COMMENTS", $this->getCommentsHtml($data));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'created') == 0) {
                    $this->tpl->setCurrentBlock('created');
                    $this->tpl->setVariable('QUESTION_CREATED', ilDatePresentation::formatDate(new ilDateTime($data['created'], IL_CAL_UNIX)));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'tstamp') == 0) {
                    $this->tpl->setCurrentBlock('updated');
                    $this->tpl->setVariable('QUESTION_UPDATED', ilDatePresentation::formatDate(new ilDateTime($data['tstamp'], IL_CAL_UNIX)));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'working_time') == 0) {
                    $this->tpl->setCurrentBlock('working_time');
                    $this->tpl->setVariable('WORKING_TIME', $data["working_time"]);
                    $this->tpl->parseCurrentBlock();
                }
            }

            $actions->addItem($this->lng->txt('preview'), '', $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW));
            if ($this->getEditable()) {
                $editHref = $this->ctrl->getLinkTargetByClass($data['type_tag'] . 'GUI', 'editQuestion');
                $actions->addItem($this->lng->txt('edit_question'), '', $editHref);

                $editPageHref = $this->ctrl->getLinkTargetByClass('ilAssQuestionPageGUI', 'edit');
                $actions->addItem($this->lng->txt('edit_page'), '', $editPageHref);
            }

            if ($this->getWriteAccess()) {
                $this->ctrl->setParameter($this->parent_obj, 'q_id', $data['question_id']);
                $moveHref = $this->ctrl->getLinkTarget($this->parent_obj, 'move');
                $this->ctrl->setParameter($this->parent_obj, 'q_id', null);
                $actions->addItem($this->lng->txt('move'), '', $moveHref);

                $this->ctrl->setParameter($this->parent_obj, 'q_id', $data['question_id']);
                $copyHref = $this->ctrl->getLinkTarget($this->parent_obj, 'copy');
                $this->ctrl->setParameter($this->parent_obj, 'q_id', null);
                $actions->addItem($this->lng->txt('copy'), '', $copyHref);

                $this->ctrl->setParameter($this->parent_obj, 'q_id', $data['question_id']);
                $deleteHref = $this->ctrl->getLinkTarget($this->parent_obj, 'deleteQuestions');
                $this->ctrl->setParameter($this->parent_obj, 'q_id', null);
                $actions->addItem($this->lng->txt('delete'), '', $deleteHref);
            }

            if ($this->getEditable()) {
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
                $this->ctrl->setParameterByClass('ilAssQuestionFeedbackEditingGUI', 'q_id', $data['question_id']);
                $feedbackHref = $this->ctrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
                $this->ctrl->setParameterByClass('ilAssQuestionFeedbackEditingGUI', 'q_id', null);
                $actions->addItem($this->lng->txt('tst_feedback'), '', $feedbackHref);

                $this->ctrl->setParameterByClass('ilAssQuestionHintsGUI', 'q_id', $data['question_id']);
                $hintsHref =  $this->ctrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
                $this->ctrl->setParameterByClass('ilAssQuestionHintsGUI', 'q_id', null);
                $actions->addItem($this->lng->txt('tst_question_hints_tab'), '', $hintsHref);
            }
            
            if ($this->isQuestionCommentingEnabled()) {
                $actions->addItem(
                    $this->lng->txt('ass_comments'),
                    'comments',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $this->getCommentsAjaxLink($data['question_id'])
                );
            }
        } else {
            $this->tpl->setCurrentBlock('hidden');
            $this->tpl->setVariable('HIDDEN_QUESTION_ID', $data["question_id"]);
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'description') == 0) {
                $this->tpl->setCurrentBlock('description');
                $this->tpl->setVariable("QUESTION_COMMENT", (strlen($data["description"])) ? $data["description"] : "&nbsp;");
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'type') == 0) {
                $this->tpl->setCurrentBlock('type');
                $this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
                $this->tpl->parseCurrentBlock();
            }
        }
        $this->tpl->setVariable('QUESTION_ID', $data["question_id"]);
        if (!$this->confirmdelete) {
            $this->tpl->setVariable('QUESTION_HREF_LINKED', $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW));
            $this->tpl->setVariable('QUESTION_TITLE_LINKED', $data['title']);
            $this->tpl->setVariable('ACTIONS', $actions->getHTML());
        } else {
            $this->tpl->setVariable('QUESTION_ID_UNLINKED', $data['question_id']);
            $this->tpl->setVariable('QUESTION_TITLE_UNLINKED', $data['title']);
        }
    }
    
    public function setEditable($value)
    {
        $this->editable = $value;
    }
    
    public function getEditable()
    {
        return $this->editable;
    }

    public function setWriteAccess($value)
    {
        $this->writeAccess = $value;
    }
    
    public function getWriteAccess()
    {
        return $this->writeAccess;
    }

    /**
     * @param string $column
     * @return bool
     */
    public function numericOrdering($column)
    {
        if (in_array($column, array('points', 'created', 'tstamp', 'comments'))) {
            return true;
        }

        return false;
    }
    
    protected function getCommentsHtml($qData)
    {
        if (!$qData['comments']) {
            return '';
        }
        
        $ajaxLink = $this->getCommentsAjaxLink($qData['question_id']);
        
        return "<a class='comment' href='#' onclick=\"return " . $ajaxLink . "\">
                        <img src='" . ilUtil::getImagePath("comment_unlabeled.svg")
            . "' alt='{$qData['comments']}'><span class='ilHActProp'>{$qData['comments']}</span></a>";
    }
    
    protected function getCommentsAjaxLink($questionId)
    {
        $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(1, $_GET['ref_id'], 'quest', $this->parent_obj->object->getId(), 'quest', $questionId);
        return ilNoteGUI::getListCommentsJSCall($ajax_hash, '');
    }
}
