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

namespace ILIAS\DI;

use PHPUnit\Framework\TestCase;

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
