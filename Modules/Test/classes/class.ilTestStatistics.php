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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

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
    public function getStatistics(): ?object
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
            $participant = $eval_data->getParticipant($active_id);
            array_push($median_array, $participant->getReached());
        }

        $this->statistics = new ilStatistics();
        $this->statistics->setData($median_array);
    }
}
