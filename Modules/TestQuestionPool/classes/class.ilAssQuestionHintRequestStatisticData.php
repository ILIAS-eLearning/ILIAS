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
     * @var float
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
     * Getter for requestsPoints
     *
     * @access public
     * @return float $requestsPoints
     */
    public function getRequestsPoints(): ?int
    {
        return $this->requestsPoints;
    }

    /**
     * Setter for requestsPoints
     *
     * @access public
     * @param float $requestsPoints
     */
    public function setRequestsPoints($requestsPoints): void
    {
        $this->requestsPoints = abs($requestsPoints);
    }

    /**
     * Getter for requestsCount
     *
     * @access public
     * @return integer $requestsCount
     */
    public function getRequestsCount(): ?int
    {
        return $this->requestsCount;
    }

    /**
     * Setter for requestsCount
     *
     * @access public
     * @param integer $requestsCount
     */
    public function setRequestsCount($requestsCount): void
    {
        $this->requestsCount = $requestsCount;
    }
}
