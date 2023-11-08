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

declare(strict_types=1);

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Modal\Interruptive;

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup components\ILIASTest
*/

class ilListOfQuestionsTableGUI extends ilTable2GUI
{
    private bool $user_has_attempts_left = true;
    /** @var Component[] $command_buttons */
    private array $command_buttons = [];
    private Interruptive $finish_test_modal;
    protected ?bool $showPointsEnabled = false;
    protected ?bool $showMarkerEnabled = false;

    protected ?bool $showObligationsEnabled = false;
    protected ?bool $obligationsFilterEnabled = false;

    protected ?bool $obligationsNotAnswered = false;

    protected ?bool $finishTestButtonEnabled = false;

    public function __construct(
        ilTestPlayerAbstractGUI $parent_obj,
        string $parent_cmd,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer
    ) {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormName('listofquestions');
        $this->setStyle('table', 'fullwidth');

        $this->setRowTemplate("tpl.il_as_tst_list_of_questions_row.html", "components/ILIAS/Test");

        $this->setLimit(999);

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));

        $this->enable('header');
        $this->disable('sort');
        $this->disable('select_all');
    }

    public function init(): void
    {
        // table title

        if ($this->isObligationsFilterEnabled()) {
            $this->setTitle($this->lng->txt('obligations_summary'));
        } else {
            $this->setTitle($this->lng->txt('question_summary'));
        }

        // columns

        $this->addColumn($this->lng->txt("tst_qst_order"), 'order', '');
        $this->addColumn($this->lng->txt("tst_question_title"), 'title', '');

        if ($this->isShowObligationsEnabled()) {
            $this->addColumn($this->lng->txt("obligatory"), 'obligatory', '');
        }

        $this->addColumn('', 'postponed', '');

        if ($this->isShowPointsEnabled()) {
            $this->addColumn($this->lng->txt("tst_maximum_points"), 'points', '');
        }

        #$this->addColumn($this->lng->txt("worked_through"),'worked_through', '');
        $this->addColumn($this->lng->txt("answered"), 'answered', '');

        if (false && $this->isShowObligationsEnabled()) {
            $this->addColumn($this->lng->txt("answered"), 'answered', '');
        }

        if ($this->isShowMarkerEnabled()) {
            $this->addColumn($this->lng->txt("tst_question_marker"), 'marked', '');
        }

        // command buttons
        $this->command_buttons[] = $this->ui_factory->button()->standard(
            $this->lng->txt('tst_resume_test'),
            $this->ctrl->getLinkTarget($this->parent_obj, ilTestPlayerCommands::SHOW_QUESTION)
        );

        if (!$this->areObligationsNotAnswered() && $this->isFinishTestButtonEnabled()) {
            $this->addFinishTestButton();
        }
    }

    public function userHasAttemptsLeft(): bool
    {
        return $this->user_has_attempts_left;
    }

    public function setUserHasAttemptsLeft(bool $user_has_attempts_left): void
    {
        $this->user_has_attempts_left = $user_has_attempts_left;
    }

    private function addFinishTestButton(): void
    {
        if ($this->userHasAttemptsLeft()) {
            $message = $this->lng->txt('tst_finish_confirmation_question');
        } else {
            $message = $this->lng->txt('tst_finish_confirmation_question_no_attempts_left');
        }
        $this->finish_test_modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('finish_test'),
            $message,
            $this->ctrl->getLinkTarget(
                $this->parent_obj,
                ilTestPlayerCommands::FINISH_TEST
            )
        )->withActionButtonLabel($this->lng->txt('tst_finish_confirm_button'));

        $this->command_buttons[] = $this->ui_factory->button()->standard($this->lng->txt('finish_test'), '')
                           ->withOnClick($this->finish_test_modal->getShowSignal());
    }

    public function getHTML(): string
    {
        foreach ($this->command_buttons as $top_item) {
            $this->tpl->setCurrentBlock('tbl_header_html');
            $this->tpl->setVariable(
                "HEADER_HTML",
                $this->ui_renderer->render($top_item)
            );
            $this->tpl->parseCurrentBlock();
        }

        $finish_test_modal = isset($this->finish_test_modal)
            ? $this->ui_renderer->render($this->finish_test_modal) : '';

        return parent::getHTML() . $finish_test_modal;
    }

    public function fillRow(array $a_set): void
    {
        if ($this->isShowPointsEnabled()) {
            $this->tpl->setCurrentBlock('points');
            $this->tpl->setVariable("POINTS", $a_set['points'] . '&nbsp;' . $this->lng->txt("points_short"));
            $this->tpl->parseCurrentBlock();
        }
        if (strlen($a_set['description'])) {
            $this->tpl->setCurrentBlock('description');
            $this->tpl->setVariable("DESCRIPTION", ilLegacyFormElementsUtil::prepareFormOutput($a_set['description']));
            $this->tpl->parseCurrentBlock();
        }
        if ($this->isShowMarkerEnabled()) {
            if ($a_set['marked']) {
                $this->tpl->setCurrentBlock('marked_img');
                $this->tpl->setVariable(
                    "HREF_MARKED",
                    ilUtil::img(
                        ilUtil::getImagePath('object/marked.svg'),
                        $this->lng->txt("tst_question_marked"),
                        '24px',
                        '24px'
                    )
                );
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock('marker');
            }
        }
        if ($this->isShowObligationsEnabled()) {
            // obligatory answer status
            if (false) {
                $value = '&nbsp;';
                if ($a_set['isAnswered']) {
                    $value = $this->lng->txt("yes");
                }
                $this->tpl->setCurrentBlock('answered_col');
                $this->tpl->setVariable('ANSWERED', $value);
                $this->tpl->parseCurrentBlock();
            }

            // obligatory icon
            if ($a_set["obligatory"]) {
                $obligatory = $this->ui_renderer->render(
                    $this->ui_factory->symbol()->icon()->custom(
                        ilUtil::getImagePath('standard/icon_alert.svg'),
                        $this->lng->txt('question_obligatory')
                    )
                );
            } else {
                $obligatory = '';
            }
            $this->tpl->setVariable("QUESTION_OBLIGATORY", $obligatory);
        }

        $postponed = (
            $a_set['postponed'] ? $this->lng->txt('postponed') : ''
        );

        if ($a_set['disabled']) {
            $this->tpl->setCurrentBlock('static_title');
            $this->tpl->setVariable("STATIC_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set['title']));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->ctrl->setParameter($this->parent_obj, 'sequence', $a_set['sequence']);
            $this->ctrl->setParameter($this->parent_obj, 'pmode', '');
            $href = $this->ctrl->getLinkTarget($this->parent_obj, ilTestPlayerCommands::SHOW_QUESTION);

            $this->tpl->setCurrentBlock('linked_title');
            $this->tpl->setVariable("LINKED_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set['title']));
            $this->tpl->setVariable("HREF", $href);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ORDER", $a_set['order']);
        $this->tpl->setVariable("POSTPONED", $postponed);
        if ($a_set["worked_through"]) {
            $this->tpl->setVariable("WORKED_THROUGH", $this->lng->txt("yes"));
        } else {
            $this->tpl->setVariable("WORKED_THROUGH", '&nbsp;');
        }
    }

    public function isShowPointsEnabled(): bool
    {
        return $this->showPointsEnabled;
    }

    public function setShowPointsEnabled($showPointsEnabled): void
    {
        $this->showPointsEnabled = $showPointsEnabled;
    }

    public function isShowMarkerEnabled(): bool
    {
        return $this->showMarkerEnabled;
    }

    public function setShowMarkerEnabled($showMarkerEnabled): void
    {
        $this->showMarkerEnabled = $showMarkerEnabled;
    }

    public function isShowObligationsEnabled(): bool
    {
        return $this->showObligationsEnabled;
    }

    public function setShowObligationsEnabled($showObligationsEnabled): void
    {
        $this->showObligationsEnabled = $showObligationsEnabled;
    }

    public function isObligationsFilterEnabled(): bool
    {
        return $this->obligationsFilterEnabled;
    }

    public function setObligationsFilterEnabled($obligationsFilterEnabled): void
    {
        $this->obligationsFilterEnabled = $obligationsFilterEnabled;
    }

    public function areObligationsNotAnswered(): bool
    {
        return $this->obligationsNotAnswered;
    }

    public function setObligationsNotAnswered($obligationsNotAnswered): void
    {
        $this->obligationsNotAnswered = $obligationsNotAnswered;
    }

    public function isFinishTestButtonEnabled(): bool
    {
        return $this->finishTestButtonEnabled;
    }

    public function setFinishTestButtonEnabled(bool $finishTestButtonEnabled): void
    {
        $this->finishTestButtonEnabled = $finishTestButtonEnabled;
    }
}
