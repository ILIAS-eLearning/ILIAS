<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilLSLPStub extends ilLSLP
{
    public function __construct()
    {
    }
}

class ilLSLPTest extends TestCase
{
    public function testCreateObject() : void
    {
        $obj = new ilLSLPStub();

        $this->assertInstanceOf(ilLSLP::class, $obj);
    }

    public function testGetDefaultModes() : void
    {
        $obj = new ilLSLPStub();
        $result = $obj->getDefaultModes(true);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals(ilLPObjSettings::LP_MODE_DEACTIVATED, array_pop($result));
    }

    public function testGetDefaultMode() : void
    {
        $obj = new ilLSLPStub();
        $result = $obj->getDefaultMode();

        $this->assertEquals(ilLPObjSettings::LP_MODE_DEACTIVATED, $result);
    }

    public function testGetValidModes() : void
    {
        $obj = new ilLSLPStub();
        $result = $obj->getValidModes();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals(ilLPObjSettings::LP_MODE_DEACTIVATED, $result[0]);
        $this->assertEquals(ilLPObjSettings::LP_MODE_COLLECTION, $result[1]);
    }
}
