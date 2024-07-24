<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace Results;

use ilTestBaseTestCase;
use ilTestPassResult;
use ilTestPassResultsTable;
use ilTestResultsPresentationFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class ilTestResultsPresentationFactoryTest extends ilTestBaseTestCase
{
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private Refinery $refinery;
    private DataFactory $data_factory;
    private HTTPService $http_service;
    private ilLanguage $language;
    private ilTestPassResult $ilTestPassResult;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->ui_factory = $this->createMock(UIFactory::class);
        $this->ui_renderer = $this->createMock(UIRenderer::class);
        $this->refinery = $this->createMock(Refinery::class);
        $this->data_factory = $this->createMock(DataFactory::class);
        $this->http_service = $this->createMock(HTTPService::class);
        $this->language = $this->createMock(ilLanguage::class);
        $this->ilTestPassResult = $this->createMock(ilTestPassResult::class);
    }

    public function testConstruct(): void
    {
        $ilTestResultsPresentationFactory = new ilTestResultsPresentationFactory(
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->data_factory,
            $this->http_service,
            $this->language
        );

        $this->assertInstanceOf(ilTestResultsPresentationFactory::class, $ilTestResultsPresentationFactory);
    }

    /**
     * @dataProvider getPassResultsPresentationTableDataProvider
     * @throws ReflectionException
     */
    public function testGetPassResultsPresentationTable(?string $IO): void
    {
        $this->markTestSkipped();
        $testResultsPresentationFactory = new ilTestResultsPresentationFactory(
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->data_factory,
            $this->http_service,
            $this->language
        );

        if (is_null($IO)) {
            $testPassResultsTable = $testResultsPresentationFactory->getPassResultsPresentationTable(
                $this->ilTestPassResult
            );
        } else {
            $testPassResultsTable = $testResultsPresentationFactory->getPassResultsPresentationTable(
                $this->ilTestPassResult,
                $IO
            );
        }

        $this->assertInstanceOf(ilTestPassResultsTable::class, $testPassResultsTable);
        $this->assertEquals($this->ilTestPassResult, $testPassResultsTable);
        $this->assertEquals($IO ?? '', $testPassResultsTable, self::getNonPublicPropertyValue($testPassResultsTable, 'title'));
    }

    public static function getPassResultsPresentationTableDataProvider(): array
    {
        return [
            'default' => [null],
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }
}
