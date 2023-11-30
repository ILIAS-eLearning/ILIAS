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
class ilAssQuestionHintRequestGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionHintRequestGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $assQuestionGUI = $this->createMock(assQuestionGUI::class);
        $assQuestionGUI->object = $this->createMock(assQuestion::class);

        $this->object = new ilAssQuestionHintRequestGUI(null, '', $assQuestionGUI, null);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionHintRequestGUI::class, $this->object);
    }
}