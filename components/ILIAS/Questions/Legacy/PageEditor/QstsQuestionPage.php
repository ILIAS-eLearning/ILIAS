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

use ILIAS\Questions\Question\QuestionImplementation;

class QstsQuestionPage extends ilPageObject
{
    private readonly QuestionImplementation $question;

    #[\Override]
    public function getParentType(): string
    {
        return 'qsts';
    }

    public function getQuestion(): QuestionImplementation
    {
        return $this->question;
    }

    public function setQuestion(
        QuestionImplementation $question
    ): void {
        $this->question = $question;
    }

    public function migrateQuestionElementToAnswerForm(): void
    {
        global $DIC;
        $dom_util = $DIC->copage()->internal()->domain()->domUtil();

        $answer_forms = $this->question->getAnswerForms();

        $answer_form_node = new ilPCAnswerForm($this);
        $answer_form_node->createPageContentNode();
        $answer_form_node->writePCId($this->generatePCId());
        $answer_form_node->create(
            array_shift($answer_forms)->getAnswerFormId()
        );

        $dom_util->path($this->getDomDoc(), '//Question')
            ->item(0)->parentNode->replaceWith($answer_form_node->getDomNode());

        $this->update();
    }
}
