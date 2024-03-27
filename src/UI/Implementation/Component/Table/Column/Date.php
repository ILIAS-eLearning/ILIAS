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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;
use ILIAS\Data\DateFormat\DateFormat;

class Date extends Column implements C\Date
{
    protected DateFormat $format;

    public function __construct(
        \ilLanguage $lng,
        string $title,
        DateFormat $format
    ) {
        parent::__construct($lng, $title);
        $this->format = $format;
    }

    public function getFormat(): DateFormat
    {
        return $this->format;
    }

    public function format($value): string
    {
        $this->checkArgInstanceOf('value', $value, \DateTimeImmutable::class);
        return $value->format($this->getFormat()->toString());
    }

    /**
     * @return string[]
     */
    public function getOrderingLabels(): array
    {
        return [
            $this->asc_label ?? $this->getTitle() . self::SEPERATOR . $this->lng->txt('order_option_chronological_ascending'),
            $this->desc_label ?? $this->getTitle() . self::SEPERATOR . $this->lng->txt('order_option_chronological_descending')
        ];
    }
}
