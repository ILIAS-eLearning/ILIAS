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
 * Class ilAssLongmenuCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssLongmenuCorrectionsInputGUI extends ilAnswerWizardInputGUI
{
    public function checkInput(): bool
    {
        return true;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate('tst.longmenu_corrections_input.html', true, true, 'Modules/TestQuestionPool');

        $tpl->setVariable('ANSWERS_MODAL', $this->buildAnswersModal()->getHTML());

        $tpl->setVariable('TAG_INPUT', $this->buildTagInput()->render());

        $tpl->setVariable('NUM_ANSWERS', $this->values['answers_all_count']);

        $tpl->setVariable('TXT_ANSWERS', $this->lng->txt('answer_options'));
        $tpl->setVariable('TXT_SHOW', $this->lng->txt('show'));
        $tpl->setVariable('TXT_CORRECT_ANSWERS', $this->lng->txt('correct_answers'));

        $tpl->setVariable('POSTVAR', $this->getPostVar());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    protected function buildAnswersModal(): ilModalGUI
    {
        $closeButton = ilJsLinkButton::getInstance();
        $closeButton->setCaption('close');
        $closeButton->setOnClick("$('#modal_{$this->getPostVar()}').modal('hide');");

        $modal = ilModalGUI::getInstance();
        $modal->setId('modal_' . $this->getPostVar());
        $modal->setHeading($this->lng->txt('answer_options'));
        $modal->addButton($closeButton);

        $inp = new ilTextWizardInputGUI('', '');
        $inp->setValues(current($this->values['answers_all']));
        $inp->setDisabled(true);

        $modal->setBody($inp->render());

        return $modal;
    }

    protected function buildTagInput(): ilTagInputGUI
    {
        $tagInput = new ilTagInputGUI('', $this->getPostVar() . '_tags');
        $tagInput->setTypeAhead(true);
        $tagInput->setTypeAheadMinLength(1);

        $tagInput->setOptions($this->values['answers_correct']);
        $tagInput->setTypeAheadList($this->values['answers_all']);

        return $tagInput;
    }
}
