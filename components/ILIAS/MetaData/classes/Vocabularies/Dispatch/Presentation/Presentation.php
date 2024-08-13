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

namespace ILIAS\MetaData\Vocabularies\Dispatch\Presentation;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepository;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepository;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class Presentation implements PresentationInterface
{
    protected CopyrightBridge $copyright;
    protected ControlledRepository $controlled;
    protected StandardRepository $standard;

    public function __construct(
        CopyrightBridge $copyright,
        ControlledRepository $controlled,
        StandardRepository $standard
    ) {
        $this->copyright = $copyright;
        $this->controlled = $controlled;
        $this->standard = $standard;
    }

    /**
     * @return LabelledValueInterface[]
     */
    public function presentableLabels(
        PresentationUtilities $presentation_utilities,
        SlotIdentifier $slot,
        bool $with_unknown_vocab_flag,
        string ...$values
    ): \Generator {
        $labelled_values = array_fill_keys($values, null);

        foreach ($this->copyright->labelsForValues($slot, ...$values) as $label) {
            if (!array_key_exists($label->value(), $labelled_values)) {
                continue;
            }
            $labelled_values[$label->value()] = $label;
        }

        foreach ($this->controlled->getLabelsForValues($slot, true, ...$values) as $label) {
            if (
                !array_key_exists($label->value(), $labelled_values) ||
                !is_null($labelled_values[$label->value()])
            ) {
                continue;
            }
            if ($label->label() === '') {
                $label = new LabelledValue($label->value(), $label->value());
            }
            $labelled_values[$label->value()] = $label;
        }

        foreach ($this->standard->getLabelsForValues(
            $presentation_utilities,
            $slot,
            true,
            ...$values
        ) as $label) {
            if (
                !array_key_exists($label->value(), $labelled_values) ||
                !is_null($labelled_values[$label->value()])
            ) {
                continue;
            }
            $labelled_values[$label->value()] = $label;
        }

        foreach ($labelled_values as $value => $labelled_value) {
            if (!is_null($labelled_value)) {
                yield $labelled_value;
                continue;
            }
            $label = (string) $value;
            if ($with_unknown_vocab_flag) {
                $label .= ' ' . $presentation_utilities->txt('md_unknown_vocabulary_flag');
            }
            yield new LabelledValue((string) $value, $label);
        }
    }

    /**
     * @return LabelledValueInterface[]
     */
    public function labelsForVocabulary(
        PresentationUtilities $presentation_utilities,
        VocabularyInterface $vocabulary
    ): \Generator {
        switch ($vocabulary->type()) {
            case Type::STANDARD:
                yield from $this->standard->getLabelsForValues(
                    $presentation_utilities,
                    $vocabulary->slot(),
                    false,
                    ...$vocabulary->values()
                );
                break;

            case Type::CONTROLLED_STRING:
            case Type::CONTROLLED_VOCAB_VALUE:
                yield from $this->controlled->getLabelsForValues(
                    $vocabulary->slot(),
                    false,
                    ...$vocabulary->values()
                );
                break;

            case Type::COPYRIGHT:
                yield from $this->copyright->labelsForValues(
                    $vocabulary->slot(),
                    ...$vocabulary->values()
                );
                break;

            default:
                yield from [];
                break;
        }
    }
}
