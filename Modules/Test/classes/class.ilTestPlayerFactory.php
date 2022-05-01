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
     */
    public function getPlayerGUI()
    {
        if ($this->testOBJ->isFixedTest()) {
            return new ilTestPlayerFixedQuestionSetGUI($this->testOBJ);
        }
        return new ilTestPlayerRandomQuestionSetGUI($this->testOBJ);
    }
}
