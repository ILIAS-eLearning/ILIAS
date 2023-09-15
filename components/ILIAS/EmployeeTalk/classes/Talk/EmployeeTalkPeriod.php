<?php

declare(strict_types=1);

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

namespace ILIAS\Modules\EmployeeTalk\Talk;

use ilDatePeriod;
use ilDateTime;

final class EmployeeTalkPeriod implements ilDatePeriod
{
    private ilDateTime $start;
    private ilDateTime $end;
    private bool $fullDay;

    /**
     * EmployeeTalkPeriod constructor.
     * @param ilDateTime $start
     * @param ilDateTime $end
     * @param bool       $fullDay
     */
    public function __construct(ilDateTime $start, ilDateTime $end, bool $fullDay)
    {
        $this->start = $start;
        $this->end = $end;
        $this->fullDay = $fullDay;
    }

    public function getStart(): ilDateTime
    {
        return $this->start;
    }

    public function getEnd(): ilDateTime
    {
        return $this->end;
    }

    public function isFullday(): bool
    {
        return $this->fullDay;
    }
}
