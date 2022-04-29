<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/ilCtrlPathTestBase.php';

/**
 * Class ilCtrlExistingPathTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlExistingPathTest extends ilCtrlPathTestBase
{
    public function testExistingPathWithString() : void
    {
        $path = new ilCtrlExistingPath($this->structure, 'foo');
        $this->assertEquals('foo', $path->getCidPath());
    }

    public function testExistingPathWithEmptyString() : void
    {
        $path = new ilCtrlExistingPath($this->structure, '');
        $this->assertNull($path->getCidPath());
    }
}
