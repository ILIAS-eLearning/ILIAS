<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIConditionvarTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIConditionvar::class, new ilQTIConditionvar());
    }
}
