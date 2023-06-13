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

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @author Björn Heyser <bheyser@databay.de>
* @version $Id$
*
* @ingroup ModulesTest
*/
class ilTestQuestionsTableGUI extends ilTable2GUI
{
    private const CLASS_PATH_FOR_QUESTION_EDIT_LINKS = [ilRepositoryGUI::class, ilObjQuestionPoolGUI::class];

    /**
     * @var bool
     */
    protected $questionRemoveRowButtonEnabled = false;

    /**
     * @var bool
     */
    protected $questionManagingEnabled = false;

    /**
     * @var bool
     */
    protected $positionInsertCommandsEnabled = false;

    /**
     * @var bool
     */
    protected $questionPositioningEnabled = false;

    /**
     * @var bool
     */
    protected $obligatoryQuestionsHandlingEnabled = false;

    protected float $totalPoints = 0;
    protected string $totalWorkingTime = '';
    private int $position = 0;
    private int $parent_ref_id;

    protected Renderer $renderer;
    protected Factory $factory;

    public function __construct($a_parent_obj, $a_parent_cmd, $parentRefId)
    {
        global $DIC;

        $this->renderer = $DIC->ui()->renderer();
        $this->factory = $DIC->ui()->factory();

        $this->setId('tst_qst_lst_' . $parentRefId);

        $this->parent_ref_id = (int) $parentRefId;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormName('questionbrowser');
        $this->setStyle('table', 'fullwidth');

        $this->setExternalSegmentation(true);

        $this->setRowTemplate("tpl.il_as_tst_questions_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->disable('sort');
        $this->enable('header');

        $this->setShowRowsSelector(false);
    }

    public function getSelectableColumns(): array
    {
        $cols = array(
            'qid' => array('txt' => $this->lng->txt('question_id'), 'default' => true),
            'description' => array('txt' => $this->lng->txt('description'), 'default' => false),
            'author' => array('txt' => $this->lng->txt('author'), 'default' => false),
            'lifecycle' => array('txt' => $this->lng->txt('qst_lifecycle'), 'default' => true)
        );

        return $cols;
    }

    public function init(): void
    {
        $this->initColumns();
        $this->initCommands();

        if ($this->isQuestionManagingEnabled()) {
            $this->setSelectAllCheckbox('q_id');
        }
    }

    protected function initColumns(): void
    {
        if ($this->isCheckboxColumnRequired()) {
            $this->addColumn('', 'f', '1%', true);
        }

        if ($this->isQuestionPositioningEnabled()) {
            $this->addColumn($this->lng->txt('order'), 'f', '1%');
        }

        if ($this->isColumnSelected('qid')) {
            $this->addColumn($this->lng->txt('question_id'), 'qid', '');
        }

        $this->addColumn($this->lng->txt("tst_question_title"), 'title', '');

        if ($this->isObligatoryQuestionsHandlingEnabled()) {
            $this->addColumn($this->lng->txt("obligatory"), 'obligatory', '');
        }

        if ($this->isColumnSelected('description')) {
            $this->addColumn($this->lng->txt('description'), 'description', '');
        }

        $this->addColumn($this->lng->txt("tst_question_type"), 'type', '');
        $this->addColumn($this->buildPointsHeader(), '', '');

        if ($this->isColumnSelected('author')) {
            $this->addColumn($this->lng->txt('author'), 'author', '');
        }
        if ($this->isColumnSelected('lifecycle')) {
            $this->addColumn($this->lng->txt('qst_lifecycle'), 'lifecycle', '');
        }

        $this->addColumn($this->lng->txt('qpl'), 'qpl', '');

        $this->addColumn($this->lng->txt('actions'), '', '1%');
    }

    protected function initCommands(): void
    {
        if ($this->isQuestionManagingEnabled()) {
            $this->addMultiCommand('removeQuestions', $this->lng->txt('remove_question'));
            $this->addMultiCommand('moveQuestions', $this->lng->txt('move'));
        }

        if ($this->isPositionInsertCommandsEnabled()) {
            $this->addMultiCommand('insertQuestionsBefore', $this->lng->txt('insert_before'));
            $this->addMultiCommand('insertQuestionsAfter', $this->lng->txt('insert_after'));
        }

        if ($this->isQuestionManagingEnabled()) {
            $this->addMultiCommand('copyQuestion', $this->lng->txt('copy'));
            $this->addMultiCommand('copyAndLinkToQuestionpool', $this->lng->txt('copy_and_link_to_questionpool'));
        }

        if ($this->isTableSaveCommandRequired()) {
            $this->addCommandButton('saveOrderAndObligations', $this->buildTableSaveCommandLabel());
        }
    }

    public function fillRow(array $a_set): void
    {
        if ($this->isCheckboxColumnRequired()) {
            $this->tpl->setVariable("CHECKBOX_QID", $a_set['question_id']);
        }

        if ($this->isQuestionPositioningEnabled()) {
            $this->position += 10;
            $inputField = $this->buildPositionInput($a_set['question_id'], $this->position);

            $this->tpl->setVariable("QUESTION_POSITION", $inputField);
            $this->tpl->setVariable("POSITION_QID", $a_set['question_id']);
        }

        if ($this->isColumnSelected('qid')) {
            $this->tpl->setVariable("QUESTION_ID_PRESENTATION", $a_set['question_id']);
        }

        $this->tpl->setVariable("QUESTION_TITLE", $this->buildQuestionTitleLink($a_set));

        if (!$a_set['complete']) {
            $this->tpl->setVariable("QUESTION_INCOMPLETE_WARNING", $this->lng->txt("warning_question_not_complete"));
        }

        if ($this->isObligatoryQuestionsHandlingEnabled()) {
            $this->tpl->setVariable("QUESTION_OBLIGATORY", $this->buildObligatoryColumnContent($a_set));
        }

        if ($this->isColumnSelected('description')) {
            $this->tpl->setVariable("QUESTION_COMMENT", $a_set["description"] ? $a_set["description"] : '&nbsp;');
        }

        $this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($a_set["type_tag"]));
        $this->tpl->setVariable("QUESTION_POINTS", $a_set["points"]);

        if ($this->isColumnSelected('author')) {
            $this->tpl->setVariable("QUESTION_AUTHOR", $a_set["author"]);
        }

        if ($this->isColumnSelected('lifecycle')) {
            try {
                $lifecycle = ilAssQuestionLifecycle::getInstance($a_set['lifecycle'])->getTranslation($this->lng);
                $this->tpl->setVariable("QUESTION_LIFECYCLE", $lifecycle);
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->tpl->setVariable("QUESTION_LIFECYCLE", '');
            }
        }

        if (ilObject::_lookupType((int) $a_set["orig_obj_fi"]) == 'qpl') {
            $this->tpl->setVariable("QUESTION_POOL", ilObject::_lookupTitle($a_set["orig_obj_fi"]));
        } else {
            $this->tpl->setVariable("QUESTION_POOL", $this->lng->txt('tst_question_not_from_pool_info'));
        }
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setId('qst' . $a_set["question_id"]);
        $actions->setListTitle($this->lng->txt('actions'));

        $actions->addItem(
            $this->lng->txt('preview'),
            '',
            $this->getPreviewLink($a_set)
        );

        $actions->addItem(
            $this->lng->txt('statistics'),
            '',
            $this->getQuestionEditLink($a_set, 'ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_STATISTICS)
        );

        if ($this->isQuestionManagingEnabled()) {
            $editHref = $this->getQuestionEditLink($a_set, $a_set['type_tag'] . 'GUI', 'editQuestion');
            $actions->addItem($this->lng->txt('edit_question'), '', $editHref);

            $editPageHref = $this->getQuestionEditLink($a_set, 'ilAssQuestionPageGUI', 'edit');
            $actions->addItem($this->lng->txt('edit_page'), '', $editPageHref);

            $moveHref = $this->getEditLink($a_set, get_class($this->getParentObject()), 'moveQuestions');
            $actions->addItem($this->lng->txt('move'), '', $moveHref);

            $copyHref = $this->getEditLink($a_set, get_class($this->getParentObject()), 'copyQuestion');
            $actions->addItem($this->lng->txt('copy'), '', $copyHref);

            $deleteHref = $this->getEditLink($a_set, get_class($this->getParentObject()), 'removeQuestions');
            $actions->addItem($this->lng->txt('delete'), '', $deleteHref);

            $feedbackHref = $this->getQuestionEditLink($a_set, 'ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
            $actions->addItem($this->lng->txt('tst_feedback'), '', $feedbackHref);

            $hintsHref = $this->getQuestionEditLink($a_set, 'ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
            $actions->addItem($this->lng->txt('tst_question_hints_tab'), '', $hintsHref);
        }
        $this->tpl->setVariable('ROW_ACTIONS', $actions->getHTML());
        if ($this->isQuestionRemoveRowButtonEnabled()) {
            $this->tpl->setVariable('ROW_ACTIONS', $this->buildQuestionRemoveButton($a_set));
        }
    }

    protected function buildQuestionRemoveButton(array $rowData): string
    {
        $this->ctrl->setParameter($this->getParentObject(), 'removeQid', $rowData['question_id']);
        $removeUrl = $this->ctrl->getLinkTarget($this->getParentObject(), $this->getParentCmd());
        $this->ctrl->setParameter($this->getParentObject(), 'removeQid', '');

        $button = ilLinkButton::getInstance();
        $button->setCaption('remove_question');
        $button->setUrl($removeUrl);

        return $button->render();
    }

    protected function buildQuestionTitleLink(array $rowData): string
    {
        return '<a href="' . $this->getPreviewLink($rowData) . '">' . $rowData["title"] . '</a>';
    }

    protected function getPreviewLink(array $rowData): string
    {
        $target_class = get_class($this->getParentObject());
        $this->ctrl->setParameterByClass(
            $target_class,
            'ref_id',
            current(ilObject::_getAllReferences($rowData['obj_fi']))
        );

        $this->ctrl->setParameterByClass(
            $target_class,
            'eqpl',
            current(ilObject::_getAllReferences($rowData['obj_fi']))
        );

        $this->ctrl->setParameterByClass(
            $target_class,
            'eqid',
            $rowData['question_id']
        );

        $this->ctrl->setParameterByClass(
            $target_class,
            'q_id',
            $rowData['question_id']
        );

        $this->ctrl->setParameterByClass(
            $target_class,
            'calling_test',
            (string) $this->parent_ref_id
        );

        $question_href = $this->ctrl->getLinkTargetByClass(
            $target_class,
            $this->getParentCmd()
        );
        $this->ctrl->setParameterByClass($target_class, 'eqpl', '');
        $this->ctrl->setParameterByClass($target_class, 'eqid', '');
        $this->ctrl->setParameterByClass($target_class, 'q_id', '');
        $this->ctrl->setParameterByClass($target_class, 'calling_test', '');

        return $question_href;
    }

    protected function getQuestionEditLink(array $rowData, string $target_class, string $cmd, array $target_class_path = []): string
    {
        $target_class_path = array_merge(self::CLASS_PATH_FOR_QUESTION_EDIT_LINKS, [$target_class]);
        return $this->getEditLink($rowData, $target_class, $cmd, $target_class_path);
    }

    protected function getEditLink(array $rowData, string $target_class, string $cmd, array $target_class_path = []): string
    {
        if ($target_class_path === []) {
            $target_class_path = $target_class;
        }
        $this->ctrl->setParameterByClass(
            $target_class,
            'ref_id',
            current(ilObject::_getAllReferences($rowData['obj_fi']))
        );

        $this->ctrl->setParameterByClass(
            $target_class,
            'q_id',
            $rowData['question_id']
        );
        $this->ctrl->setParameterByClass(
            $target_class,
            'calling_test',
            $_GET['ref_id']
        );

        $link = $this->ctrl->getLinkTargetByClass($target_class_path, $cmd);

        $this->ctrl->setParameterByClass($target_class, 'ref_id', '');
        $this->ctrl->setParameterByClass($target_class, 'q_id', '');
        $this->ctrl->setParameterByClass($target_class, 'calling_test', '');
        return $link;
    }

    protected function buildObligatoryColumnContent(array $rowData): string
    {
        if (!$rowData['obligationPossible']) {
            return '&nbsp;';
        }

        if ($rowData['obligatory'] && !$this->isQuestionManagingEnabled()) {
            return $this->renderer->render(
                $this->factory->symbol()->icon()->custom(
                    ilUtil::getImagePath('icon_alert.svg'),
                    $this->lng->txt('question_obligatory')
                )
            );
        }

        $checkedAttr = $rowData['obligatory'] ? 'checked="checked"' : '';
        return '<input type="checkbox" name="obligatory[' . $rowData['question_id'] . ']" value="1" ' . $checkedAttr . ' />';
    }

    protected function buildPositionInput($questionId, $position): string
    {
        return '<input type="text" name="order[q_' . $questionId . ']" value="' . $position . '" maxlength="4" size="4" />';
    }

    protected function buildTableSaveCommandLabel(): string
    {
        if ($this->isObligatoryQuestionsHandlingEnabled() && $this->isQuestionPositioningEnabled()) {
            return $this->lng->txt('saveOrderAndObligations');
        }

        if ($this->isObligatoryQuestionsHandlingEnabled()) {
            return $this->lng->txt('saveObligations');
        }

        if ($this->isQuestionPositioningEnabled()) {
            return $this->lng->txt('saveOrder');
        }

        return $this->lng->txt('save');
    }

    protected function buildPointsHeader(): string
    {
        if ($this->getTotalPoints()) {
            return $this->lng->txt('points') . ' (' . $this->getTotalPoints() . ')';
        }

        return $this->lng->txt('points');
    }

    protected function isTableSaveCommandRequired(): bool
    {
        if (!$this->isQuestionManagingEnabled()) {
            return false;
        }

        return $this->isQuestionPositioningEnabled() || $this->isObligatoryQuestionsHandlingEnabled();
    }

    protected function isCheckboxColumnRequired(): bool
    {
        return $this->isQuestionManagingEnabled() || $this->isPositionInsertCommandsEnabled();
    }

    public function isQuestionManagingEnabled(): bool
    {
        return $this->questionManagingEnabled;
    }

    public function setQuestionManagingEnabled(bool $questionManagingEnabled): void
    {
        $this->questionManagingEnabled = $questionManagingEnabled;
    }

    public function isPositionInsertCommandsEnabled(): bool
    {
        return $this->positionInsertCommandsEnabled;
    }

    public function setPositionInsertCommandsEnabled(bool $positionInsertCommandsEnabled): void
    {
        $this->positionInsertCommandsEnabled = $positionInsertCommandsEnabled;
    }

    public function isQuestionPositioningEnabled(): bool
    {
        return $this->questionPositioningEnabled;
    }

    public function setQuestionPositioningEnabled(bool $questionPositioningEnabled): void
    {
        $this->questionPositioningEnabled = $questionPositioningEnabled;
    }

    public function isObligatoryQuestionsHandlingEnabled(): bool
    {
        return $this->obligatoryQuestionsHandlingEnabled;
    }

    public function setObligatoryQuestionsHandlingEnabled(bool $obligatoryQuestionsHandlingEnabled): void
    {
        $this->obligatoryQuestionsHandlingEnabled = $obligatoryQuestionsHandlingEnabled;
    }

    public function getTotalPoints(): float
    {
        return $this->totalPoints;
    }

    public function setTotalPoints(float $totalPoints): void
    {
        $this->totalPoints = $totalPoints;
    }

    public function getTotalWorkingTime(): string
    {
        return $this->totalWorkingTime;
    }

    public function isQuestionRemoveRowButtonEnabled(): bool
    {
        return $this->questionRemoveRowButtonEnabled;
    }

    public function setQuestionRemoveRowButtonEnabled(bool $questionRemoveRowButtonEnabled): void
    {
        $this->questionRemoveRowButtonEnabled = $questionRemoveRowButtonEnabled;
    }
}
