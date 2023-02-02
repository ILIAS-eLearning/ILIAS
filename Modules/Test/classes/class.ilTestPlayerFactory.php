<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
