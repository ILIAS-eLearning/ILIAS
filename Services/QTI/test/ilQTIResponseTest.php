<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResponseTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponse::class, new ilQTIResponse());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIResponse();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    /**
     * @dataProvider rtimings
     */
    public function testSetGetRtiming(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponse();
        $instance->setRtiming($input);
        $this->assertEquals($expected, $instance->getRtiming());
    }

    /**
     * @dataProvider numtypes
     */
    public function testSetGetNumtype(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponse();
        $instance->setNumtype($input);
        $this->assertEquals($expected, $instance->getNumtype());
    }

    public function rtimings() : array
    {
        class_exists(ilQTIResponse::class); // Force autoload to define the constants.

        return [
            ['no', RTIMING_NO],
            ['1', RTIMING_NO],
            ['yes', RTIMING_YES],
            ['2', RTIMING_YES],
            ['Random input.', null],
        ];
    }

    public function numtypes() : array
    {
        class_exists(ilQTIResponse::class); // Force autoload to define the constants.
        return [
            ['integer', NUMTYPE_INTEGER],
            ['1', NUMTYPE_INTEGER],
            ['decimal', NUMTYPE_DECIMAL],
            ['2', NUMTYPE_DECIMAL],
            ['scientific', NUMTYPE_SCIENTIFIC],
            ['3', NUMTYPE_SCIENTIFIC],
            ['Random input.', null],
        ];
    }
}
