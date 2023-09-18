<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WeekGridEntry
{
    protected int $start;
    protected int $end;
    protected string $html;

    public function __construct(
        int $start,
        int $end,
        string $html
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->html = $html;
    }

    public function getStart() : int
    {
        return $this->start;
    }

    public function getEnd() : int
    {
        return $this->end;
    }

    public function getHTML() : string
    {
        return $this->html;
    }
}
