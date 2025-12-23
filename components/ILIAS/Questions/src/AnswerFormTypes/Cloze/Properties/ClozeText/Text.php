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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Data\Text\Markdown;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Markdown as MarkdownInput;
use ILIAS\UI\Component\Input\Field\Hidden as HiddenInput;
use Mustache\Engine;

class Text
{
    public function __construct(
        private readonly Refinery $refinery,
        private readonly Engine $mustache_engine,
        private readonly TextFactory $text_factory,
        private Markdown $cloze_text
    ) {
    }

    public function getInput(
        Language $lng,
        FieldFactory $ff,
        Factory $cloze_text_factory
    ): MarkdownInput {
        return $ff->markdown(
            new \ilUIMarkdownPreviewGUI(),
            $lng->txt('cloze_text')
        )->withMustacheVariables([
            Gap::GAP_PLACEHOLDER_NAME => $lng->txt('gap')
        ])->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                fn(string $v): self => $cloze_text_factory->buildFromTextString($v)
            )
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                fn(self $v): bool => $v->hasAtLeastOneGap(),
                $lng->txt('no_gaps')
            )
        )->withRequired(true)
        ->withValue($this->cloze_text->getRawRepresentation());
    }

    public function getCarryInputs(FieldFactory $ff): HiddenInput
    {
        return $ff->hidden()->withValue($this->getTextForOutputInHiddenInput());
    }

    public function getRawRepresentationForPersistence(): string
    {
        return $this->cloze_text->getRawRepresentation();
    }

    public function getRenderedMarkdownForEditingPresentation(
        Gaps $gaps
    ): string {
        return $this->mustache_engine->render(
            $this->cloze_text->getRawRepresentation(),
            $gaps->getPlaceholderArrayForPreview()
        );
    }

    public function updateGapsFromMarkdown(
        Uuid $answer_form_id,
        Gaps $pre_existing_gaps
    ): Gaps {
        if ($this->cloze_text->getRawRepresentation() === '') {
            return $pre_existing_gaps->withResetGaps();
        }

        $position = 0;
        return array_reduce(
            $this->mustache_engine->getTokenizer()->scan($this->cloze_text->getRawRepresentation()),
            function (Gaps $c, array $v) use ($answer_form_id, &$position): Gaps {
                if ($v['type'] !== '_v'
                    || !str_starts_with($v['name'], Gap::GAP_PLACEHOLDER_NAME)) {
                    return $c;
                }

                if ($v['name'] === Gap::GAP_PLACEHOLDER_NAME) {
                    return $c->withNewGap($answer_form_id, $position++);
                }

                $gap = $c->getGapByTagName($v['name']);
                if ($gap !== null) {
                    return $c->withGap(
                        $gap->withProperties(
                            $gap->getProperties()->withPosition(
                                $answer_form_id,
                                $position++
                            )
                        )
                    );
                }

                return $c->withAdditionalGapFromTagName(
                    $answer_form_id,
                    $v['name'],
                    $position++
                );
            },
            $pre_existing_gaps
        );
    }

    public function withIdsOfNewGapsInClozeText(array $new_gaps): self
    {
        if ($new_gaps === []) {
            return self;
        }

        $clone = clone $this;
        $clone->cloze_text = $this->text_factory->markdown(
            mb_ereg_replace_callback(
                '{{' . Gap::GAP_PLACEHOLDER_NAME . '}}',
                function (array $matches) use (&$new_gaps): string {
                    return array_shift($new_gaps)->getGapPlaceholder();
                },
                $this->cloze_text->getRawRepresentation()
            )
        );
        return $clone;
    }

    private function hasAtLeastOneGap(): bool
    {
        if ($this->cloze_text->getRawRepresentation() === '') {
            return false;
        }

        foreach ($this->mustache_engine->getTokenizer()
            ->scan($this->cloze_text->getRawRepresentation()) as $token) {
            if ($token['type'] === '_v'
                && str_starts_with($token['name'], Gap::GAP_PLACEHOLDER_NAME)) {
                return true;
            }
        }

        return false;
    }

    private function getTextForOutputInHiddenInput(): string
    {
        return str_replace(
            ['{{', '}}'],
            ['\{\{', '\}\}'],
            $this->cloze_text->getRawRepresentation()
        );
    }
}
