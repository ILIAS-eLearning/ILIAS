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
    private ilTestResultsPresentationFactory $ilTestResultsPresentationFactory;
    protected function setUp(): void
    {
        parent::setUp();

        global $DIC;

        $this->ilTestResultsPresentationFactory = $ilTestResultsPresentationFactory = new ilTestResultsPresentationFactory(
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['refinery'],
            $DIC['DataFactory'],
            $DIC['http'],
            $DIC['lng']
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestResultsPresentationFactory::class, $this->ilTestResultsPresentationFactory);
    }

    /**
     * @dataProvider getPassResultsPresentationTableDataProvider
     * @throws ReflectionException
     */
    public function testGetPassResultsPresentationTable(?string $IO): void
    {
        $this->markTestSkipped();


        if (is_null($IO)) {
            $testPassResultsTable = $this->ilTestResultsPresentationFactory->getPassResultsPresentationTable(
                $this->ilTestPassResult
            );
        } else {
            $testPassResultsTable = $this->ilTestResultsPresentationFactory->getPassResultsPresentationTable(
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
