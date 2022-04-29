<?php declare(strict_types=1);

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
