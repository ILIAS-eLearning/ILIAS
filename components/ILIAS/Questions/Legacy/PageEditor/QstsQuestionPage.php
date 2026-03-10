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

use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Question\QuestionImplementation;

class QstsQuestionPage extends ilPageObject
{
    private readonly Edit $edit_view;
    private readonly QuestionImplementation $question;

    #[\Override]
    public function getParentType(): string
    {
        return 'qsts';
    }

    public function getEditView(): Edit
    {
        return $this->edit_view;
    }

    public function setEditView(
        Edit $edit_view
    ): void {
        $this->edit_view = $edit_view;
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

    public function addQuestionText(
        string $text
    ): void {
        $this->buildDom();

        $lng = $this->user->getLanguage();
        if ($lng === '') {
            $lng = 'de';
        }

        $page_element = new \ilPCParagraph($this);
        $page_element->create($this, 'pg');
        $page_element->setLanguage($lng);
        $page_element->setText($text);

        $this->update();
    }
}
