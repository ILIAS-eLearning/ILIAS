<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

/**
 * ilBaseWorkflowTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * workflows/class.BaseWorkflow
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilBaseWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        global $ilSetting;

        if ($ilSetting != null) {
            $ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            $ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
}
