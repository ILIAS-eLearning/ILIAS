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

declare(strict_types=1);

require_once __DIR__ . '/ilCtrlPathTestBase.php';

/**
 * Class ilCtrlExistingPathTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlExistingPathTest extends ilCtrlPathTestBase
{
    public function testExistingPathWithString(): void
    {
        $path = new ilCtrlExistingPath($this->structure, 'foo');
        $this->assertEquals('foo', $path->getCidPath());
    }

    public function testExistingPathWithEmptyString(): void
    {
        $path = new ilCtrlExistingPath($this->structure, '');
        $this->assertNull($path->getCidPath());
    }
}
