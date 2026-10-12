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

use PHPUnit\Framework\TestCase;

class ilIniFileTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = tempnam(sys_get_temp_dir(), 'ini');
        unlink($this->path);
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    /**
     * ilIniFilesPopulatedObjective fills a new ini file by assigning $GROUPS
     */
    public function testAssignedGroupsAreWritten(): void
    {
        $ini = new ilIniFile($this->path);
        $ini->GROUPS = [
            'server' => ['http_path' => 'https://ilias.example.org', 'timezone' => 'Europe/Zurich'],
            'clients' => ['default' => 'default'],
        ];
        $this->assertTrue($ini->write());

        $written = new ilIniFile($this->path);
        $this->assertSame('https://ilias.example.org', $written->readVariable('server', 'http_path'));
        $this->assertSame('default', $written->readVariable('clients', 'default'));
        $this->assertTrue($written->setVariable('server', 'timezone', 'UTC'));
    }

    public function testGroupsAndVariablesRemovedFromGroupsAreNotWritten(): void
    {
        $ini = new ilIniFile($this->path);
        $ini->GROUPS = ['a' => ['x' => '1', 'y' => '2'], 'b' => ['z' => '3']];
        $ini->write();

        $ini = new ilIniFile($this->path);
        unset($ini->GROUPS['b'], $ini->GROUPS['a']['y']);
        $ini->write();

        $written = new ilIniFile($this->path);
        $this->assertSame(['a'], $written->readGroups());
        $this->assertSame(['x' => '1'], $written->readGroup('a'));
    }

    public function testSetVariableIsWritten(): void
    {
        $ini = new ilIniFile($this->path);
        $ini->addGroup('log');
        $ini->setVariable('log', 'level', 'debug');
        $ini->write();

        $this->assertSame('debug', (new ilIniFile($this->path))->readVariable('log', 'level'));
    }
}
