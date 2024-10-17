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
use ILIAS\Language\Language;

class Date extends Column implements C\Date
{
    protected \DateTimeZone $tz;

    public function __construct(
        Language $lng,
        \DateTimeZone $user_tz,
        string $title,
        protected DateFormat $format,
    ) {
        parent::__construct($lng, $title);
        $this->tz = $user_tz;
    }

    public function withTimeZone(\DateTimeZone $tz): C\Date
    {
        $clone = clone $this;
        $clone->tz = $tz;
        return $clone;
    }

    public function getTimeZone(): \DateTimeZone
    {
        return $this->tz;
    }

    public function getFormat(): DateFormat
    {
        return $this->format;
    }

    public function format($value): string
    {
        $this->checkArgInstanceOf('value', $value, \DateTimeImmutable::class);
        return $value->setTimezone($this->getTimeZone())->format($this->getFormat()->toString());
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
