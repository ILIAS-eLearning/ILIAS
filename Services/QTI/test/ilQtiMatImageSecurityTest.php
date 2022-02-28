<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQtiMatImageSecurityTest extends TestCase
{
    public function testConstruct() : void
    {
        $image = $this->getMockBuilder(ilQTIMatimage::class)->disableOriginalConstructor()->getMock();
        $image->expects(self::exactly(2))->method('getRawContent')->willReturn('Ayayay');
        $this->assertInstanceOf(ilQtiMatImageSecurity::class, new ilQtiMatImageSecurity($image));
    }
}
