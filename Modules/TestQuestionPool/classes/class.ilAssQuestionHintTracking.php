<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	 * @global	ilDB					$ilDB
	 * @param	integer					$questionId
	 * @param	integer					$activeId
	 * @param	integer					$pass
	 * @return	boolean					$requestsPossible
	 */
	public static function requestsPossible($questionId, $activeId, $pass)
	{
		global $ilDB;
		
		$query = "
			SELECT		COUNT(qht_hint_id) cnt_available,
						COUNT(qhtr_track_id) cnt_requested
			
			FROM		qpl_hints
			
			LEFT JOIN	qpl_hint_tracking
			ON			qhtr_question_fi = qht_question_fi
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
}

