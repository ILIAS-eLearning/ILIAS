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
use ILIAS\Questions\Presentation\Definitions\ViewConfiguration;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Question\Question;
use ILIAS\Data\UUID\Factory as UuidFactory;

class QstsQuestionPage extends ilPageObject
{
    private UuidFactory $uuid_factory;

    private readonly Edit $edit_view;
    private Question $question;
    private ?Attempt $attempt_data = null;
    private ?Viewable $participant_view = null;
    private ?RequiredCapabilities $required_capabilites = null;
    private ?ViewConfiguration $view_configuration = null;

    private static array $answer_form_mapping = [];

    #[\Override]
    public function afterConstructor(): void
    {
        $this->uuid_factory = new UuidFactory();
    }

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

    public function getViewConfiguration(): ?ViewConfiguration
    {
        return $this->view_configuration;
    }

    public function setViewConfiguration(
        ViewConfiguration $view_configuration
    ): void {
        $this->view_configuration = $view_configuration;
    }

    #[\Override]
    public function pasteContents(
        string $page_content_id,
        bool $a_self_ass = false
    ): array|bool {
        $answer_form_element = $this->page_manager
            ->content($this->getDomDoc())
            ->getContentDomNode(...explode(':', $page_content_id))
            ?->getElementsByTagName(ilPCAnswerForm::ANSWER_FORM_ELEMENT_TAG);

        if ($answer_form_element?->length > 0) {
            foreach ($answer_form_element->getIterator() as $node) {
                self::$answer_form_mapping[$node->getAttribute(ilPCAnswerForm::ANSWER_FORM_ID_ATTRIBUTE)]
                    = $this->uuid_factory->uuid4AsString();
            }
        }

        return parent::pasteContents($page_content_id, $a_self_ass);
    }

    #[\Override]
    public function cutContents(
        array $page_content_ids
    ): array|bool {
        return parent::cutContents(
            array_filter(
                $page_content_ids,
                fn(string $v): bool => $this->page_manager
                    ->content($this->getDomDoc())
                    ->getContentDomNode(...explode(':', $v))
                    ?->getElementsByTagName('AnswerForm')
                    ?->length < 1
            )
        );
    }

    /**
     * 2026-06-30, sk:This is awfull, but as there are statics used in the copy
     * process this is the only way I found to do this. If somebody has a better
     * idea...
     *
     * @return array|null
     */
    public static function getAnswerFormMapping(): array
    {
        return self::$answer_form_mapping;
    }

    public static function setAnswerFormMapping(
        array $answer_form_mapping
    ): void {
        self::$answer_form_mapping = $answer_form_mapping;
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
