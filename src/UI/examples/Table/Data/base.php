<?php

declare(strict_types=1);

use ILIAS\UI\Component\Table\Data\Data\Data as DataInterface;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Settings\Settings;
use ILIAS\UI\Component\Table\Data\Settings\Sort\SortField;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Column;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Data;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher\AbstractDataFetcher;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Row\PropertyRowData;

/**
 * @return string
 */
function base() : string
{
    global $DIC;

    $DIC->ctrl()->saveParameterByClass(ilSystemStyleDocumentationGUI::class, "node_id");

    $action_url = $DIC->ctrl()->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class, "", "", false, false);

    $table = $DIC->ui()->factory()->table()->data("example_datatable_actions", $action_url, "Example data table with actions", [
        new Column($DIC, "column1", "Column 1"),
        new Column($DIC, "column2", "Column 2"),
        new Column($DIC, "column3", "Column 3")
    ], new BaseExampleDataFetcher($DIC));

    return $DIC->ui()->renderer()->render($table);
}

/**
 * Class BaseExampleDataFetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class BaseExampleDataFetcher extends AbstractDataFetcher
{

    /**
     * @inheritDoc
     */
    public function fetchData(Settings $settings) : DataInterface
    {
        $data = array_map(function (int $index) : stdClass {
            return (object) [
                "column1" => $index,
                "column2" => "text $index",
                "column3" => ($index % 2 === 0 ? "true" : "false")
            ];
        }, range(0, 25));

        $data = array_filter($data, function (stdClass $data) use ($settings): bool {
            $match = true;

            foreach ($settings->getFilterFieldValues() as $key => $value) {
                if (!empty($value)) {
                    switch (true) {
                        case is_array($value):
                            $match = in_array($data->{$key}, $value);
                            break;

                        case is_integer($data->{$key}):
                        case is_float($data->{$key}):
                            $match = ($data->{$key} === intval($value));
                            break;

                        case is_string($data->{$key}):
                            $match = (stripos($data->{$key}, $value) !== false);
                            break;

                        default:
                            $match = ($data->{$key} === $value);
                            break;
                    }

                    if (!$match) {
                        break;
                    }
                }
            }

            return $match;
        });

        usort($data, function (stdClass $o1, stdClass $o2) use ($settings): int {
            foreach ($settings->getSortFields() as $sort_field) {
                $s1 = strval($o1->{$sort_field->getSortField()});
                $s2 = strval($o2->{$sort_field->getSortField()});

                $i = strnatcmp($s1, $s2);

                if ($sort_field->getSortFieldDirection() === SortField::SORT_DIRECTION_DOWN) {
                    $i *= -1;
                }

                if ($i !== 0) {
                    return $i;
                }
            }

            return 0;
        });

        $max_count = count($data);

        $data = array_slice($data, $settings->getOffset(), $settings->getRowsCount());

        $data = array_map(function (stdClass $row) : RowData {
            return new PropertyRowData(strval($row->column1), $row);
        }, $data);

        return new Data($data, $max_count);
    }
}
