<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    
    public function getTotalMaxSkillPoints()
    {
        return $this->totalMaxSkillPoints;
    }

    public function getTotalReachedSkillPoints()
    {
        return $this->totalReachedSkillPoints;
    }
    
    public function getNumBookings()
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
