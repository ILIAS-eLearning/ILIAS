<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    private $questionId;
    
    private $activeId;
    
    private $pass;
    
    public function __construct($questionId, $activeId, $pass)
    {
        $this->questionId = $questionId;
        $this->activeId = $activeId;
        $this->pass = $pass;
    }

    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    public function getActiveId()
    {
        return $this->activeId;
    }

    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }

    public function getQuestionId()
    {
        return $this->questionId;
    }
    
    /**
     * Returns the fact wether there exists hint requests for the given
     * question relating to the given testactive and testpass or not
     *
     * @access	public
     * @global	ilDBInterface					$ilDB
     * @return	boolean					$requestsExist
     */
    public function requestsExist()
    {
        if (self::getNumExistingRequests($this->getQuestionId(), $this->getActiveId(), $this->getPass()) > 0) {
            return true;
        }
        
        return false;
    }

    /**
     * Returns the number existing hint requests for the given
     * question relating to the given testactive and testpass or not
     *
     * @access	public
     * @global	ilDBInterface					$ilDB
     * @return	integer					$numExisingRequests
     */
    public function getNumExistingRequests()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "
			SELECT		COUNT(qhtr_track_id) cnt
			
			FROM		qpl_hint_tracking
			
			WHERE		qhtr_question_fi = %s
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
		";
        
        $res = $ilDB->queryF(
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getQuestionId(), $this->getActiveId(), $this->getPass())
        );
        
        $row = $ilDB->fetchAssoc($res);
        
        return $row['cnt'];
    }

    /**
     * Returns the fact wether (further) hint requests are possible for the given
     * question relating to the given testactive and testpass or not
     *
     * @access	public
     * @global	ilDBInterface		$ilDB
     * @return	boolean		$requestsPossible
     */
    public function requestsPossible()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
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
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getActiveId(), $this->getPass(), $this->getQuestionId())
        );
        
        $row = $ilDB->fetchAssoc($res);
        
        if ($row['cnt_available'] > $row['cnt_requested']) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns the fact wether the hint for given id is requested
     * for the given testactive and testpass
     *
     * @access	public
     * @global	ilDBInterface	$ilDB
     * @param	integer	$hintId
     * @return	boolean	$isRequested
     */
    public function isRequested($hintId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "
			SELECT		COUNT(qhtr_track_id) cnt
			
			FROM		qpl_hint_tracking
			
			WHERE		qhtr_hint_fi = %s
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
		";
        
        $res = $ilDB->queryF(
            $query,
            array('integer', 'integer', 'integer'),
            array($hintId, $this->getActiveId(), $this->getPass())
        );
        
        $row = $ilDB->fetchAssoc($res);
        
        if ($row['cnt'] > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns the next requestable hint for given question
     * relating to given testactive and testpass
     *
     * @access	public
     * @global	ilDBInterface				$ilDB
     * @return	ilAssQuestionHint	$nextRequestableHint
     * @throws	ilTestException
     */
    public function getNextRequestableHint()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
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
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getActiveId(), $this->getPass(), $this->getQuestionId())
        );
        
        while ($row = $ilDB->fetchAssoc($res)) {
            $nextHint = ilAssQuestionHint::getInstanceById($row['qht_hint_id']);
            
            return $nextHint;
        }
        
        require_once 'Modules/Test/exceptions/class.ilTestNoNextRequestableHintExistsException.php';
        
        throw new ilTestNoNextRequestableHintExistsException(
            "no next hint found for questionId={$this->getQuestionId()}, activeId={$this->getActiveId()}, pass={$this->getPass()}"
        );
    }
    
    /**
     * Returns an object of class ilAssQuestionHintList containing objects
     * of class ilAssQuestionHint for all allready requested hints
     * relating to the given question, testactive and testpass
     *
     * @access	public
     * @global	ilDBInterface					$ilDB
     * @return	ilAssQuestionHintList	$requestedHintsList
     */
    public function getRequestedHintsList()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "
			SELECT		qhtr_hint_fi
			
			FROM		qpl_hint_tracking
			
			WHERE		qhtr_question_fi = %s
			AND			qhtr_active_fi = %s
			AND			qhtr_pass = %s
		";
        
        $res = $ilDB->queryF(
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getQuestionId(), $this->getActiveId(), $this->getPass())
        );
        
        $hintIds = array();
        
        while ($row = $ilDB->fetchAssoc($res)) {
            $hintIds[] = $row['qhtr_hint_fi'];
        }
        
        $requestedHintsList = ilAssQuestionHintList::getListByHintIds($hintIds);
        
        return $requestedHintsList;
    }
    
    /**
     * Tracks the given hint as requested for the given
     * question, testactive and testpass
     *
     * @access	public
     * @global	ilDBInterface				$ilDB
     * @param	ilAssQuestionHint	$questionHint
     */
    public function storeRequest(ilAssQuestionHint $questionHint)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $trackId = $ilDB->nextId('qpl_hint_tracking');
        
        $ilDB->insert('qpl_hint_tracking', array(
            'qhtr_track_id' => array('integer', $trackId),
            'qhtr_active_fi' => array('integer', $this->getActiveId()),
            'qhtr_pass' => array('integer', $this->getPass()),
            'qhtr_question_fi' => array('integer', $this->getQuestionId()),
            'qhtr_hint_fi' => array('integer', $questionHint->getId()),
        ));
    }
    
    /**
     * Returns a question hint request statistic data container
     * containing the statistics for all requests relating to given ...
     * - question
     * - testactive
     * - testpass
     *
     * @access public
     * @global ilDBInterface $ilDB
     * @return ilAssQuestionHintRequestStatisticData $requestsStatisticData
     */
    public function getRequestStatisticDataByQuestionAndTestpass()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
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
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getQuestionId(), $this->getActiveId(), $this->getPass())
        );
        
        $row = $ilDB->fetchAssoc($res);
        
        if ($row['requests_points'] === null) {
            $row['requests_points'] = 0;
        }
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestStatisticData.php';
        
        $requestsStatisticData = new ilAssQuestionHintRequestStatisticData();
        $requestsStatisticData->setRequestsCount($row['requests_count']);
        $requestsStatisticData->setRequestsPoints($row['requests_points']);
        
        return $requestsStatisticData;
    }
    
    /**
     * @param integer $activeId
     * @return ilAssQuestionHintRequestStatisticRegister
     */
    public static function getRequestRequestStatisticDataRegisterByActiveId($activeId)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestStatisticRegister.php';
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestStatisticData.php';
        
        /* @var ILIAS\DI\Container $DIC */ global $DIC;
        $db = $DIC->database();
        
        $query = "
			SELECT		qhtr_pass requests_pass,
						qhtr_question_fi requests_question,
						COUNT(qhtr_track_id) requests_count,
						SUM(qht_hint_points) requests_points
			
			FROM		qpl_hint_tracking
			
			INNER JOIN	qpl_hints
			ON			qht_hint_id = qhtr_hint_fi
			
			WHERE		qhtr_active_fi = %s
			
			GROUP BY	qhtr_pass, qhtr_question_fi
		";
        
        $res = $db->queryF(
            $query,
            array('integer'),
            array($activeId)
        );
        
        $register = new ilAssQuestionHintRequestStatisticRegister();
        
        while ($row = $db->fetchAssoc($res)) {
            if ($row['requests_points'] === null) {
                $row['requests_points'] = 0;
            }
            
            $requestsStatisticData = new ilAssQuestionHintRequestStatisticData();
            $requestsStatisticData->setRequestsCount($row['requests_count']);
            $requestsStatisticData->setRequestsPoints($row['requests_points']);
            
            $register->addRequestByTestPassIndexAndQuestionId($row['requests_pass'], $row['requests_question'], $requestsStatisticData);
        }
        
        return $register;
    }
    
    /**
     * Deletes all hint requests relating to a question included in given question ids
     * @param array[integer] $questionIds
     */
    public static function deleteRequestsByQuestionIds($questionIds)
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;
        $ilDB = $DIC['ilDB'];

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
     * @access public
     * @global ilDBInterface $ilDB
     * @param array[integer] $activeIds
     */
    public static function deleteRequestsByActiveIds($activeIds)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $__active_fi__IN__activeIds = $ilDB->in('qhtr_active_fi', $activeIds, false, 'integer');
        
        $query = "
			DELETE FROM	qpl_hint_tracking
			WHERE		$__active_fi__IN__activeIds
		";
        
        $ilDB->manipulate($query);
    }
}
