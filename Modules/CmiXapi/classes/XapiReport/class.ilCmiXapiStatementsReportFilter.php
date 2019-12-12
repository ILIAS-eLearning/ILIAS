<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiReportFilter
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsReportFilter
{
    /**
     * @var string
     */
    protected $activityId;
    
    /**
     * @var int
     */
    protected $limit;
    
    /**
     * @var int
     */
    protected $offset;
    
    /**
     * @var string
     */
    protected $orderField;
    
    /**
     * @var string
     */
    protected $orderDirection;
    
    /**
     * @var ilCmiXapiUser
     */
    protected $actor;
    
    /**
     * @var string
     */
    protected $verb;
    
    /**
     * @var ilCmiXapiDateTime
     */
    protected $startDate;
    
    /**
     * @var ilCmiXapiDateTime
     */
    protected $endDate;
    
    /**
     * @return string
     */
    public function getActivityId()
    {
        return $this->activityId;
    }
    
    /**
     * @param string $activityId
     */
    public function setActivityId($activityId)
    {
        $this->activityId = $activityId;
    }
    
    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    /**
     * @return string
     */
    public function getOrderField()
    {
        return $this->orderField;
    }
    
    /**
     * @param string $orderField
     */
    public function setOrderField($orderField)
    {
        $this->orderField = $orderField;
    }
    
    /**
     * @return string
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }
    
    /**
     * @param string $orderDirection
     */
    public function setOrderDirection($orderDirection)
    {
        $this->orderDirection = $orderDirection;
    }
    
    /**
     * @return ilCmiXapiUser
     */
    public function getActor()
    {
        return $this->actor;
    }
    
    /**
     * @param ilCmiXapiUser $actor
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }
    
    /**
     * @return string
     */
    public function getVerb()
    {
        return $this->verb;
    }
    
    /**
     * @param string $verb
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
    }
    
    /**
     * @return ilCmiXapiDateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * @param ilCmiXapiDateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }
    
    /**
     * @return ilCmiXapiDateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    
    /**
     * @param ilCmiXapiDateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }
}
