<?php

namespace Results;

use ilTestBaseTestCase;
use ilTestResultsFactory;
use ilTestShuffler;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class ilTestResultsFactoryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestResultsFactoryTest = new ilTestResultsFactory(
            $this->createMock(ilTestShuffler::class),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class)
        );
        $this->assertInstanceOf(ilTestResultsFactory::class, $ilTestResultsFactoryTest);
    }
}