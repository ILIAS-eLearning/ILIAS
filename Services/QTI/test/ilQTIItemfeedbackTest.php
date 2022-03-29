<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIItemfeedbackTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIItemfeedback::class, new ilQTIItemfeedback());
    }

    /**
     * @depends testConstruct
     * @dataProvider views
     */
    public function testSetGetView($input, $expected) : void
    {
        $instance = new ilQTIItemfeedback();
        $instance->setView($input);
        $this->assertEquals($expected, $instance->getView());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIItemfeedback();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetTitle() : void
    {
        $instance = new ilQTIItemfeedback();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function views() : array
    {
        class_exists(ilQTIItemfeedback::class); // Force autoload to define the constants.
        return [
            ['1', VIEW_ALL],
            ['all', VIEW_ALL],
            ['2', VIEW_ADMINISTRATOR],
            ['administrator', VIEW_ADMINISTRATOR],
            ['3', VIEW_ADMINAUTHORITY],
            ['adminauthority', VIEW_ADMINAUTHORITY],
            ['4', VIEW_ASSESSOR],
            ['assessor', VIEW_ASSESSOR],
            ['5', VIEW_AUTHOR],
            ['author', VIEW_AUTHOR],
            ['6', VIEW_CANDIDATE],
            ['candidate', VIEW_CANDIDATE],
            ['7', VIEW_INVIGILATORPROCTOR],
            ['invigilatorproctor', VIEW_INVIGILATORPROCTOR],
            ['8', VIEW_PSYCHOMETRICIAN],
            ['psychometrician', VIEW_PSYCHOMETRICIAN],
            ['9', VIEW_SCORER],
            ['scorer', VIEW_SCORER],
            ['10', VIEW_TUTOR],
            ['tutor', VIEW_TUTOR],
            ['11', null],
            ['Random input.', null],
        ];
    }
}
