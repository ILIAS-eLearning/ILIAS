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
    private \ILIAS\TestQuestionPool\InternalRequestService $request;
    protected \ILIAS\Notes\Service $notes;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $renderer;
    protected $editable = true;
    protected $writeAccess = false;
    protected $totalPoints = 0;
    protected $totalWorkingTime = '00:00:00';
    protected $confirmdelete;

    protected $taxIds = array();

    /**
     * @var bool
     */
    protected $questionCommentingEnabled = false;

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
        $this->request = $DIC->testQuestionPool()->internal()->request();
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();

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
                if ($c == 'lifecycle') {
                    $this->addColumn($this->lng->txt('qst_lifecycle'), 'lifecycle', '');
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
        $this->notes = $DIC->notes();
        if ($this->isQuestionCommentingEnabled()) {
            $this->notes->gui()->initJavascript();
        }
    }

    /**
     * @return bool
     */
    public function isQuestionCommentingEnabled(): bool
    {
        return $this->questionCommentingEnabled;
    }

    /**
     * @param bool $questionCommentingEnabled
     */
    public function setQuestionCommentingEnabled(bool $questionCommentingEnabled): void
    {
        $this->questionCommentingEnabled = $questionCommentingEnabled;
    }

    protected function isCommentsColumnSelected(): bool
    {
        return in_array('comments', $this->getSelectedColumns());
    }

    public function setQuestionData($questionData): void
    {
        if ($this->isQuestionCommentingEnabled() && ($this->isCommentsColumnSelected() || $this->filter['commented'])) {
            foreach ($questionData as $key => $data) {
                $notes_context = $this->notes
                    ->data()
                    ->context(
                        $this->parent_obj->object->getId(),
                        $data['question_id'],
                        'quest'
                    );
                $numComments = $this->notes
                    ->domain()
                    ->getNrOfCommentsForContext($notes_context);

                if ($this->filter['commented'] && !$numComments) {
                    unset($questionData[$key]);
                    continue;
                }

                $questionData[$key]['comments'] = $numComments;
            }
        }

        $this->setData($questionData);
    }

    public function getSelectableColumns(): array
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
            $cols['lifecycle'] = array(
                'txt' => $lng->txt('qst_lifecycle'),
                'default' => true
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
    public function initFilter(): void
    {
        global $DIC;
        $lng = $DIC['lng'];

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

        // lifecycle
        $lifecycleOptions = array_merge(
            array('' => $this->lng->txt('qst_lifecycle_filter_all')),
            ilAssQuestionLifecycle::getDraftInstance()->getSelectOptions($this->lng)
        );
        $lifecycleInp = new ilSelectInputGUI($this->lng->txt('qst_lifecycle'), 'lifecycle');
        $lifecycleInp->setOptions($lifecycleOptions);
        $this->addFilterItem($lifecycleInp);
        $lifecycleInp->readFromSession();
        $this->filter['lifecycle'] = $lifecycleInp->getValue();

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

    public function fillHeader(): void
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
     * @access public
     * @param
     * @return void
     */
    public function fillRow(array $a_set): void
    {
        $class = strtolower(assQuestionGUI::_getGUIClassNameForId($a_set["question_id"]));
        $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $a_set["question_id"]);
        $this->ctrl->setParameterByClass("ilAssQuestionPreviewGUI", "q_id", $a_set["question_id"]);
        $this->ctrl->setParameterByClass($class, "q_id", $a_set["question_id"]);
        $points = 0;

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setId('qst' . $a_set["question_id"]);
        $actions->setListTitle($this->lng->txt('actions'));

        if (!$this->confirmdelete) {
            $this->tpl->setCurrentBlock('checkbox');
            $this->tpl->setVariable('CB_QUESTION_ID', $a_set["question_id"]);
            $this->tpl->parseCurrentBlock();

            if ($a_set["complete"] == 0) {
                $icon = $this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath("icon_alert.svg"), $this->lng->txt("warning_question_not_complete"));
                $this->tpl->setCurrentBlock("qpl_warning");
                $this->tpl->setVariable("ICON_WARNING", $this->renderer->render($icon));
                $this->tpl->parseCurrentBlock();
            } else {
                $points = $a_set["points"];
                $this->totalWorkingTime = assQuestion::sumTimesInISO8601FormatH_i_s_Extended($this->totalWorkingTime, $a_set['working_time']);
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
                    $this->tpl->setVariable("IMG_ASSESSMENT", ilUtil::getImagePath("assessment.gif", "Modules/TestQuestionPool"));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'author') == 0) {
                    $this->tpl->setCurrentBlock('author');
                    $this->tpl->setVariable("QUESTION_AUTHOR", $a_set["author"]);
                    $this->tpl->parseCurrentBlock();
                }
                if ($c == 'lifecycle') {
                    $lifecycle = ilAssQuestionLifecycle::getInstance($a_set['lifecycle']);

                    $this->tpl->setCurrentBlock('lifecycle');
                    $this->tpl->setVariable("QUESTION_LIFECYCLE", $lifecycle->getTranslation($this->lng));
                    $this->tpl->parseCurrentBlock();
                }
                if ($c == 'comments' && $this->isQuestionCommentingEnabled()) {
                    $this->tpl->setCurrentBlock('comments');
                    $this->tpl->setVariable("COMMENTS", $this->getCommentsHtml($a_set));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'created') == 0) {
                    $this->tpl->setCurrentBlock('created');
                    $this->tpl->setVariable('QUESTION_CREATED', ilDatePresentation::formatDate(new ilDateTime($a_set['created'], IL_CAL_UNIX)));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'tstamp') == 0) {
                    $this->tpl->setCurrentBlock('updated');
                    $this->tpl->setVariable('QUESTION_UPDATED', ilDatePresentation::formatDate(new ilDateTime($a_set['tstamp'], IL_CAL_UNIX)));
                    $this->tpl->parseCurrentBlock();
                }
                if (strcmp($c, 'working_time') == 0) {
                    $this->tpl->setCurrentBlock('working_time');
                    $this->tpl->setVariable('WORKING_TIME', $a_set["working_time"]);
                    $this->tpl->parseCurrentBlock();
                }
            }

            $actions->addItem(
                $this->lng->txt('preview'),
                '',
                $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW)
            );
            $actions->addItem(
                $this->lng->txt('statistics'),
                '',
                $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_STATISTICS)
            );
            if ($this->getEditable()) {
                $editHref = $this->ctrl->getLinkTargetByClass($a_set['type_tag'] . 'GUI', 'editQuestion');
                $actions->addItem($this->lng->txt('edit_question'), '', $editHref);

                $editPageHref = $this->ctrl->getLinkTargetByClass('ilAssQuestionPageGUI', 'edit');
                $actions->addItem($this->lng->txt('edit_page'), '', $editPageHref);
            }

            if ($this->getWriteAccess()) {
                $this->ctrl->setParameter($this->parent_obj, 'q_id', $a_set['question_id']);
                $moveHref = $this->ctrl->getLinkTarget($this->parent_obj, 'move');
                $this->ctrl->setParameter($this->parent_obj, 'q_id', null);
                $actions->addItem($this->lng->txt('move'), '', $moveHref);

                $this->ctrl->setParameter($this->parent_obj, 'q_id', $a_set['question_id']);
                $copyHref = $this->ctrl->getLinkTarget($this->parent_obj, 'copy');
                $this->ctrl->setParameter($this->parent_obj, 'q_id', null);
                $actions->addItem($this->lng->txt('copy'), '', $copyHref);

                $this->ctrl->setParameter($this->parent_obj, 'q_id', $a_set['question_id']);
                $deleteHref = $this->ctrl->getLinkTarget($this->parent_obj, 'deleteQuestions');
                $this->ctrl->setParameter($this->parent_obj, 'q_id', null);
                $actions->addItem($this->lng->txt('delete'), '', $deleteHref);
            }

            if ($this->getEditable()) {
                $this->ctrl->setParameterByClass('ilAssQuestionFeedbackEditingGUI', 'q_id', $a_set['question_id']);
                $feedbackHref = $this->ctrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
                $this->ctrl->setParameterByClass('ilAssQuestionFeedbackEditingGUI', 'q_id', null);
                $actions->addItem($this->lng->txt('tst_feedback'), '', $feedbackHref);

                $this->ctrl->setParameterByClass('ilAssQuestionHintsGUI', 'q_id', $a_set['question_id']);
                $hintsHref = $this->ctrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
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
                    $this->getCommentsAjaxLink($a_set['question_id'])
                );
            }
        } else {
            $this->tpl->setCurrentBlock('hidden');
            $this->tpl->setVariable('HIDDEN_QUESTION_ID', $a_set["question_id"]);
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'description') == 0) {
                $this->tpl->setCurrentBlock('description');
                $this->tpl->setVariable("QUESTION_COMMENT", (strlen($a_set["description"])) ? $a_set["description"] : "&nbsp;");
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'type') == 0) {
                $this->tpl->setCurrentBlock('type');
                $this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($a_set["type_tag"]));
                $this->tpl->parseCurrentBlock();
            }
        }
        $this->tpl->setVariable('QUESTION_ID', $a_set["question_id"]);
        if (!$this->confirmdelete) {
            $this->tpl->setVariable('QUESTION_HREF_LINKED', $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW));
            $this->tpl->setVariable('QUESTION_TITLE_LINKED', $a_set['title']);
            $this->tpl->setVariable('ACTIONS', $actions->getHTML());
        } else {
            $this->tpl->setVariable('QUESTION_ID_UNLINKED', $a_set['question_id']);
            $this->tpl->setVariable('QUESTION_TITLE_UNLINKED', $a_set['title']);
        }
    }

    public function setEditable($value): void
    {
        $this->editable = $value;
    }

    public function getEditable(): bool
    {
        return $this->editable;
    }

    public function setWriteAccess($value): void
    {
        $this->writeAccess = $value;
    }

    public function getWriteAccess(): bool
    {
        return $this->writeAccess;
    }

    /**
     * @param string $a_field
     * @return bool
     */
    public function numericOrdering(string $a_field): bool
    {
        if (in_array($a_field, array('points', 'created', 'tstamp', 'comments'))) {
            return true;
        }

        return false;
    }

    protected function getCommentsHtml($qData): string
    {
        if (!$qData['comments']) {
            return '';
        }

        $ajaxLink = $this->getCommentsAjaxLink($qData['question_id']);

        return "<a class='comment' href='#' onclick=\"return " . $ajaxLink . "\">
                        <img src='" . ilUtil::getImagePath("comment_unlabeled.svg")
            . "' alt='{$qData['comments']}'><span class='ilHActProp'>{$qData['comments']}</span></a>";
    }

    protected function getCommentsAjaxLink($questionId): string
    {
        $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(1, $this->request->getRefId(), 'quest', $this->parent_obj->object->getId(), 'quest', $questionId);
        return ilNoteGUI::getListCommentsJSCall($ajax_hash, '');
    }
}
