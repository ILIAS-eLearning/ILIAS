<?php

declare(strict_types=1);

namespace AdvancedMetadata\Field\Table;

use ILIAS\UI\Component\Table\DataRetrieval as BaseDataRetrieval;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;
use ilAdvancedMDFieldDefinition;
use ilAdvancedMDFieldTranslations;
use ilAdvancedMDPermissionHelper;
use ilLanguage;

class DataRetrieval implements BaseDataRetrieval, OrderingRetrieval
{
    public function __construct(
        protected int $record_id,
        protected string $active_language,
        protected ilAdvancedMDPermissionHelper $permissions,
        protected ilLanguage $lng,
        protected \ILIAS\UI\Factory $ui_factory,
        protected \ILIAS\Data\Factory $data_factory,
        protected \ILIAS\Refinery\Factory $refinery
    ) {
    }

    public function getRawData(): array
    {
        $fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId(
            $this->record_id,
            false,
            $this->active_language
        );

        $res = [];
        $counter = 0;
        $ok_icon = $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', '', 'small');
        $not_ok_icon = $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', '', 'small');

        foreach ($fields as $definition) {
            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($definition->getRecordId());
            $id = $definition->getFieldId();
            $counter += 10;

            $properties = $definition->getFieldDefinitionForTableGUI($this->active_language);
            $configuration = implode("\n", array_map(
                static fn($key, $value): string => (string) $key . ": " . (string) $value . ";",
                array_keys($properties),
                $properties
            ));

            $searchable_icon = $definition->isSearchable() ? $ok_icon : $not_ok_icon;

            $res[$id] = [
                'id' => $id,
                'position' => $counter,
                'title' => $field_translations->getTitleForLanguage($id, $this->active_language),
                'description' => $field_translations->getDescriptionForLanguage($id, $this->active_language),
                'type' => $this->lng->txt($definition->getTypeTitle()),
                'configuration' => $configuration,
                'searchable' => $searchable_icon,
                'perm' => $this->permissions->hasPermissions(
                    ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
                    (int) $id,
                    [
                        ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT,
                        [
                            ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                            ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE
                        ]
                    ]
                )
            ];
        }

        return $res;
    }

    public function getRows(
        $row_builder,
        array $visible_column_ids,
        Range $range = null,
        Order $order = null,
        mixed $additional_viewcontrol_data = null,
        mixed $filter_data = null,
        mixed $additional_parameters = null
    ): Generator {
        $data = $this->getRawData();
        uasort($data, fn($a, $b) => $a['position'] <=> $b['position']);

        foreach ($data as $id => $datum) {
            yield $row_builder->buildOrderingRow(
                (string) $id,
                $datum
            )->withPosition((int) ($datum['position'] / 10));
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getRawData());
    }

    public function getAllIDs(): array
    {
        return array_map('strval', array_keys($this->getRawData()));
    }

    /**
     * @return string[]
     */
    public function resolveIDs(array|string $ids): array
    {
        if ($ids === ['ALL_OBJECTS'] || $ids === 'ALL_OBJECTS') {
            return $this->getAllIDs();
        }
        if (is_array($ids)) {
            return array_values(array_filter($ids, 'is_string'));
        }
        if (is_string($ids)) {
            return [$ids];
        }
        return [];
    }
}
