<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIPresentationMaterialTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIPresentationMaterial::class, new ilQTIPresentationMaterial());
    }
}
