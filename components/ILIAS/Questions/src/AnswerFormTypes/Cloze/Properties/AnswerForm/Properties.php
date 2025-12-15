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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\AnswerForm;

use ILIAS\Questions\AnswerForm\Properties as PropertiesInterface;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Text;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapsFactory;
use ILIAS\Questions\Presentation\Layout\Definitions\CarryWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;

class Properties implements PropertiesInterface
{
    private const string FORM_KEY_ID = 'form_id';
    private const string FORM_KEY_CLOZE_TEXT = 'cloze_text';
    private const string FORM_KEY_IDENTICAL_SCORING = 'identical_scoring';
    private const string FORM_KEY_ENABLE_COMBINATIONS = 'enable_combinations';
    private const string FORM_KEY_GAPS_TO_EDIT = 'gaps';

    /**
     * @param array<string, \ILIAS\Questions\AnswerFormTypes\Cloze\Gap> $gaps
     */
    public function __construct(
        private readonly Uuid $answer_form_id,
        private readonly Uuid $question_id,
        private Text $cloze_text,
        private readonly string $legacy_cloze_text,
        private Gaps $gaps,
        private ScoringIdentical $scoring_identical = ScoringIdentical::ScoreAll,
        private bool $combinations_enabled = false
    ) {
    }

    public function getAnswerFormId(): ?Uuid
    {
        return $this->answer_form_id;
    }

    public function getQuestionId(): Uuid
    {
        return $this->question_id;
    }

    public function getTypeGenericProperties(): TypeGenericProperties
    {
        return new TypeGenericProperties(
            $this->answer_form_id,
            $this->question_id,
            null,
            null,
            null,
            $this->cloze_text->getRawRepresentationForPersistence(),
            $this->legacy_cloze_text
        );
    }

    public function getClozeText(): Text
    {
        return $this->cloze_text;
    }

    public function withClozeText(Text $cloze_text): self
    {
        $clone = clone $this;
        $clone->cloze_text = $cloze_text;
        return $clone;
    }

    public function getLegacyClozeText(): string
    {
        return $this->legacy_cloze_text;
    }

    public function getScoringOfIdenticalResponses(): ScoringIdentical
    {
        return $this->scoring_identical;
    }

    public function withScoringOfIdenticalResponses(ScoringIdentical $scoring_identical): self
    {
        $clone = clone $this;
        $clone->scoring_identical = $scoring_identical;
        return $clone;
    }

    public function areCombinationsEnabled(): bool
    {
        return $this->combinations_enabled;
    }

    public function withCombinationsEnabled(bool $combinations_enabled): self
    {
        $clone = clone $this;
        $clone->combinations_enabled = $combinations_enabled;
        return $clone;
    }

    public function getGaps(): Gaps
    {
        return $this->gaps;
    }

    public function withGaps(Gaps $gaps): self
    {
        $clone = clone $this;
        $clone->gaps = $gaps;
        return $clone;
    }

    public function getBasicPropertiesForListing(Language $lng): array
    {
        return [
            $lng->txt('cloze_text') => $this->cloze_text
                ->getRenderedMarkdownForEditingPresentation($this->gaps),
            $lng->txt('score_identical') => $this->scoring_identical
                ->getTranslatedOptionName($lng)
        ];
    }

    public function buildBasicEditingInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        Factory $propteries_factory,
        ClozeTextFactory $cloze_text_factory
    ): Section {
        return $ff->section(
            [
                self::FORM_KEY_CLOZE_TEXT => $this->getClozeText()->getInput(
                    $lng,
                    $ff,
                    $cloze_text_factory
                ),
                self::FORM_KEY_IDENTICAL_SCORING => ScoringIdentical::buildInput(
                    $lng,
                    $ff,
                    $refinery,
                    $this->scoring_identical
                )->withValue($this->getScoringOfIdenticalResponses()->value),
                self::FORM_KEY_ENABLE_COMBINATIONS => $ff->checkbox($lng->txt('cloze_enable_combinations'))
                    ->withValue($this->areCombinationsEnabled())
            ],
            $lng->txt('create_answer_form')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(array $vs): self => $propteries_factory->fromForm(
                    $this,
                    $vs[self::FORM_KEY_CLOZE_TEXT],
                    $vs[self::FORM_KEY_IDENTICAL_SCORING],
                    $vs[self::FORM_KEY_ENABLE_COMBINATIONS]
                )
            )
        );
    }

    public function buildCarryInputs(
        FieldFactory $ff
    ): Group {
        return $ff->group(
            [
                self::FORM_KEY_ID => $ff->hidden()->withValue($this->answer_form_id->toString()),
                self::FORM_KEY_CLOZE_TEXT => $this->getClozeText()->getCarryInputs($ff)
                    ->withDedicatedName(self::FORM_KEY_CLOZE_TEXT),
                self::FORM_KEY_GAPS_TO_EDIT => $this->gaps->getCarryInputs($ff)
                    ->withDedicatedName(self::FORM_KEY_GAPS_TO_EDIT),
                self::FORM_KEY_IDENTICAL_SCORING => $ff->hidden()
                    ->withDedicatedName(self::FORM_KEY_IDENTICAL_SCORING)
                    ->withValue($this->getScoringOfIdenticalResponses()->value),
                self::FORM_KEY_ENABLE_COMBINATIONS => $ff->hidden()
                    ->withDedicatedName(self::FORM_KEY_ENABLE_COMBINATIONS)
                    ->withValue($this->areCombinationsEnabled() ? 1 : 0)
            ]
        );
    }

    public function withValuesFromCarry(
        Refinery $refinery,
        ClozeTextFactory $cloze_text_factory,
        GapsFactory $gaps_factory,
        CarryWrapper $carry
    ): Properties {
        $clone = clone $this;
        $clone->cloze_text = $carry->retrieve(
            self::FORM_KEY_CLOZE_TEXT,
            $refinery->byTrying([
                $refinery->custom()->transformation(
                    fn(?string $v): Text => $v === null
                        ? throw new \InvalidArgumentException()
                        : $cloze_text_factory->buildFromHiddenInputString($v)
                ),
                $refinery->always($clone->cloze_text)
            ])
        );

        $clone->scoring_identical = $carry->retrieve(
            self::FORM_KEY_IDENTICAL_SCORING,
            $refinery->byTrying([
                $refinery->custom()->transformation(
                    fn(?string $v): ScoringIdentical => $v === null
                        ? throw new \InvalidArgumentException()
                        : ScoringIdentical::tryFrom($v) ?? $clone->scoring_identical
                ),
                $refinery->always($clone->scoring_identical)
            ])
        );

        $clone->combinations_enabled = $carry->retrieve(
            self::FORM_KEY_ENABLE_COMBINATIONS,
            $refinery->byTrying([
                $refinery->kindlyTo()->bool(),
                $refinery->always($clone->combinations_enabled)
            ])
        );

        $clone->gaps = $carry->retrieve(
            self::FORM_KEY_GAPS_TO_EDIT,
            $clone->cloze_text->updateGapsFromMarkdown($this->getGaps())
                ->getFromCarryTransformation(
                    $refinery,
                    $gaps_factory
                )
        );

        return $clone;
    }
}
