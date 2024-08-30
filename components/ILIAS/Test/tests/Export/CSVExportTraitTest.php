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
use ILIAS\Tests\Refinery\TestCase;

class CSVExportTraitTest extends TestCase
{
    use CSVExportTrait;
    /**
     * @dataProvider provideRows
     */
    public function test_pocessCSVRow($row, $quote_all, $separator, $result): void
    {
        $this->assertSame($result, $this->processCSVRow($row, $quote_all, $separator));
    }

    public static function provideRows(): array
    {
        return [
            "dataset 1" => [
                "row" => [
                    "normalEntry",
                    "entry;with;separator",
                    "entry with \"",
                    "entry with \" and ;"
                ],
                "quote_all" => true,
                "separator" => ";",
                "result" => [
                    "\"normalEntry\"",
                    "\"entry;with;separator\"",
                    "\"entry with \"\"\"",
                    "\"entry with \"\" and ;\""
                ]
            ],
            "dataset 2" => [
                "row" => [
                    "normalEntry",
                    "entry;without;separator",
                    "entry with \"",
                    "entry with \" and :",
                    "entry with :"
                ],
                "quote_all" => false,
                "separator" => ":",
                "result" => [
                    "normalEntry",
                    "entry;without;separator",
                    "\"entry with \"\"\"",
                    "\"entry with \"\" and :\"",
                    "\"entry with :\""
                ]
            ],
            "dataset 3" => [
                "row" => [
                    "normalEntry",
                    "entry;without;separator",
                    "entry with \"",
                    "entry with \" and :",
                    "entry with :"
                ],
                "quote_all" => true,
                "separator" => ":",
                "result" => [
                    "\"normalEntry\"",
                    "\"entry;without;separator\"",
                    "\"entry with \"\"\"",
                    "\"entry with \"\" and :\"",
                    "\"entry with :\""
                ]
            ],
            "dataset 4" => [
                "row" => [
                    "normalEntry",
                    "entry;with;separator",
                    "entry with \"",
                    "entry with \" and ;"
                ],
                "quote_all" => false,
                "separator" => ";",
                "result" => [
                    "normalEntry",
                    "\"entry;with;separator\"",
                    "\"entry with \"\"\"",
                    "\"entry with \"\" and ;\""
                ]
            ]
        ];
    }
}
