<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

interface ilComponentDefinitionProcessorMock1 extends ilComponentDefinitionProcessor
{
};
interface ilComponentDefinitionProcessorMock2 extends ilComponentDefinitionProcessor
{
};

class ilComponentDefinitionReaderTest extends TestCase
{
    protected function setUp() : void
    {
        $this->processor1 = $this->createMock(ilComponentDefinitionProcessorMock1::class);
        $this->processor2 = $this->createMock(ilComponentDefinitionProcessorMock2::class);

        $this->reader = new ilComponentDefinitionReader(
            $this->processor1,
            $this->processor2
        );
    }

    public function testPurge()
    {
        $this->processor1
            ->expects($this->once())
            ->method("purge")
            ->with();
        $this->processor2
            ->expects($this->once())
            ->method("purge")
            ->with();

        $this->reader->purge();
    }
}
