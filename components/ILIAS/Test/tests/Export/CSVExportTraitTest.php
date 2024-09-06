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

declare(strict_types=1);

namespace Export;

use ILIAS\Test\Export\CSVExportTrait;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class CSVExportTraitTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider provideRows
     * @throws ReflectionException|Exception
     */
    public function testProcessCSVRow(array $input, array $output): void
    {
        $csv_export_trait = $this->createTraitInstanceOf(CSVExportTrait::class);
        $this->assertSame($output, self::callMethod($csv_export_trait, 'processCSVRow', [$input['row'], $input['quote_all'], $input['separator']]));
    }

    public static function provideRows(): array
    {
        return [
            '1' => [
                [
                    'row' => [
                    'normalEntry',
                    'entry;with;separator',
                    'entry with "',
                    'entry with " and ;'
                    ],
                    'quote_all' => true,
                    'separator' => ';'
                ],
                [
                    '"normalEntry"',
                    '"entry;with;separator"',
                    '"entry with """',
                    '"entry with "" and ;"'
                ]
            ],
            '2' => [
                [
                    'row' => [
                    'normalEntry',
                    'entry;without;separator',
                    'entry with "',
                    'entry with " and :',
                    'entry with :'
                    ],
                    'quote_all' => false,
                    'separator' => ':'
                ],
                [
                    'normalEntry',
                    'entry;without;separator',
                    '"entry with """',
                    '"entry with "" and :"',
                    '"entry with :"'
                ]
            ],
            '3' => [
                [
                    'row' => [
                        'normalEntry',
                        'entry;without;separator',
                        'entry with "',
                        'entry with " and :',
                        'entry with :'
                    ],
                    'quote_all' => true,
                    'separator' => ':'
                ],
                [
                    '"normalEntry"',
                    '"entry;without;separator"',
                    '"entry with """',
                    '"entry with "" and :"',
                    '"entry with :"'
                ]
            ],
            '4' => [
                [
                    'row' => [
                        'normalEntry',
                        'entry;with;separator',
                        'entry with "',
                        'entry with " and ;'
                    ],
                    'quote_all' => false,
                    'separator' => ';'
                ],
                [
                    'normalEntry',
                    '"entry;with;separator"',
                    '"entry with """',
                    '"entry with "" and ;"'
                ]
            ]
        ];
    }
}
