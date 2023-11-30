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
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilAssQuestionHintsTableGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionHintsTableGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $questionOBJ = $this->createMock(assQuestion::class);
        $questionHintList = $this->createMock(ilAssQuestionHintList::class);
        $parentGUI = $this->createMock(ilAssQuestionHintAbstractGUI::class);
        $parentCmd = '';
        $tableMode = ilAssQuestionHintsTableGUI::TBL_MODE_TESTOUTPUT;
        $hintOrderingClipboard = $this->createMock(ilAssQuestionHintsOrderingClipboard::class);

        $this->object = new ilAssQuestionHintsTableGUI($questionOBJ, $questionHintList, $parentGUI, $parentCmd, $tableMode, $hintOrderingClipboard);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionHintsTableGUI::class, $this->object);
    }
}