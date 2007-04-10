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
* @param integer $a_test_id Test id
* @access public
*/
	function ilTestStatistics($a_test_id)
	{
		$this->test_id = $a_test_id;
		$this->statistics = NULL;
		$this->calculateStatistics();
	}

	/**
	* Returns the test id
	*
	* Returns the test id
	*
	* @return integer Test id
	* @access public
	* @see $test_id
	*/
	function getTestId()
	{
		return $this->test_id;
	}

	/**
	* Sets the test id
	*
	* Sets the test id
	*
	* @param integer $a_test_id Test id
	* @access public
	* @see $test_id
	*/
	function setTestId($a_test_id)
	{
		$this->test_id = $a_test_id;
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
	function calculateStatistics()
	{
		global $ilDB;

		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$overview =& ilObjTest::_evalResultsOverview($this->getTestId());

		$query = sprintf("SELECT tst_tests.pass_scoring FROM tst_tests WHERE tst_tests.test_id = %s",
			$ilDB->quote($this->getTestId() . "")
		);
		$result = $ilDB->query($query);
		$pass_scoring = SCORE_LAST_PASS;
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$pass_scoring = $row["pass_scoring"];
		}

		$median_array = array();

		foreach ($overview as $active_id => $userdata)
		{
			$bestpass = 0;
			$bestpasspoints = 0;
			$lastpass = 0;
			foreach ($userdata as $passnr => $userresults)
			{
				if (is_numeric($passnr))
				{
					$reached = $pass[$passnr]["reached"];
					if ($reached > $bestpasspoints)
					{
						$bestpasspoints = $reached;
						$bestpass = $passnr;
					}
					if ($passnr > $lastpass) $lastpass = $passnr;
				}
			}
			$statpass = 0;
			if ($pass_scoring == SCORE_BEST_PASS)
			{
				$statpass = $bestpass;
			}
			else
			{
				$statpass = $lastpass;
			}
			array_push($median_array, $userdata[$statpass]["reached"]);
		}

		$this->statistics = new ilStatistics();
		$this->statistics->setData($median_array);
	}
}

?>
