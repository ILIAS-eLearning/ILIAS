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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

class ilQTIParserTest extends TestCase
{
    private ?Container $dic = null;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTIParser::class, new ilQTIParser('dummy import dir', 'dummy xml file'));
    }

    public function testSetTestObject(): void
    {
        $id = 8098;
        $test = $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock();
        $test->expects(self::once())->method('getId')->willReturn($id);
        $instance = new ilQTIParser('dummy import dir', 'dummy xml file');
        $instance->setTestObject($test);
        $this->assertEquals($test, $instance->tst_object);
        $this->assertEquals($id, $instance->tst_id);
    }

    protected function setUp(): void
    {
        global $DIC;
        $this->dic = is_object($DIC) ? clone $DIC : $DIC;
        $DIC = new Container();
        $DIC['ilUser'] = $this->createMock(ilObjUser::class);
        $DIC['lng'] = $this->createMock(ilLanguage::class);
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic;
    }
}
