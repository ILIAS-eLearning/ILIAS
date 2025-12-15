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

use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\Question\Persistence\Manipulate;
use ILIAS\Questions\Presentation\Layout\Definitions\EnvironmentImplementation;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\RequestInterface;

class QuestionImplementation implements Question
{
    public bool $self_updated = false;
    public array $updated_answer_forms = [];

    /**
     * @param array{string, \ILIAS\Questions\AnswerForm\Form} $answer_forms
     */
    public function __construct(
        private readonly Uuid $id,
        private ?int $page_id = null,
        private string $title = '',
        private string $author = '',
        private Lifecycle $lifecycle = Lifecycle::Draft,
        private string $remarks = '',
        private ?Uuid $original_id = null,
        private ?\DateTimeImmutable $last_update = null,
        private readonly ?\DateTimeImmutable $created = null,
        private array $answer_forms = [],
        private ?Taxonomies $taxonomies = null,
        private ?ContentForRecapitulation $content_for_recapitulation = null
    ) {
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPageId(): ?int
    {
        return $this->page_id;
    }

    public function withPageId(int $page_id): self
    {
        $clone = clone $this;
        $clone->page_id = $page_id;
        $clone->self_updated = true;
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
        $clone->self_updated = true;
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
        $clone->self_updated = true;
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
        $clone->self_updated = true;
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
        $clone->self_updated = true;
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
        $clone->self_updated = true;
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

    public function getAnswerForms(): array
    {
        return $this->answer_forms;
    }

    public function getAnswerFormByIdString(string $form_id): ?AnswerFormProperties
    {
        return $this->answer_forms[$form_id] ?? null;
    }

    public function withAnswerForm(AnswerFormProperties $answer_form): self
    {
        $clone = clone $this;
        $clone->answer_forms[$answer_form->getAnswerFormId()->toString()] = $answer_form;
        $clone->updated_answer_forms[] = $answer_form->getAnswerFormId();
        return $clone;
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
        EnvironmentImplementation $environment
    ): StandardLink {
        return $link_factory->standard(
            $this->title,
            $environment->withQuestionIdParameter($this->id)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );
    }

    public function toTableRow(
        DataRowBuilder $row_builder,
        UIFactory $ui_factory,
        EnvironmentImplementation $environment
    ): DataRow {
        return $row_builder->buildDataRow(
            $this->id->toString(),
            [
                'title' => $ui_factory->link()->standard(
                    $this->title,
                    $environment->withQuestionIdParameter(
                        $this->id
                    )->getUrlBuilder()
                    ->buildURI()
                    ->__toString()
                )
            ]
        );
    }

    public function toStorage(
        Manipulate $manipulate
    ): Manipulate {
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
