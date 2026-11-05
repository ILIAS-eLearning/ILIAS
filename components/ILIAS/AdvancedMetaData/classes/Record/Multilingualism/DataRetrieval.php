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

namespace ILIAS\AdvancedMetaData\Record\Multilingualism;

use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;
use ilAdvancedMDRecord;
use ilAdvancedMDRecordTranslations;
use ilLanguage;
use ilObject;
use ILIAS\UI\Component\Table\DataRetrieval as BaseDataRetrieval;

class DataRetrieval implements BaseDataRetrieval
{
    protected ilAdvancedMDRecord $record;
    protected ilLanguage $lng;
    protected ?ilAdvancedMDRecordTranslations $record_translations = null;
    protected ?array $raw_data_cache = null;
    protected ?array $all_ids_cache = null;

    public function __construct(
        ilAdvancedMDRecord $record,
        ilLanguage $lng
    ) {
        $this->record = $record;
        $this->lng = $lng;
    }

    protected function getRecordTranslations(): ilAdvancedMDRecordTranslations
    {
        if ($this->record_translations === null) {
            $this->record_translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId(
                $this->record->getRecordId()
            );
        }
        return $this->record_translations;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $data = $this->getRawData();

        $order_field = array_keys($order->get())[0] ?? TableBuilder::COL_LANGUAGE;
        $order_direction = $order->get()[$order_field] ?? Order::ASC;

        uasort($data, static function ($a, $b) use ($order_field) {
            return strnatcasecmp((string) $a[$order_field], (string) $b[$order_field]);
        });

        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }

        $data = array_slice($data, $range->getStart(), $range->getLength() ?? 50, true);

        foreach ($data as $lang_code => $row) {
            $data_row = $row_builder->buildDataRow($lang_code, $row);

            $data_row = $data_row->withDisabledAction(
                TableBuilder::ACTION_ACTIVATE,
                $row[TableBuilder::COL_ACTIVE]
            );

            $data_row = $data_row->withDisabledAction(
                TableBuilder::ACTION_DEACTIVATE,
                !$row[TableBuilder::COL_ACTIVE] || $row[TableBuilder::COL_DEFAULT]
            );

            $data_row = $data_row->withDisabledAction(
                TableBuilder::ACTION_SET_DEFAULT,
                !$row[TableBuilder::COL_ACTIVE] || $row[TableBuilder::COL_DEFAULT]
            );

            yield $data_row;
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getAllIDs());
    }

    public function getAllIDs(): array
    {
        if ($this->all_ids_cache === null) {
            $languages = ilObject::_getObjectsByType('lng');
            $parsed_languages = [];
            foreach ($languages as $language) {
                $lang = $language['title'];
                $parsed_languages[] = $lang;
            }
            $this->all_ids_cache = $parsed_languages;
        }
        return $this->all_ids_cache;
    }

    protected function getRawData(): array
    {
        if ($this->raw_data_cache === null) {
            $all_languages = $this->getAllIDs();
            $installed_languages = ilLanguage::_getInstalledLanguages();
            $record_translations = $this->getRecordTranslations();
            $default_language = $this->record->getDefaultLanguage();

            $data = [];
            foreach ($all_languages as $lang_code) {
                $data[$lang_code] = [
                    TableBuilder::COL_LANGUAGE => $this->lng->txt('meta_l_' . $lang_code),
                    TableBuilder::COL_DEFAULT => ($lang_code === $default_language),
                    TableBuilder::COL_ACTIVE => $record_translations->isConfigured($lang_code),
                    TableBuilder::COL_INSTALLED => in_array($lang_code, $installed_languages)
                ];
            }
            $this->raw_data_cache = $data;
        }
        return $this->raw_data_cache;
    }
}
