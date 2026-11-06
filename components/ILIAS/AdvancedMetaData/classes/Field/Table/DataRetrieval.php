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

namespace AdvancedMetadata\Field\Table;

use ILIAS\UI\Component\Table\OrderingRetrieval;
use Generator;
use ilAdvancedMDFieldDefinition;
use ilAdvancedMDFieldTranslations;
use ilAdvancedMDPermissionHelper;
use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\OrderingRowBuilder;

class DataRetrieval implements OrderingRetrieval
{
    protected array $raw_data;

    public function __construct(
        protected int $record_id,
        protected string $active_language,
        protected ilAdvancedMDPermissionHelper $permissions,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory
    ) {
    }

    protected function getRawData(): array
    {
        if (isset($this->raw_data)) {
            return $this->raw_data;
        }

        $fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId(
            $this->record_id,
            false,
            $this->active_language
        );

        $res = [];
        $ok_icon = $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', '', 'small');
        $not_ok_icon = $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', '', 'small');

        foreach ($fields as $definition) {
            $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($definition->getRecordId());
            $id = $definition->getFieldId();

            $properties = $definition->getFieldDefinitionForTableGUI($this->active_language);
            $configuration_items = [];
            foreach ($properties as $key => $value) {
                $configuration_items[] = (string) $key . ": " . (string) $value;
            }
            $configuration = $this->ui_factory->listing()->unordered($configuration_items);

            $searchable_icon = $definition->isSearchable() ? $ok_icon : $not_ok_icon;

            $res[$id] = [
                'position' => $definition->getPosition(),
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

        $this->raw_data = $res;
        return $this->raw_data;
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): Generator {
        foreach ($this->getRawData() as $id => $datum) {
            yield $row_builder->buildOrderingRow(
                (string) $id,
                $datum
            );
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
        return array_keys($this->getRawData());
    }
}
