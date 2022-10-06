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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillPointAccount
{
    private $totalMaxSkillPoints;

    private $totalReachedSkillPoints;

    private $numBookings;

    public function __construct()
    {
        $this->totalMaxSkillPoints = 0;
        $this->totalReachedSkillPoints = 0;

        $this->numBookings = 0;
    }

    public function addBooking($maxSkillPoints, $reachedSkillPoints)
    {
        $this->totalMaxSkillPoints += $maxSkillPoints;
        $this->totalReachedSkillPoints += $reachedSkillPoints;

        $this->numBookings++;
    }

    public function getTotalMaxSkillPoints(): int
    {
        return $this->totalMaxSkillPoints;
    }

    public function getTotalReachedSkillPoints(): int
    {
        return $this->totalReachedSkillPoints;
    }

    public function getNumBookings(): int
    {
        return $this->numBookings;
    }

    public function getTotalReachedSkillPercent()
    {
        return (
            ($this->getTotalReachedSkillPoints() * 100) / $this->getTotalMaxSkillPoints()
        );
    }
}
