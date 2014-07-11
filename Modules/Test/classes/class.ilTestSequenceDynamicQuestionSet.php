<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Test sequence handler
 *
 * This class manages the sequence settings for a given user
 * and a dynamic question set test
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 * @package	Modules/Test
 */
class ilTestSequenceDynamicQuestionSet
{
	/**
	 * @var ilDB
	 */
	private $db = null;
	
	/**
	 * @var ilTestDynamicQuestionSet
	 */
	private $questionSet = null;
	
	/**
	 * @var integer
	 */
	private $activeId = null;
	
	/**
	 * @var array
	 */
	private $questionTracking = array();
	
	/**
	 * @var array
	 */
	private $postponedQuestions = array();
	
	/**
	 * @var array
	 */
	private $correctAnsweredQuestions = array();
	
	/**
	 * @var array
	 */
	private $wrongAnsweredQuestions = array();
	
	/**
	 * Constructor
	 * 
	 * @param ilTestDynamicQuestionSet $questionSet
	 */
	public function __construct(ilDB $db, ilTestDynamicQuestionSet $questionSet, $activeId)
	{
		$this->db = $db;
		$this->questionSet = $questionSet;
		$this->activeId = $activeId;
	}
	
	function getActiveId()
	{
		return $this->activeId;
	}
	
	public function loadFromDb()
	{
		$query = "SELECT * FROM tst_sequence WHERE active_fi = %s AND pass = %s";
		
		$res = $this->db->queryF($query, array('integer','integer'), array($this->activeId, 0));
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->questionTracking = unserialize($row["sequence"]);

			$this->postponedQuestions = unserialize($row["postponed"]);
			
			$hidden = unserialize($row["hidden"]);
			$this->correctAnsweredQuestions = $hidden['correct'];
			$this->wrongAnsweredQuestions = $hidden['wrong'];
			
			break;
		}
	}
	
	public function saveToDb()
	{
		$tracking = serialize($this->questionTracking);
		
		$postponed = serialize($this->postponedQuestions);

		$hidden = serialize(array(
			'correct' => $this->correctAnsweredQuestions,
			'wrong' => $this->wrongAnsweredQuestions
		));

		$query = "SELECT COUNT(*) cnt FROM tst_sequence WHERE active_fi = %s AND pass = %s";
		$res = $this->db->queryF($query, array('integer','integer'), array($this->activeId, 0));
		$row = $this->db->fetchAssoc($res);
		
		if( $row['cnt'] > 0 )
		{
			$this->db->update('tst_sequence', array(
					'sequence' => array('clob', $tracking),
					'postponed' => array('text', $postponed),
					'hidden' => array('text', $hidden),
					'tstamp' => array('integer', time())
				), array(
					'active_fi' => array('integer', $this->activeId),
					'pass' => array('integer', 0),
			));
		}
		else
		{			
			$this->db->insert('tst_sequence', array(
				'active_fi' => array('integer', $this->activeId),
				'pass' => array('integer', 0),
				'sequence' => array('clob', $tracking),
				'postponed' => array('text', $postponed),
				'hidden' => array('text', $hidden),
				'tstamp' => array('integer', time())
			));
		}
	}
	
	public function loadQuestions(ilObjTestDynamicQuestionSetConfig $dynamicQuestionSetConfig, $taxonomyFilterSelection)
	{
		$this->questionSet->load($dynamicQuestionSetConfig, $taxonomyFilterSelection);

//		echo "<table><tr>";
//		echo "<td width='200'><pre>".print_r($this->questionSet->getActualQuestionSequence(), 1)."</pre></td>";
//		echo "<td width='200'><pre>".print_r($this->correctAnsweredQuestions, 1)."</pre></td>";
//		echo "<td width='200'><pre>".print_r($this->wrongAnsweredQuestions, 1)."</pre></td>";
//		echo "</tr></table>";
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	public function cleanupQuestions(ilTestSessionDynamicQuestionSet $testSession)
	{
		switch( true )
		{
			case !$this->questionSet->questionExists($testSession->getCurrentQuestionId()):
			case !$this->isFilteredQuestion($testSession->getCurrentQuestionId()):
				
				$testSession->setCurrentQuestionId(null);
		}
		
		foreach($this->postponedQuestions as $questionId)
		{
			if( !$this->questionSet->questionExists($questionId) )
			{
				unset($this->postponedQuestions[$questionId]);
			}
		}
		
		foreach($this->wrongAnsweredQuestions as $questionId)
		{
			if( !$this->questionSet->questionExists($questionId) )
			{
				unset($this->wrongAnsweredQuestions[$questionId]);
			}
		}
		
		foreach($this->correctAnsweredQuestions as $questionId)
		{
			if( !$this->questionSet->questionExists($questionId) )
			{
				unset($this->correctAnsweredQuestions[$questionId]);
			}
		}
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	public function getUpcomingQuestionId()
	{
		$questionId = $this->fetchUpcomingQuestionId(true);
		
		if( $questionId )
		{
			return $questionId;
		}
		
		$questionId = $this->fetchUpcomingQuestionId(false);
		
		if( $questionId )
		{
			return $questionId;
		}
		
		return null;
	}
	
	private function fetchUpcomingQuestionId($forceNonAnswered = false)
	{
		foreach($this->questionSet->getActualQuestionSequence() as $level => $questions)
		{
			$postponedQuestions = array();
			
			foreach($questions as $pos => $qId)
			{
				if( isset($this->correctAnsweredQuestions[$qId]) )
				{
					continue;
				}
				
				if( isset($this->postponedQuestions[$qId]) )
				{
					$postponedQuestions[$qId] = $this->postponedQuestions[$qId];
					continue;
				}
				
				if( $forceNonAnswered && isset($this->wrongAnsweredQuestions[$qId]) )
				{
					continue;
				}
				
				return $qId;
			}
			
			if( count($postponedQuestions) )
			{
				$minPostponeCount = null;
				$minPostponeItem = null;

				foreach(array_reverse($postponedQuestions, true) as $qId => $postponeCount)
				{
					if($minPostponeCount === null || $postponeCount <= $minPostponeCount)
					{
						$minPostponeCount = $postponeCount;
						$minPostponeItem = $qId;
					}
				}

				return $minPostponeItem;
			}
		}
		
		return null;
	}
	
	public function isAnsweredQuestion($questionId)
	{
		return (
			isset($this->correctAnsweredQuestions[$questionId])
			|| isset($this->wrongAnsweredQuestions[$questionId])
		);
	}
	
	public function isPostponedQuestion($questionId)
	{
		return isset($this->postponedQuestions[$questionId]);
	}
	
	public function isFilteredQuestion($questionId)
	{
		foreach($this->questionSet->getActualQuestionSequence() as $level => $questions)
		{
			if( in_array($questionId, $questions) )
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function trackedQuestionExists()
	{
		return (bool)count($this->questionTracking);
	}
	
	public function getTrackedQuestionList($currentQuestionId = null)
	{
		$questionList = array();
		
		if( $currentQuestionId )
		{
			$questionList[$currentQuestionId] = $this->questionSet->getQuestionData($currentQuestionId);
		}
		
		foreach( array_reverse($this->questionTracking) as $trackedQuestion)
		{
			if( !isset($questionList[ $trackedQuestion['qid'] ]) )
			{
				$questionList[ $trackedQuestion['qid'] ] = $this->questionSet->getQuestionData($trackedQuestion['qid']);
			}
		}
		
		return $questionList;
	}
	
	public function openQuestionExists()
	{
		return count($this->getOpenQuestions()) > 0;
	}
	
	public function getOpenQuestions()
	{
		$completeQuestionIds = array_keys( $this->questionSet->getAllQuestionsData() );
		
		$openQuestions = array_diff($completeQuestionIds, $this->correctAnsweredQuestions);
		
		return $openQuestions;
	}
	
	public function getTrackedQuestionCount()
	{
		return count($this->questionTracking);
	}
	
	public function getCurrentPositionIndex($questionId)
	{
		$i = 0;
		
		foreach($this->questionSet->getActualQuestionSequence() as $level => $questions)
		{
			foreach($questions as $pos => $qId)
			{
				$i++;
				
				if($qId == $questionId)
				{
					return $i;
				}
			}
		}

		return null;
	}
	
	public function getLastPositionIndex()
	{
		$count = 0;
		
		foreach($this->questionSet->getActualQuestionSequence() as $level => $questions)
		{
			$count += count($questions);
		}
		
		return $count;
	}
	
	// -----------------------------------------------------------------------------------------------------------------

	public function setQuestionPostponed($questionId)
	{
		$this->trackQuestion($questionId, 'postponed');
		
		if( !isset($this->postponedQuestions[$questionId]) )
		{
			$this->postponedQuestions[$questionId] = 0;
		}
		
		$this->postponedQuestions[$questionId]++;
	}

	public function setQuestionAnsweredCorrect($questionId)
	{
		$this->trackQuestion($questionId, 'correct');
		
		$this->correctAnsweredQuestions[$questionId] = $questionId;
		
		if( isset($this->postponedQuestions[$questionId]) )
			unset($this->postponedQuestions[$questionId]);
		
		if( isset($this->wrongAnsweredQuestions[$questionId]) )
			unset($this->wrongAnsweredQuestions[$questionId]);
	}

	public function setQuestionAnsweredWrong($questionId)
	{
		$this->trackQuestion($questionId, 'wrong');
		
		$this->wrongAnsweredQuestions[$questionId] = $questionId;
		
		if( isset($this->postponedQuestions[$questionId]) )
			unset($this->postponedQuestions[$questionId]);
		
		if( isset($this->correctAnsweredQuestions[$questionId]) )
			unset($this->correctAnsweredQuestions[$questionId]);
	}
	
	private function trackQuestion($questionId, $answerStatus)
	{
		$this->questionTracking[] = array(
			'qid' => $questionId, 'status' => $answerStatus
		);
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	
	public function hasStarted()
	{
		return $this->trackedQuestionExists();
	}

	// -----------------------------------------------------------------------------------------------------------------
	
	public function getFilteredQuestionList()
	{
		return $this->questionSet->getFilteredQuestionsData();
	}

	// -----------------------------------------------------------------------------------------------------------------
	
	public function getUserSequenceQuestions()
	{
		//return array_keys( $this->getTrackedQuestionList() );
		
		$questionSequence = array();
		
		foreach( $this->questionSet->getActualQuestionSequence() as $level => $questions )
		{
			$questionSequence = array_merge($questionSequence, $questions);
		}
		
		return $questionSequence;
	}
	
}

