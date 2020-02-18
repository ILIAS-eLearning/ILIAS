<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/OrgUnit/classes/Positions/Operation/class.ilOrgUnitOperation.php';

/**
 * Class ilTestParticipantAccessFilter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilTestParticipantAccessFilter
{
    const FILTER_MANAGE_PARTICIPANTS = 'manageParticipantsUserFilter';
    const FILTER_SCORE_PARTICIPANTS = 'scoreParticipantsUserFilter';
    const FILTER_ACCESS_RESULTS = 'accessResultsUserFilter';
    const FILTER_ACCESS_STATISTICS = 'accessStatisticsUserFilter';
    
    const CALLBACK_METHOD = 'filterCallback';
    
    /**
     * @var integer
     */
    protected $refId;
    
    /**
     * @var string
     */
    protected $filter;
    
    /**
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }
    
    /**
     * @param int $refId
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;
    }
    
    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }
    
    /**
     * @param string $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }
    
    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function filterCallback($userIds)
    {
        switch ($this->getFilter()) {
            case self::FILTER_MANAGE_PARTICIPANTS:
                return $this->manageParticipantsUserFilter($userIds);

            case self::FILTER_SCORE_PARTICIPANTS:
                return $this->scoreParticipantsUserFilter($userIds);

            case self::FILTER_ACCESS_RESULTS:
                return $this->accessResultsUserFilter($userIds);

            case self::FILTER_ACCESS_STATISTICS:
                return $this->accessStatisticsUserFilter($userIds);
        }
        
        require_once 'Modules/Test/exceptions/class.ilTestException.php';
        throw new ilTestException('invalid user access filter mode chosen: ' . $this->getFilter());
    }
    
    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function manageParticipantsUserFilter($userIds)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $userIds = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'write',
            ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS,
            $this->getRefId(),
            $userIds
        );
        
        return $userIds;
    }
    
    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function scoreParticipantsUserFilter($userIds)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $userIds = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'write',
            ilOrgUnitOperation::OP_SCORE_PARTICIPANTS,
            $this->getRefId(),
            $userIds
        );
        
        return $userIds;
    }
    
    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function accessResultsUserFilter($userIds)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $userIds = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'write',
            ilOrgUnitOperation::OP_ACCESS_RESULTS,
            $this->getRefId(),
            $userIds
        );
        
        return $userIds;
    }
    
    /**
     * @param int[] $userIds
     * @return int[]
     */
    public function accessStatisticsUserFilter($userIds)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($DIC->access()->checkAccess('tst_statistics', '', $this->getRefId())) {
            return $userIds;
        }
        
        return $this->accessResultsUserFilter($userIds);
    }
    
    /**
     * @param integer $refId
     * @return callable
     */
    public static function getManageParticipantsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_MANAGE_PARTICIPANTS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }
    
    /**
     * @param integer $refId
     * @return callable
     */
    public static function getScoreParticipantsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_SCORE_PARTICIPANTS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }
    
    /**
     * @param integer $refId
     * @return callable
     */
    public static function getAccessResultsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_ACCESS_RESULTS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }
    
    /**
     * @param integer $refId
     * @return callable
     */
    public static function getAccessStatisticsUserFilter($refId)
    {
        $filter = new self();
        $filter->setFilter(self::FILTER_ACCESS_STATISTICS);
        $filter->setRefId($refId);
        return [$filter, self::CALLBACK_METHOD];
    }
}
