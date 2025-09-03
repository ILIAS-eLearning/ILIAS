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

namespace ILIAS\Questions\Question;

use ILIAS\Questions\AnswerForm\Form;
use ILIAS\Questions\Question\Lifecycle;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\RequestInterface;

class QuestionImplementation implements Question
{
    /**
     * @param array{string, \ILIAS\Questions\AnswerForm\Form} $answer_forms
     */
    public function __construct(
        private ?Uuid $id = null,
        private ?int $page_id = null,
        private string $title = '',
        private string $author = '',
        private Lifecycle $lifecycle = Lifecycle::Draft,
        private string $remarks = '',
        private ?Uuid $original_id = null,
        private ?\DateTimeImmutable $last_update = null,
        private ?\DateTimeImmutable $created = null,
        private array $answer_forms = [],
        private ?Taxonomies $taxonomies = null,
        private ?ContentForRecapitulation $content_for_recapitulation = null
    ) {
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function withQuestionId(Uuid $question_id): self
    {
        $clone = clone $this;
        $clone->id = $question_id;
        return $clone;
    }

    public function getPageId(): ?int
    {
        return $this->page_id;
    }

    public function withPageId(int $page_id): self
    {
        $clone = clone $this;
        $clone->page_id = $page_id;
        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function withAuthor(string $author): self
    {
        $clone = clone $this;
        $clone->author = $author;
        return $clone;
    }

    public function getLifecycle(): Lifecycle
    {
        return $this->lifecycle;
    }

    public function withLifecycle(Lifecycle $lifecycle): self
    {
        $clone = clone $this;
        $clone->lifecycle = $lifecycle;
        return $clone;
    }

    public function getRemarks(): string
    {
        return $this->remarks;
    }

    public function withRemarks(string $remarks): self
    {
        $clone = clone $this;
        $clone->remarks = $remarks;
        return $clone;
    }

    public function getOriginalId(): ?Uuid
    {
        return $this->original_id;
    }

    public function withOriginalId(Uuid $original_id): self
    {
        $clone = clone $this;
        $clone->original_id = $original_id;
        return $clone;
    }

    public function getLastUpdate(): ?\DateTimeImmutable
    {
        return $this->last_update;
    }

    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    public function withCreated(\DateTimeImmutable $created): self
    {
        $clone = clone $this;
        $clone->created = $created;
        return $clone;
    }

    public function getAnswerForms(): array
    {
        return $this->answer_forms;
    }

    public function getAnswerForm(Uuid $form_id): ?Form
    {
        return $this->answer_forms[$form_id->toString()] ?? null;
    }

    public function withAnswerForm(Form $answer_form): self
    {
        $this->answer_forms[$answer_from->getId()->toString()] = $answer_form;
    }

    /**
     * Checks whether the question is a clone of another question or not
     */
    public function isClone(): bool
    {
        return $this->original_id !== null;
    }

    public function getEditView(
        Language $lng,
        \ilObjUser $current_user,
        UIFactory $ui_factory,
        Refinery $refinery,
        RequestInterface $request,
        \ilCtrl $ctrl,
        DataFactory $data_factory
    ): Views\Edit {
        return new Views\Edit($lng, $current_user, $ui_factory, $refinery, $request, $ctrl, $data_factory, $this);
    }

    public function getParticipantView(): Views\Participant
    {
        return new Views\Participant(
            new \QstsQuestionPageGUI($this),
            $this->answer_forms
        );
    }

    public function toEditLink(
        LinkFactory $link_factory,
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token
    ): StandardLink {
        return $link_factory->standard(
            $this->title,
            $url_builder->withParameter(
                $row_id_token,
                $this->id->toString()
            )->buildURI()->__toString()
        );
    }

    public function toTableRow(
        DataRowBuilder $row_builder,
        UIFactory $ui_factory,
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token
    ): DataRow {
        return $row_builder->buildDataRow(
            $this->id->toString(),
            [
                'title' => $ui_factory->link()->standard(
                    $this->title,
                    $url_builder->withParameter(
                        $row_id_token,
                        $this->id->toString()
                    )->buildURI()->__toString()
                )
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorage(): array
    {
        return [
            'id' => [\ilDBConstants::T_TEXT, $this->id->toString()],
            'page_id' => [\ilDBConstants::T_INTEGER, $this->page_id],
            'title' => [\ilDBConstants::T_TEXT, $this->title],
            'author' => [\ilDBConstants::T_TEXT, $this->author],
            'lifecycle' => [\ilDBConstants::T_TEXT, $this->lifecycle->value],
            'remarks' => [\ilDBConstants::T_TEXT, $this->remarks],
            'original_id' => [\ilDBConstants::T_TEXT, $this->original_id?->toString()],
            'last_update' => [\ilDBConstants::T_INTEGER, time()],
            'created' => [\ilDBConstants::T_INTEGER, $this->created?->getTimestamp() ?? time()]
        ];
    }

}
