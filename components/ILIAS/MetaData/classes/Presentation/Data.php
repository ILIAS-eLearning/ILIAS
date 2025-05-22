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

namespace ILIAS\MetaData\Presentation;

use ILIAS\MetaData\Elements\Data\DataInterface as ElementsDataInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\DataHelper\DataHelperInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\PresentationInterface as VocabulariesPresentation;

class Data implements DataInterface
{
    protected UtilitiesInterface $utilities;
    protected DataHelperInterface $data_helper;
    protected VocabulariesPresentation $vocab_presentation;

    public function __construct(
        UtilitiesInterface $utilities,
        DataHelperInterface $data_helper,
        VocabulariesPresentation $vocab_presentation
    ) {
        $this->utilities = $utilities;
        $this->data_helper = $data_helper;
        $this->vocab_presentation = $vocab_presentation;
    }

    public function dataValue(ElementsDataInterface $data): string
    {
        switch ($data->type()) {
            case Type::VOCAB_VALUE:
            case Type::STRING:
                return $this->vocabularyValue($data->value(), $data->vocabularySlot());

            case Type::LANG:
                return $this->language($data->value());

            case Type::DATETIME:
                return $this->datetime($data->value());

            case Type::DURATION:
                return $this->duration($data->value());

            default:
                return $data->value();
        }
    }

    public function vocabularyValue(
        string $value,
        SlotIdentifier $vocabulary_slot
    ): string {
        return $this->vocab_presentation->presentableLabels(
            $this->utilities,
            $vocabulary_slot,
            false,
            $value
        )->current()->label();
    }

    public function language(string $language): string
    {
        return $this->utilities->txt('meta_l_' . $language);
    }

    public function datetime(string $datetime): string
    {
        $date = $this->data_helper->datetimeToObject($datetime);
        return $this->utilities->getUserDateFormat()->applyTo($date);
    }

    public function duration(string $duration): string
    {
        $labels = [
            ['years', 'year'],
            ['months', 'month'],
            ['days', 'day'],
            ['hours', 'hour'],
            ['minutes', 'minute'],
            ['seconds', 'second'],
        ];
        $res_array = [];
        foreach ($this->data_helper->durationToIterator($duration) as $key => $match) {
            if (!is_null($match)) {
                $res_array[] =
                    $match . ' ' .
                    ($match === '1' ?
                        $this->utilities->txt($labels[$key][1]) :
                        $this->utilities->txt($labels[$key][0]));
            }
        }
        return implode(', ', $res_array);
    }
}
