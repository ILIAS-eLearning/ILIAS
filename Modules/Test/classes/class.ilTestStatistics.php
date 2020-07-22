<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/Test/classes/class.ilStatistics.php";

/**
* This class calculates statistical data for a test which has to be
* calculated using all participant datasets (like the median).
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
*/
class ilTestStatistics
{
    public $test_id;
    public $statistics;
    
    /**
    * ilTestStatistics constructor
    *
    * The constructor takes the id of an existing test object
    *
    * @param integer $eval_data Complete test data as ilTestEvaluationData object
    * @access public
    */
    public function __construct($eval_data)
    {
        $this->statistics = null;
        $this->calculateStatistics($eval_data);
    }

    /**
    * Returns the statistics object
    *
    * @return object ilStatistics object
    * @access public
    * @see $statistics
    */
    public function getStatistics()
    {
        return $this->statistics;
    }

    /**
    * Instanciates the statistics object
    *
    * @access private
    * @see $statistics
    */
    public function calculateStatistics($eval_data)
    {
        $median_array = array();

        foreach ($eval_data->getParticipantIds() as $active_id) {
            $participant = &$eval_data->getParticipant($active_id);
            array_push($median_array, $participant->getReached());
        }

        $this->statistics = new ilStatistics();
        $this->statistics->setData($median_array);
    }
}
