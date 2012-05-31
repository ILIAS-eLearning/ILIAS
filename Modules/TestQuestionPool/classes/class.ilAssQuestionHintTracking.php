<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintList.php';

/**
 * Class for tracking of question hint requests
 * 
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintTracking
{
	/**
	 * Returns the fact wether there exists hint requests for the given
	 * question relating to the given testactive and testpass or not
	 *
	 * @static
	 * @access	public
	 * @global	ilDB					$ilDB
	 * @param	integer					$questionId
	 * @param	integer					$activeId
	 * @param	integer					$pass
	 * @return	boolean					$requestsExist
	 */
	public static function requestsExist($questionId, $activeId, $pass)
	{
		global $ilDB;
		
		$query = "
			SELECT		COUNT(qhtr_track_id) cnt
			
			FROM		qpl_hint_tracking
			
			WHERE		qhtr_question_fi = %s
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
		";
		
		$res = $ilDB->queryF(
				$query, array('integer', 'integer', 'integer'), array($questionId, $activeId, $pass)
		);
		
		$row = $ilDB->fetchAssoc($res);
		
		if( $row['cnt'] > 0 )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the fact wether (further) hint requests are possible for the given
	 * question relating to the given testactive and testpass or not
	 *
	 * @static
	 * @access	public
	 * @global	ilDB		$ilDB
	 * @param	integer		$questionId
	 * @param	integer		$activeId
	 * @param	integer		$pass
	 * @return	boolean		$requestsPossible
	 */
	public static function requestsPossible($questionId, $activeId, $pass)
	{
		global $ilDB;
		
		$query = "
			SELECT		COUNT(qht_hint_id) cnt_available,
						COUNT(qhtr_track_id) cnt_requested
			
			FROM		qpl_hints
			
			LEFT JOIN	qpl_hint_tracking
			ON			qhtr_hint_fi = qht_hint_id
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
			
			WHERE		qht_question_fi = %s
		";
		
		$res = $ilDB->queryF(
				$query, array('integer', 'integer', 'integer'), array($activeId, $pass, $questionId)
		);
		
		$row = $ilDB->fetchAssoc($res);
		
		if( $row['cnt_available'] > $row['cnt_requested'] )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the fact wether the hint for given id is requested
	 * for the given testactive and testpass
	 *
	 * @static
	 * @access	public
	 * @global	ilDB	$ilDB
	 * @param	integer	$hintId
	 * @param	integer	$activeId
	 * @param	integer	$pass
	 * @return	boolean	$isRequested
	 */
	public static function isRequested($hintId, $activeId, $pass)
	{
		global $ilDB;
		
		$query = "
			SELECT		COUNT(qhtr_track_id) cnt
			
			FROM		qpl_hint_tracking
			
			WHERE		qhtr_hint_fi = %s
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
		";
		
		$res = $ilDB->queryF(
				$query, array('integer', 'integer', 'integer'), array($hintId, $activeId, $pass)
		);
		
		$row = $ilDB->fetchAssoc($res);
		
		if( $row['cnt'] > 0 )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the next requestable hint for given question
	 * relating to given testactive and testpass
	 *
	 * @static
	 * @access	public
	 * @global	ilDB				$ilDB
	 * @param	integer				$questionId
	 * @param	integer				$activeId
	 * @param	integer				$pass
	 * @return	ilAssQuestionHint	$nextRequestableHint
	 * @throws	ilTestException
	 */
	public static function getNextRequestableHint($questionId, $activeId, $pass)
	{
		global $ilDB;
		
		$query = "
			SELECT		qht_hint_id
			
			FROM		qpl_hints
			
			LEFT JOIN	qpl_hint_tracking
			ON			qhtr_hint_fi = qht_hint_id
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
			
			WHERE		qht_question_fi = %s
			AND			qhtr_track_id IS NULL
			
			ORDER BY	qht_hint_index ASC
		";
		
		$ilDB->setLimit(1);
		
		$res = $ilDB->queryF(
				$query, array('integer', 'integer', 'integer'), array($activeId, $pass, $questionId)
		);
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			$nextHint = ilAssQuestionHint::getInstanceById($row['qht_hint_id']);
			
			return $nextHint;
		}
		
		throw new ilTestException("no next hint found for questionId=$questionId, activeId=$activeId, pass=$pass");
	}
	
	/**
	 * Returns an object of class ilAssQuestionHintList containing objects
	 * of class ilAssQuestionHint for all allready requested hints
	 * relating to the given question, testactive and testpass
	 * 
	 * @static
	 * @access	public
	 * @global	ilDB					$ilDB
	 * @param	integer					$questionId
	 * @param	integer					$activeId
	 * @param	integer					$pass
	 * @return	ilAssQuestionHintList	$requestedHintsList
	 */
	public static function getRequestedHintsList($questionId, $activeId, $pass)
	{
		global $ilDB;
		
		$query = "
			SELECT		qhtr_hint_fi
			
			FROM		qpl_hint_tracking
			
			WHERE		qhtr_question_fi = %s
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
		";
		
		$res = $ilDB->queryF(
				$query, array('integer', 'integer', 'integer'), array($questionId, $activeId, $pass)
		);
		
		$hintIds = array();
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			$hintIds[] = $row['qhtr_hint_fi'];
		}
		
		$requestedHintsList = ilAssQuestionHintList::getListByHintIds($hintIds);
		
		return $requestedHintsList;
	}
	
	/**
	 * Tracks the given hint as requested for the given
	 * question, testactive and testpass
	 *
	 * @static
	 * @access	public
	 * @global	ilDB				$ilDB
	 * @param	ilAssQuestionHint	$questionHint
	 * @param	integer				$questionId
	 * @param	integer				$activeId
	 * @param	integer				$pass
	 */
	public static function storeRequest(ilAssQuestionHint $questionHint, $questionId, $activeId, $pass)
	{
		global $ilDB;
		
		$trackId = $ilDB->nextId('qpl_hint_tracking');
		
		$ilDB->insert('qpl_hint_tracking', array(
			'qhtr_track_id'		=> array('integer', $trackId),
			'qhtr_active_fi'	=> array('integer', $activeId),
			'qhtr_pass'			=> array('integer', $pass),
			'qhtr_question_fi'	=> array('integer', $questionId),
			'qhtr_hint_fi'		=> array('integer', $questionHint->getId()),
		));
	}
	
	/**
	 * Returns a question hint request statistic data container
	 * containing the statistics for all requests relating to given ...
	 * - question
	 * - testactive
	 * - testpass
	 *
	 * @static
	 * @access public
	 * @global ilDB $ilDB
	 * @param integer $questionId
	 * @param integer $activeId
	 * @param integer $pass
	 * @return ilAssQuestionHintRequestStatisticData $requestsStatisticData
	 */
	public static function getRequestStatisticDataByQuestionAndTestpass($questionId, $activeId, $pass)
	{
		global $ilDB;
		
		$query = "
			SELECT		COUNT(qhtr_track_id) requests_count,
						SUM(qht_hint_points) requests_points
			
			FROM		qpl_hint_tracking
			
			INNER JOIN	qpl_hints
			ON			qht_hint_id = qhtr_hint_fi
			
			WHERE		qhtr_question_fi = %s
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
		";
		
		$res = $ilDB->queryF(
				$query, array('integer', 'integer', 'integer'), array($questionId, $activeId, $pass)
		);
		
		$row = $ilDB->fetchAssoc($res);
		
		if( $row['requests_points'] === null )
		{
			$row['requests_points'] = 0;
		}
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestStatisticData.php';
		
		$requestsStatisticData = new ilAssQuestionHintRequestStatisticData();
		$requestsStatisticData->setRequestsCount($row['requests_count']);
		$requestsStatisticData->setRequestsPoints($row['requests_points']);
		
		return $requestsStatisticData;
	}
	
	/**
	 * Deletes all hint requests relating to a question included in given question ids
	 *
	 * @static
	 * @access public
	 * @global ilDB $ilDB
	 * @param array[integer] $questionIds 
	 */
	public static function deleteRequestsByQuestionIds($questionIds)
	{
		global $ilDB;
		
		$__question_fi__IN__questionIds = $ilDB->in('qhtr_question_fi', $questionIds, false, 'integer');
				
		$query = "
			DELETE FROM	qpl_hint_tracking
			WHERE		$__question_fi__IN__questionIds
		";
		
		$ilDB->manipulate($query);
	}
	
	/**
	 * Deletes all hint requests relating to a testactive included in given active ids
	 *
	 * @static
	 * @access public
	 * @global ilDB $ilDB
	 * @param array[integer] $activeIds 
	 */
	public static function deleteRequestsByActiveIds($activeIds)
	{
		global $ilDB;
		
		$__active_fi__IN__activeIds = $ilDB->in('qhtr_active_fi', $activeIds, false, 'integer');
		
		$query = "
			DELETE FROM	qpl_hint_tracking
			WHERE		$__active_fi__IN__activeIds
		";
		
		$ilDB->manipulate($query);
	}
}

