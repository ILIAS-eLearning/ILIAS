<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Container for question hint request statistic data
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintRequestStatisticData
{
    /**
     * The sum of points deducted
     *
     * @var integer
     */
    private $requestsPoints = null;
    
    /**
     * The number of hint requests
     *
     * @var integer
     */
    private $requestsCount = null;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Getter for requestsPonts
     *
     * @access public
     * @return integer $requestsPoints
     */
    public function getRequestsPoints()
    {
        return $this->requestsPoints;
    }

    /**
     * Setter for requestsPonts
     *
     * @access public
     * @param integer $requestsPoints
     */
    public function setRequestsPoints($requestsPoints)
    {
        $this->requestsPoints = $requestsPoints;
    }

    /**
     * Getter for requestsCount
     *
     * @access public
     * @return integer $requestsCount
     */
    public function getRequestsCount()
    {
        return $this->requestsCount;
    }

    /**
     * Setter for requestsCount
     *
     * @access public
     * @param integer $requestsCount
     */
    public function setRequestsCount($requestsCount)
    {
        $this->requestsCount = $requestsCount;
    }
}
