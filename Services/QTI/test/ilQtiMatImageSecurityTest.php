<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQtiMatImageSecurityTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQtiMatImageSecurity::class, new ilQtiMatImageSecurity($this->image()));
    }

    private function image() : ilQTIMatimage
    {
        $image = $this->getMockBuilder(ilQTIMatimage::class)->disableOriginalConstructor()->getMock();
        $image->expects(self::exactly(2))->method('getRawContent')->willReturn('Ayayay');

        return $image;
    }
}
