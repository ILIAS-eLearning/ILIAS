<?php

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

    private function provideRows(): array
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
