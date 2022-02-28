<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIParserTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIParser::class, new ilQTIParser('dummy xml file'));
    }
}
