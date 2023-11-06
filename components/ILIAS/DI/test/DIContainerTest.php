<?php

namespace ILIAS\DI;

use PHPUnit\Framework\TestCase;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class DIContainerTest
 */
class DIContainerTest extends TestCase
{
    /**
     * @var Container
     */
    protected $DIC;

    protected function setUp(): void
    {
        $this->DIC = new Container();
    }

    public function testIsDependencyAvailableIfNotAvailable(): void
    {
        $this->assertFalse($this->DIC->isDependencyAvailable("ctrl"));
    }

    public function testIsDependencyAvailableIfAvailable(): void
    {
        $this->DIC["ilCtrl"] = $this->getMockBuilder(\ilCtrl::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->assertTrue($this->DIC->isDependencyAvailable("ctrl"));
    }
}
