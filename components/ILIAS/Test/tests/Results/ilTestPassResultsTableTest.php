<?php

namespace Results;

use ilTestBaseTestCase;
use ilTestPassResultsTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Data\Factory as DataFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class ilTestPassResultsTableTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestPassResultsTable = new ilTestPassResultsTable(
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(Refinery::class),
            $this->createConfiguredMock(HTTPService::class, [
                'request' => $this->createConfiguredMock(RequestInterface::class, [
                    'getUri' => $this->createConfiguredMock(UriInterface::class, [
                        '__toString' => '',
                    ]),
                ]),
            ]),
            $this->createMock(DataFactory::class),
            $this->createMock(\ilLanguage::class),
            $this->createMock(\ilTestPassResult::class),
            '',
        );
        $this->assertInstanceOf(ilTestPassResultsTable::class, $ilTestPassResultsTable);
    }

    public function testRender(): void
    {
        $this->markTestSkipped();
    }

    public function testApplyControls(): void
    {
        $this->markTestSkipped();
    }

    public function testGetViewControlsParameter(): void
    {
        $this->markTestSkipped();
    }

    public function testGetViewControls(): void
    {
        $this->markTestSkipped();
    }

    public function testGetMapping(): void
    {
        $this->markTestSkipped();
    }
}