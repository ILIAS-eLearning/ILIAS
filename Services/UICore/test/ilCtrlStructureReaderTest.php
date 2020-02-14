<?php
/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilCtrlStructureReaderTest extends TestCase
{
    protected function setUp() : void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->reader = (new ilCtrlStructureReader())
            ->withDB($this->db);
    }

    public function testSmoke()
    {
        $this->assertInstanceOf(ilCtrlStructureReader::class, $this->reader);
    }

    public function testReadSmoke()
    {
        $dir = __DIR__ . "/test_dir/";
        $result = $this->reader->read($dir);
        $this->assertTrue($result === false || is_null($result));
    }
}
