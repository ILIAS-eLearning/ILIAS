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

use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Question\Question;

class QstsQuestionPage extends ilPageObject
{
    private readonly Edit $edit_view;
    private readonly Question $question;
    private ?Attempt $attempt_data = null;
    private ?Viewable $participant_view = null;
    private ?RequiredCapabilities $required_capabilites = null;
    private bool $interactive = true;
    private bool $show_best_response = true;
    private bool $show_feedback = false;

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

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(
        Question $question
    ): void {
        $this->question = $question;
    }

    public function getParticipantView(): Viewable
    {
        return $this->participant_view;
    }

    public function setParticipantView(
        Viewable $participant_view
    ): void {
        $this->participant_view = $participant_view;
    }

    public function getAttemptData(): ?Attempt
    {
        return $this->attempt_data;
    }

    public function setAttemptData(
        ?Attempt $attempt_data
    ): void {
        $this->attempt_data = $attempt_data;
    }

    public function getRequiredCapabilities(): ?RequiredCapabilities
    {
        return $this->required_capabilites;
    }

    public function setRequiredCapabilities(
        RequiredCapabilities $required_capabilities
    ): void {
        $this->required_capabilites = $required_capabilities;
    }

    public function getInteractive(): bool
    {
        return $this->interactive;
    }

    public function setInteractive(
        bool $interactive
    ): void {
        $this->interactive = $interactive;
    }

    public function getShowBestResponse(): bool
    {
        return $this->show_best_response;
    }

    public function setShowBestResponse(
        bool $show_best_response
    ): void {
        $this->show_best_response = $show_best_response;
    }

    public function getShowFeedback(): bool
    {
        return $this->show_feedback;
    }

    public function setShowFeedback(
        bool $show_feedback
    ): void {
        $this->show_feedback = $show_feedback;
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
