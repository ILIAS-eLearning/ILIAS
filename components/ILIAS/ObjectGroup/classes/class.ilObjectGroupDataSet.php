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

class ilObjectGroupDataSet extends ilDataSet
{
    protected ilObjObjectGroup $current_obj;

    public function getSupportedVersions(): array
    {
        return ['4.3.0', '5.3.0', '10.12'];
    }

    public function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Modules/ObjectGroup/{$a_entity}";
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        $object_types = match ($a_version) {
            '4.3.0' => [
                'Id' => ilDBConstants::T_INTEGER,
                'Title' => ilDBConstants::T_TEXT,
                'Description' => ilDBConstants::T_TEXT,
            ],
            '5.3.0' => [
                'Id' => ilDBConstants::T_INTEGER,
                'HideTitle' => ilDBConstants::T_INTEGER,
                'Behaviour' => ilDBConstants::T_INTEGER,
                'ListPresentation' => ilDBConstants::T_TEXT,
                'TileSize' => ilDBConstants::T_INTEGER,
                'Title' => ilDBConstants::T_TEXT,
                'Description' => ilDBConstants::T_TEXT,
            ],
            '10.12' => [
                'Id' => ilDBConstants::T_INTEGER,
                'Display' => ilDBConstants::T_TEXT,
                'ToggleableInitially' => ilDBConstants::T_TEXT,
                'ListPresentation' => ilDBConstants::T_TEXT,
                'TileSize' => ilDBConstants::T_INTEGER,
                'Title' => ilDBConstants::T_TEXT,
                'Description' => ilDBConstants::T_TEXT,
            ],
            default => [],
        };

        return match ($a_entity) {
            'obgr' => $object_types,
            'obgr_item' => match ($a_version) {
                '4.3.0', '5.3.0', '10.12' => [
                    'ObjectGroupId' => ilDBConstants::T_INTEGER,
                    'ItemId' => ilDBConstants::T_TEXT,
                ],
                default => [],
            },
            default => [],
        };
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $in_obj_id = $this->db->in('obj_id', $a_ids, false, ilDBConstants::T_INTEGER);
        match ($a_entity) {
            'obgr' => match ($a_version) {
                '4.3.0' => $this->getDirectDataFromQuery(
                    "SELECT obj_id id, title, description, list_presentation, tile_size FROM object_data 
                    WHERE {$in_obj_id}"
                ),
                '5.3.0' => $this->getDirectDataFromQuery(
                    "SELECT obj_id id, title, description, hide_title, behaviour, list_presentation, tile_size FROM object_data 
                    INNER JOIN obgr_data ON object_data.obj_id = obgr_data.id 
                    WHERE {$in_obj_id}"
                ),
                '10.12' => $this->getDirectDataFromQuery(
                    "SELECT obj_id id, title, description, list_presentation, tile_size, display, toggleable_initially FROM object_data 
                    INNER JOIN obgr_data ON object_data.obj_id = obgr_data.id 
                    WHERE {$in_obj_id}"
                ),
                default => '',
            },
            'obgr_item' => match ($a_version) {
                '4.3.0', '5.3.0', '10.12' => $this->getDirectDataFromQuery(
                    "SELECT object_group_id, object_ref_id item_id FROM object_group_item 
                    WHERE {$this->db->in('object_group_id', $a_ids, false, ilDBConstants::T_INTEGER)}"
                ),
                default => null,
            },
            default => null,
        };
    }

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set): array
    {
        if ($a_entity === 'obgr_item') {
            $a_set['ItemId'] = ilObject::_lookupObjId($a_set['ItemId']);
        }

        return $a_set;
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        return match ($a_entity) {
            'obgr' => ['obgr_item' => ['ids' => $a_rec['Id'] ?? []]],
            default => [],
        };
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        $a_rec = $this->normalizeImportedRecord($this->stripTags($a_rec));
        $source_entity = $a_entity;
        $a_entity = $this->exportEntity($a_entity);

        switch ($a_entity) {
            case 'obgr':
                $new_id = (int) ($a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['Id']) ?? 0);

                if ($new_id !== 0) {
                    /** @var ilObjObjectGroup $newObj */
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                    $newObj->setType('obgr');
                } else {
                    $newObj = new ilObjObjectGroup();
                    $newObj->setType('obgr');
                    $newObj->create(true);
                }

                $newObj->setTitle($a_rec['Title']);
                $newObj->setDescription($a_rec['Description']);
                $newObj->setListPresentation((string) ($a_rec['ListPresentation'] ?? ''));
                $newObj->setTileSize((int) ($a_rec['TileSize'] ?? 0));
                $newObj->setDisplay($this->resolveDisplay($a_rec));
                $newObj->setDisplayWithTitleAndToggleableInitially($this->resolveBehaviour($a_rec));
                $newObj->update();
                $this->current_obj = $newObj;

                if ($source_entity === 'itgr') {
                    $a_mapping->addMapping('components/ILIAS/ItemGroup', 'itgr', $a_rec['Id'], (string) $newObj->getId());
                }
                $a_mapping->addMapping('components/ILIAS/ObjectGroup', 'obgr', $a_rec['Id'], (string) $newObj->getId());
                break;

            case 'obgr_item':
                $obj_id = (int) ($a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['ItemId']) ?? 0);

                if ($obj_id === 0) {
                    break;
                }

                $ref_id = current(ilObject::_getAllReferences($obj_id));
                $ogi = new ilObjectGroupItems();
                $ogi->setObjectGroupId($this->current_obj->getId());
                $ogi->read();
                $ogi->addItem($ref_id);
                $ogi->update();
                break;
        }
    }

    private function resolveDisplay(array $a_set): string
    {
        if (isset($a_set['HideTitle'])) {
            return match ($a_set['HideTitle']) {
                '1' => ilObjectGroupAR::DISPLAY_WITHOUT_TITLE,
                default => ilObjectGroupAR::DISPLAY_WITH_TITLE,
            };
        }

        $display = $a_set['Display'] ?? '';
        return match ($display) {
            ilObjectGroupAR::DISPLAY_WITHOUT_TITLE, ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE => $display,
            default => ilObjectGroupAR::DISPLAY_WITH_TITLE,
        };
    }

    private function resolveBehaviour(array $a_set): string
    {
        if (isset($a_set['Behaviour'])) {
            return match ($a_set['Behaviour']) {
                '1' => ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
                default => ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_CLOSED,
            };
        }

        $toggleable_initially = $a_set['ToggleableInitially'] ?? '';
        return match ($toggleable_initially) {
            ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN => $toggleable_initially,
            default => ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_CLOSED,
        };
    }

    private function exportEntity(string $entity): string
    {
        return match ($entity) {
            'itgr' => 'obgr',
            'itgr_item' => 'obgr_item',
            default => $entity,
        };
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function normalizeImportedRecord(array $record): array
    {
        if (!isset($record['ObjectGroupId'])) {
            $record['ObjectGroupId'] = $record['ItemGroupId'] ?? $record['ItgrId'] ?? null;
        }

        return $record;
    }

}
