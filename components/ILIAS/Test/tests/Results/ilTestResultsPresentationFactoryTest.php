<?php

namespace Results;

use ilTestBaseTestCase;
use ilTestResultsPresentationFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;

class ilTestResultsPresentationFactoryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestResultsPresentationFactory = new ilTestResultsPresentationFactory(
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(Refinery::class),
            $this->createMock(DataFactory::class),
            $this->createMock(HTTPService::class),
            $this->createMock(ilLanguage::class),
        );
        $this->assertInstanceOf(ilTestResultsPresentationFactory::class, $ilTestResultsPresentationFactory);
    }

    public function testGetPassResultsPresentationTable(): void
    {
        $this->markTestSkipped();
    }
}