<?php declare(strict_types=1);

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

class ilQTIParserTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIParser::class, new ilQTIParser('dummy xml file'));
    }

    public function testSetGetQuestionSetType() : void
    {
        $instance = new ilQTIParser('dummy xml file');
        $instance->setQuestionSetType('Some input.');
        $this->assertEquals('Some input.', $instance->getQuestionSetType());
    }

    public function testSetTestObject() : void
    {
        $id = 8098;
        $test = $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock();
        $test->expects(self::once())->method('getId')->willReturn($id);
        $instance = new ilQTIParser('dummy xml file');
        $instance->setTestObject($test);
        $this->assertEquals($test, $instance->tst_object);
        $this->assertEquals($id, $instance->tst_id);
    }

    protected function setup() : void
    {
        $GLOBALS['DIC'] = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $GLOBALS['DIC']->expects(self::any())->method('isDependencyAvailable')->with('language')->willReturn(false);
    }

    protected function tearDown() : void
    {
        unset($GLOBALS['DIC']);
    }
}
