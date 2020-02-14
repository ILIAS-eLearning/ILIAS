<?php
/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilCtrlStructureReaderTest extends TestCase
{
    protected function setUp() : void
    {
        $this->reader = new ilCtrlStructureReader();
    }

    public function testSmoke()
    {
        $this->assertInstanceOf(ilCtrlStructureReader::class, $this->reader);
    }
}
