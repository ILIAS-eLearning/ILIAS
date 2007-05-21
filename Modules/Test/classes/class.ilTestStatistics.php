<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./classes/class.ilStatistics.php";

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
	var $test_id;
	var $statistics;
	
/**
* ilTestStatistics constructor
*
* The constructor takes the id of an existing test object 
*
* @param integer $eval_data Complete test data as ilTestEvaluationData object
* @access public
*/
	function ilTestStatistics($eval_data)
	{
		$this->statistics = NULL;
		$this->calculateStatistics($eval_data);
	}

	/**
	* Returns the statistics object
	*
	* Returns the statistics object
	*
	* @return object ilStatistics object
	* @access public
	* @see $statistics
	*/
	function getStatistics()
	{
		return $this->statistics;
	}

	/**
	* Instanciates the statistics object
	*
	* Instanciates the statistics object
	*
	* @access private
	* @see $statistics
	*/
	function calculateStatistics($eval_data)
	{
		$median_array = array();

		foreach ($eval_data->getParticipantIds() as $active_id)
		{
			$participant =& $eval_data->getParticipant($active_id);
			array_push($median_array, $participant->getReached());
		}

		$this->statistics = new ilStatistics();
		$this->statistics->setData($median_array);
	}
}

?>
