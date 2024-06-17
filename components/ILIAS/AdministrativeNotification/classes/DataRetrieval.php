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

namespace ILIAS\AdministrativeNotification;

use DateTimeImmutable;
use ilADNNotification;
use ilDatePresentation;
use ilDateTime;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table as I;

/**
 *
 */
class DataRetrieval implements I\DataRetrieval
{
    private \ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC['lng'];
    }

    public function getRows(
        I\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $records = $this->getRecords($order);
        foreach ($records as $idx => $record) {
            $row_id = (string) $record['id'];

            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    protected function getRecords(Order $order): array
    {
        $records = ilADNNotification::getArray();

        // populate with additional data
        array_walk($records, function (&$record) {
            $record['languages'] = $this->getLanguagesTextForNotification($record);
            $record['type'] = $this->lng->txt("msg_type_" . $record['type']);
            $record['event_start'] = $this->formatDate($record['event_start']);
            $record['event_end'] = $this->formatDate($record['event_end']);
            $record['display_start'] = $this->formatDate($record['display_start']);
            $record['display_end'] = $this->formatDate($record['display_end']);

            if (!$record['permanent']) {
                $record['type_during_event'] = $this->lng->txt("msg_type_" . $record['type_during_event']);
            } else {
                $record['type_during_event'] = '';
            }
        });

        // sort
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        usort($records, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
        if ($order_direction === 'DESC') {
            $records = array_reverse($records);
        }

        return $records;
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count(ilADNNotification::getArray());
    }

    protected function formatDate(DateTimeImmutable $timestamp): string
    {
        return ilDatePresentation::formatDate(new ilDateTime($timestamp->getTimestamp(), IL_CAL_UNIX));
    }

    protected function getLanguagesTextForNotification(array $record): string
    {
        $has_language_limitation = $record['has_language_limitation'];
        $limited_to_languages = $record['limited_to_languages'];
        // text is all by default
        $languages_text = $this->lng->txt("all");
        if ($has_language_limitation) {
            // text is none in case the notification has a language limitation but no languages are specified
            $languages_text = $this->lng->txt("none");
            if (!empty($limited_to_languages)) {
                $this->lng->loadLanguageModule("meta");
                // text is comma separated list of languages if the notification has a language limitation
                // and the languages have been specified
                $languages_text = implode(
                    ', ',
                    array_map(
                        function (string $lng_code): string {
                            return $this->lng->txt("meta_l_" . $lng_code);
                        },
                        $limited_to_languages
                    )
                );
            }
        }
        return $languages_text;
    }
}
