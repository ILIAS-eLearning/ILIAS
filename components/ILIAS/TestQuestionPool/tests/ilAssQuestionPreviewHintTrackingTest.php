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
class ilAssQuestionPreviewHintTrackingTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionPreviewHintTracking $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ilAssQuestionPreviewHintTracking($this->createMock(ilDBInterface::class), $this->createMock(ilAssQuestionPreviewSession::class));
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionPreviewHintTracking::class, $this->object);
    }
}