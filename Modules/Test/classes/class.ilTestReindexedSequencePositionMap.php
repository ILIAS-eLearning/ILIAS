<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilTestReindexedSequencePositionMap
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/Test
 */
class ilTestReindexedSequencePositionMap
{
    /**
     * @var array
     */
    protected $sequencePositionMap = array();
    
    /**
     * @param int $oldSequencePosition
     * @param int $newSequencePosition
     */
    public function addPositionMapping($oldSequencePosition, $newSequencePosition)
    {
        $this->sequencePositionMap[$oldSequencePosition] = $newSequencePosition;
    }
    
    /**
     * @param int $oldSequencePosition
     * @return int
     */
    public function getNewSequencePosition($oldSequencePosition)
    {
        return $this->sequencePositionMap[$oldSequencePosition];
    }
}
