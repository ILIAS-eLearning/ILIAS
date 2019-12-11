<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for test sequence
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestSequenceFactory
{
    /**
     * singleton instances of test sequences
     *
     * @var array
     */
    private $testSequences = array();
    
    /**
     * global ilDBInterface object instance
     *
     * @var ilDBInterface
     */
    private $db = null;
    
    /**
     * global ilLanguage object instance
     *
     * @var ilLanguage
     */
    private $lng = null;
    
    /**
     * global ilPluginAdmin object instance
     *
     * @var ilPluginAdmin
     */
    private $pluginAdmin = null;
    
    /**
     * object instance of current test
     *
     * @var ilObjTest
     */
    private $testOBJ = null;
    
    /**
     * constructor
     *
     * @param ilObjTest $testOBJ
     */
    public function __construct(ilDBInterface $db, ilLanguage $lng, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
    {
        $this->db = $db;
        $this->lng = $lng;
        $this->pluginAdmin = $pluginAdmin;
        $this->testOBJ = $testOBJ;
    }
    
    /**
     * creates and returns an instance of a test sequence
     * that corresponds to the current test mode and the pass stored in test session
     *
     * @param ilTestSession|ilTestSessionDynamicQuestionSet $testSession
     * @return ilTestSequence|ilTestSequenceDynamicQuestionSet
     */
    public function getSequenceByTestSession($testSession)
    {
        return $this->getSequenceByActiveIdAndPass($testSession->getActiveId(), $testSession->getPass());
    }
    
    /**
     * creates and returns an instance of a test sequence
     * that corresponds to the current test mode and given active/pass
     *
     * @param integer $activeId
     * @param integer $pass
     * @return ilTestSequenceFixedQuestionSet|ilTestSequenceRandomQuestionSet|ilTestSequenceDynamicQuestionSet
     */
    public function getSequenceByActiveIdAndPass($activeId, $pass)
    {
        if ($this->testSequences[$activeId][$pass] === null) {
            switch ($this->testOBJ->getQuestionSetType()) {
                case ilObjTest::QUESTION_SET_TYPE_FIXED:

                    require_once 'Modules/Test/classes/class.ilTestSequenceFixedQuestionSet.php';
                    $this->testSequences[$activeId][$pass] = new ilTestSequenceFixedQuestionSet(
                        $activeId,
                        $pass,
                        $this->testOBJ->isRandomTest()
                    );
                    break;

                case ilObjTest::QUESTION_SET_TYPE_RANDOM:

                    require_once 'Modules/Test/classes/class.ilTestSequenceRandomQuestionSet.php';
                    $this->testSequences[$activeId][$pass] = new ilTestSequenceRandomQuestionSet(
                        $activeId,
                        $pass,
                        $this->testOBJ->isRandomTest()
                    );
                    break;

                case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:

                    require_once 'Modules/Test/classes/class.ilTestSequenceDynamicQuestionSet.php';
                    require_once 'Modules/Test/classes/class.ilTestDynamicQuestionSet.php';
                    $questionSet = new ilTestDynamicQuestionSet(
                        $this->db,
                        $this->lng,
                        $this->pluginAdmin,
                        $this->testOBJ
                    );
                    $this->testSequences[$activeId][$pass] = new ilTestSequenceDynamicQuestionSet(
                        $this->db,
                        $questionSet,
                        $activeId
                    );
                    
                    #$this->testSequence->setPreventCheckedQuestionsFromComingUpEnabled(
                    #	$this->testOBJ->isInstantFeedbackAnswerFixationEnabled()
                    #); // checked questions now has to come up any time, so they can be set to unchecked right at this moment
                    
                    break;
            }
        }

        return $this->testSequences[$activeId][$pass];
    }
}
