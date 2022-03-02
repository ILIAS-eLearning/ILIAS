<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResponseLabelTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponseLabel::class, new ilQTIResponseLabel());
    }

    /**
     * @dataProvider rshuffles
     */
    public function testSetGetRshuffle($input, $expected) : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setRshuffle($input);
        $this->assertEquals($expected, $instance->getRshuffle());
    }

    /**
     * @dataProvider areas
     */
    public function testSetGetRarea($input, $expected) : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setRarea($input);
        $this->assertEquals($expected, $instance->getRarea());
    }

    /**
     * @dataProvider rranges
     */
    public function testSetGetRrange($input, $expected) : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setRrange($input);
        $this->assertEquals($expected, $instance->getRshuffle());
    }

    public function testSetGetLabelrefid() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setLabelrefid('Some input.');
        $this->assertEquals('Some input.', $instance->getLabelrefid());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetMatchGroup() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setMatchGroup('Some input.');
        $this->assertEquals('Some input.', $instance->getMatchGroup());
    }

    public function testSetGetMatchMax() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setMatchMax('Some input.');
        $this->assertEquals('Some input.', $instance->getMatchMax());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function rshuffles() : array
    {
        class_exists(ilQTIResponseLabel::class); // Force autoload to define the constants.

        return [
            ['1', RSHUFFLE_NO],
            ['no', RSHUFFLE_NO],
            ['2', RSHUFFLE_YES],
            ['yes', RSHUFFLE_YES],
            ['Random input', null],
        ];
    }

    public function areas() : array
    {
        class_exists(ilQTIResponseLabel::class); // Force autoload to define the constants.
        return [
            ['1', RAREA_ELLIPSE],
            ['ellipse', RAREA_ELLIPSE],
            ['2', RAREA_RECTANGLE],
            ['rectangle', RAREA_RECTANGLE],
            ['3', RAREA_BOUNDED],
            ['bounded', RAREA_BOUNDED],
            ['Random input', null],
        ];
    }

    public function rranges() : array
    {
        class_exists(ilQTIResponseLabel::class); // Force autoload to define the constants.
        return [
            ['1', RRANGE_EXACT],
            ['excact', RRANGE_EXACT],
            ['2', RRANGE_RANGE],
            ['range', RRANGE_RANGE],
        ];
    }
}
