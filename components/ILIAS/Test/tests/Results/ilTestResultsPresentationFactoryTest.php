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

namespace ILIAS\Tests\Tests\Results;

use ilTestBaseTestCase;
use ilTestPassResult;
use ilTestPassResultsTable;
use ilTestResultsPresentationFactory;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class ilTestResultsPresentationFactoryTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_test_results_presentation_factory = $this->createInstanceOf(ilTestResultsPresentationFactory::class);
        $this->assertInstanceOf(ilTestResultsPresentationFactory::class, $il_test_results_presentation_factory);
    }

    /**
     * @dataProvider getPassResultsPresentationTableDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetPassResultsPresentationTable(?string $IO): void
    {
        $this->markTestSkipped();

        $il_test_results_presentation_factory = $this->createInstanceOf(ilTestResultsPresentationFactory::class);
        $il_test_pass_result = $this->createInstanceOf(ilTestPassResult::class);

        if ($IO === null) {
            $test_pass_results_table = $il_test_results_presentation_factory->getPassResultsPresentationTable(
                $il_test_pass_result
            );
        } else {
            $test_pass_results_table = $il_test_results_presentation_factory->getPassResultsPresentationTable(
                $il_test_pass_result,
                $IO
            );
        }

        $this->assertInstanceOf(ilTestPassResultsTable::class, $test_pass_results_table);
        $this->assertEquals($il_test_pass_result, $test_pass_results_table);
        $this->assertEquals($IO ?? '', $test_pass_results_table, self::getNonPublicPropertyValue($test_pass_results_table, 'title'));
    }

    public static function getPassResultsPresentationTableDataProvider(): array
    {
        return [
            'default' => [null],
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING'],
            'STRING' => ['STRING']
        ];
    }
}
