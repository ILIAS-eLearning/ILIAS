<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSession.php';
require_once 'Modules/Test/classes/class.ilTestDynamicQuestionSetFilterSelection.php';

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';

/**
 * Test session handler for tests with mode dynamic question set
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestSessionDynamicQuestionSet extends ilTestSession
{
    /**
     * @var ilTestDynamicQuestionSetFilterSelection
     */
    private $questionSetFilterSelection = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->questionSetFilterSelection = new ilTestDynamicQuestionSetFilterSelection();
    }

    /**
     * @return ilTestDynamicQuestionSetFilterSelection
     */
    public function getQuestionSetFilterSelection()
    {
        return $this->questionSetFilterSelection;
    }
    
    public function loadFromDb($active_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT * FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $this->active_id = $row["active_id"];
            $this->user_id = $row["user_fi"];
            $this->anonymous_id = $row["anonymous_id"];
            $this->test_id = $row["test_fi"];
            $this->lastsequence = $row["lastindex"];
            $this->pass = $row["tries"];
            $this->submitted = ($row["submitted"]) ? true : false;
            $this->submittedTimestamp = $row["submittimestamp"];
            $this->tstamp = $row["tstamp"];

            $this->questionSetFilterSelection->setTaxonomySelection(unserialize($row['taxfilter']));
            $this->questionSetFilterSelection->setAnswerStatusSelection($row['answerstatusfilter']);
            $this->questionSetFilterSelection->setAnswerStatusActiveId($row['active_id']);
        }
    }
    
    public function loadTestSession($test_id, $user_id = "", $anonymous_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if (!$user_id) {
            $user_id = $ilUser->getId();
        }
        if (($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID) && $this->doesAccessCodeInSessionExists()) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
                array('integer','integer','text'),
                array($user_id, $test_id, $this->getAccessCodeFromSession())
            );
        } elseif (strlen($anonymous_id)) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
                array('integer','integer','text'),
                array($user_id, $test_id, $anonymous_id)
            );
        } else {
            if ($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID) {
                return null;
            }
            $result = $ilDB->queryF(
                "SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
                array('integer','integer'),
                array($user_id, $test_id)
            );
        }

        // TODO bheyser: Refactor
        $this->user_id = $user_id;

        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $this->active_id = $row["active_id"];
            $this->user_id = $row["user_fi"];
            $this->anonymous_id = $row["anonymous_id"];
            $this->test_id = $row["test_fi"];
            $this->lastsequence = $row["lastindex"];
            $this->pass = $row["tries"];
            $this->submitted = ($row["submitted"]) ? true : false;
            $this->submittedTimestamp = $row["submittimestamp"];
            $this->tstamp = $row["tstamp"];

            $this->questionSetFilterSelection->setTaxonomySelection(unserialize($row['taxfilter']));
            $this->questionSetFilterSelection->setAnswerStatusSelection($row['answerstatusfilter']);
            $this->questionSetFilterSelection->setAnswerStatusActiveId($row['active_id']);
        } elseif ($this->doesAccessCodeInSessionExists()) {
            $this->unsetAccessCodeInSession();
        }
    }
    
    public function saveToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        $submitted = ($this->isSubmitted()) ? 1 : 0;
        if ($this->active_id > 0) {
            $affectedRows = $ilDB->update(
                'tst_active',
                array(
                    'lastindex' => array('integer', $this->getLastSequence()),
                    'tries' => array('integer', $this->getPass()),
                    'submitted' => array('integer', $submitted),
                    'submittimestamp' => array('timestamp', (strlen($this->getSubmittedTimestamp())) ? $this->getSubmittedTimestamp() : null),
                    'tstamp' => array('integer', time()-10),
                    'taxfilter' => array('text', serialize($this->getQuestionSetFilterSelection()->getTaxonomySelection())),
                    'answerstatusfilter' => array('text', $this->getQuestionSetFilterSelection()->getAnswerStatusSelection())
                ),
                array(
                    'active_id' => array('integer', $this->getActiveId())
                )
            );

            // update learning progress
            include_once("./Modules/Test/classes/class.ilObjTestAccess.php");
            include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
            ilLPStatusWrapper::_updateStatus(
                ilObjTestAccess::_lookupObjIdForTestId($this->getTestId()),
                ilObjTestAccess::_getParticipantId($this->getActiveId())
            );
        } else {
            if (!$this->activeIDExists($this->getUserId(), $this->getTestId())) {
                $anonymous_id = ($this->getAnonymousId()) ? $this->getAnonymousId() : null;

                $next_id = $ilDB->nextId('tst_active');
                $affectedRows = $ilDB->insert(
                    'tst_active',
                    array(
                        'active_id' => array('integer', $next_id),
                        'user_fi' => array('integer', $this->getUserId()),
                        'anonymous_id' => array('text', $anonymous_id),
                        'test_fi' => array('integer', $this->getTestId()),
                        'lastindex' => array('integer', $this->getLastSequence()),
                        'tries' => array('integer', $this->getPass()),
                        'submitted' => array('integer', $submitted),
                        'submittimestamp' => array('timestamp', (strlen($this->getSubmittedTimestamp())) ? $this->getSubmittedTimestamp() : null),
                        'tstamp' => array('integer', time()-10),
                        'taxfilter' => array('text', serialize($this->getQuestionSetFilterSelection()->getTaxonomySelection())),
                        'answerstatusfilter' => array('text', $this->getQuestionSetFilterSelection()->getAnswerStatusSelection())
                    )
                );
                $this->active_id = $next_id;

                // update learning progress
                include_once("./Modules/Test/classes/class.ilObjTestAccess.php");
                include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
                ilLPStatusWrapper::_updateStatus(
                    ilObjTestAccess::_lookupObjIdForTestId($this->getTestId()),
                    $this->getUserId()
                );
            }
        }
        
        include_once("./Services/Tracking/classes/class.ilLearningProgress.php");
        ilLearningProgress::_tracProgress(
            $this->getUserId(),
            ilObjTestAccess::_lookupObjIdForTestId($this->getTestId()),
            $this->getRefId(),
            'tst'
        );
    }
    
    public function getCurrentQuestionId()
    {
        return $this->getLastSequence();
    }

    public function setCurrentQuestionId($currentQuestionId)
    {
        $this->setLastSequence((int) $currentQuestionId);
    }
}
