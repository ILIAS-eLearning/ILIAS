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
        if (!isset($this->testSequences[$activeId]) || $this->testSequences[$activeId][$pass] === null) {
            if ($this->testOBJ->isFixedTest()) {
                $this->testSequences[$activeId][$pass] = new ilTestSequenceFixedQuestionSet(
                    $activeId,
                    $pass,
                    $this->testOBJ->isRandomTest()
                );
            }

            if ($this->testOBJ->isRandomTest()) {
                $this->testSequences[$activeId][$pass] = new ilTestSequenceRandomQuestionSet(
                    $activeId,
                    $pass,
                    $this->testOBJ->isRandomTest()
                );
            }
            
            if ($this->testOBJ->isDynamicTest()) {
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
            }
        }

        return $this->testSequences[$activeId][$pass];
    }
}
