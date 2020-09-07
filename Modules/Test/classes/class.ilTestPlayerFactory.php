<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for test player
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestPlayerFactory
{
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
    public function __construct(ilObjTest $testOBJ)
    {
        $this->testOBJ = $testOBJ;
    }
    
    /**
     * creates and returns an instance of a player gui
     * that corresponds to the current test mode
     *
     * @return ilTestPlayerAbstractGUI
     */
    public function getPlayerGUI()
    {
        switch ($this->testOBJ->getQuestionSetType()) {
            case ilObjTest::QUESTION_SET_TYPE_FIXED:

                require_once 'Modules/Test/classes/class.ilTestPlayerFixedQuestionSetGUI.php';
                return new ilTestPlayerFixedQuestionSetGUI($this->testOBJ);

            case ilObjTest::QUESTION_SET_TYPE_RANDOM:
                
                require_once 'Modules/Test/classes/class.ilTestPlayerRandomQuestionSetGUI.php';
                return new ilTestPlayerRandomQuestionSetGUI($this->testOBJ);
                
            case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:
                
                require_once 'Modules/Test/classes/class.ilTestPlayerDynamicQuestionSetGUI.php';
                return new ilTestPlayerDynamicQuestionSetGUI($this->testOBJ);
        }
    }
}
