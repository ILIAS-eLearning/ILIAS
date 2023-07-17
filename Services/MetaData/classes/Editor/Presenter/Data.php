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

namespace ILIAS\MetaData\Editor\Presenter;

use ILIAS\MetaData\Elements\Data\DataInterface as ElementsDataInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Presentation\DataInterface as DataPresentation;

class Data implements DataInterface
{
    protected UtilitiesInterface $utilities;
    protected DataPresentation $data_presentation;

    public function __construct(
        UtilitiesInterface $utilities,
        DataPresentation $data_presentation
    ) {
        $this->utilities = $utilities;
        $this->data_presentation = $data_presentation;
    }

    public function dataValue(ElementsDataInterface $data): string
    {
        return $this->data_presentation->dataValue($data);
    }

    public function vocabularyValue(string $value): string
    {
        return $this->data_presentation->vocabularyValue($value);
    }

    public function language(string $language): string
    {
        return $this->data_presentation->language($language);
    }

    public function datetime(string $datetime): string
    {
        return $this->data_presentation->datetime($datetime);
    }

    public function duration(string $duration): string
    {
        return $this->data_presentation->duration($duration);
    }

    /**
     * @return string[]
     */
    public function durationLabels(): \Generator
    {
        yield from [
            $this->utilities->txt('years'),
            $this->utilities->txt('months'),
            $this->utilities->txt('days'),
            $this->utilities->txt('hours'),
            $this->utilities->txt('minutes'),
            $this->utilities->txt('seconds')
        ];
    }
}
