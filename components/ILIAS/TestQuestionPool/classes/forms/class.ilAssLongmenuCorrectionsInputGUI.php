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

use ILIAS\UI\Component\Modal\Lightbox;

/**
 * Class ilAssLongmenuCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilAssLongmenuCorrectionsInputGUI extends ilAnswerWizardInputGUI
{
    private \ILIAS\DI\UIServices $ui;

    private ?Lightbox $answer_options_modal = null;

    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;
        $this->ui = $DIC->ui();

        parent::__construct($a_title, $a_postvar);
    }

    public function checkInput(): bool
    {
        return true;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        // Get input
        $inp = new ilTextWizardInputGUI('', '');
        $inp->setValues(current($this->values['answers_all']));
        $inp->setDisabled(true);

        $this->answer_options_modal = $this->ui->factory()->modal()->lightbox(
            $this->ui->factory()->modal()->lightboxTextPage(
                $inp->render(),
                $this->lng->txt('answer_options')
            )
        );

        $tpl = new ilTemplate('tpl.prop_longmenu_corrections_input.html', true, true, 'components/ILIAS/TestQuestionPool');

        $tpl->setVariable('TAG_INPUT', $this->buildTagInput()->render());
        $tpl->setVariable('NUM_ANSWERS', $this->values['answers_all_count']);
        $tpl->setVariable(
            'BTN_SHOW',
            $this->ui->renderer()->render(
                $this->ui->factory()->button()->standard(
                    $this->lng->txt('show'),
                    $this->answer_options_modal->getShowSignal()
                )
            )
        );
        $tpl->setVariable('TXT_ANSWERS', $this->lng->txt('answer_options'));
        $tpl->setVariable('TXT_CORRECT_ANSWERS', $this->lng->txt('correct_answers') . ':');

        $tpl->setVariable('POSTVAR', $this->getPostVar());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    public function getContentOutsideFormTag(): string
    {
        return $this->answer_options_modal === null
            ? ''
            : $this->ui->renderer()->render($this->answer_options_modal);
    }

    protected function buildTagInput(): ilTagInputGUI
    {
        $tagInput = new ilTagInputGUI('', $this->getPostVar() . '_tags');
        $tagInput->setTypeAheadMinLength(1);

        $tagInput->setOptions($this->values['answers_correct']);
        $tagInput->setTypeAheadList($this->values['answers_all'][0]);

        return $tagInput;
    }
}
