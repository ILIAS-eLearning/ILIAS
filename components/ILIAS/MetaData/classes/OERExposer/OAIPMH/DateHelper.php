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

namespace ILIAS\MetaData\OERExposer\OAIPMH;

trait DateHelper
{
    protected function getCurrentDateTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    protected function getCurrentDate(): \DateTimeImmutable
    {
        return $this->getCurrentDateTime()->setTime(0, 0);
    }

    protected function getFormattedDateTime(\DateTimeImmutable $datetime): string
    {
        return $datetime->format('Y-m-d\TH:i:sp');
    }

    protected function getFormattedDate(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d');
    }

    protected function isStringValidAsDate(string $string): bool
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $string);
        return $date && $date->format('Y-m-d') === $string;
    }

    protected function getDateFromString(string $string): \DateTimeImmutable
    {
        return new \DateTimeImmutable($string, new \DateTimeZone('UTC'));
    }
}
