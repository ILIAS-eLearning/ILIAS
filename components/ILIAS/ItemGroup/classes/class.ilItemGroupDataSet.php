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

class ilItemGroupDataSet extends ilDataSet
{
    protected ilObjItemGroup $current_obj;

    public function getSupportedVersions(): array
    {
        return ['4.3.0', '5.3.0', '10.12'];
    }

    public function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Modules/ItemGroup/{$a_entity}";
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        return match ($a_entity) {
            'itgr' => match ($a_version) {
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
            },
            'itgr_item' => match ($a_version) {
                '4.3.0', '5.3.0', '10.12' => [
                    'ItemGroupId' => ilDBConstants::T_INTEGER,
                    'ItemId' => ilDBConstants::T_TEXT,
                ],
                default => [],
            },
        };
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $in_obj_id = $this->db->in('obj_id', $a_ids, false, ilDBConstants::T_INTEGER);

        match ($a_entity) {
            'itgr' => match ($a_version) {
                '4.3.0' => $this->getDirectDataFromQuery(
                    "SELECT obj_id id, title, description, list_presentation, tile_size FROM object_data 
                    WHERE {$in_obj_id}"
                ),
                '5.3.0' => $this->getDirectDataFromQuery(
                    "SELECT obj_id id, title, description, hide_title, behaviour, list_presentation, tile_size FROM object_data 
                    INNER JOIN itgr_data ON object_data.obj_id = itgr_data.id 
                    WHERE {$in_obj_id}"
                ),
                '10.12' => $this->getDirectDataFromQuery(
                    "SELECT obj_id id, title, description, list_presentation, tile_size, display, toggleable_initially FROM object_data 
                    INNER JOIN itgr_data ON object_data.obj_id = itgr_data.id 
                    WHERE {$in_obj_id}"
                ),
                default => '',
            },
            'itgr_item' => match ($a_version) {
                '4.3.0', '5.3.0', '10.12' => $this->getDirectDataFromQuery(
                    "SELECT item_group_id itgr_id, item_ref_id item_id FROM item_group_item 
                    WHERE {$this->db->in('item_group_id', $a_ids, false, ilDBConstants::T_INTEGER)}"
                ),
                default => null,
            },
            default => null,
        };
    }

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set): array
    {
        if ($a_entity === 'itgr_item') {
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
            'itgr' => ['itgr_item' => ['ids' => $a_rec['Id'] ?? []]],
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
        $a_rec = $this->stripTags($a_rec);

        switch ($a_entity) {
            case 'itgr':
                $new_id = (int) ($a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['Id']) ?? 0);

                if ($new_id !== 0) {
                    /** @var ilObjItemGroup $newObj */
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjItemGroup();
                    $newObj->setType('itgr');
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
                $a_mapping->addMapping('components/ILIAS/ItemGroup', 'itgr', $a_rec['Id'], (string) $newObj->getId());
                break;

            case 'itgr_item':
                $obj_id = (int) ($a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['ItemId']) ?? 0);

                if ($obj_id === 0) {
                    break;
                }

                $ref_id = current(ilObject::_getAllReferences($obj_id));
                $itgri = new ilItemGroupItems();
                $itgri->setItemGroupId($this->current_obj->getId());
                $itgri->read();
                $itgri->addItem($ref_id);
                $itgri->update();
                break;
        }
    }

    private function resolveDisplay(array $a_set): string
    {
        if (isset($a_set['HideTitle'])) {
            return match ($a_set['HideTitle']) {
                '1' => ilItemGroupAR::DISPLAY_WITHOUT_TITLE,
                default => ilItemGroupAR::DISPLAY_WITH_TITLE,
            };
        }

        $display = $a_set['Display'] ?? '';
        return match ($display) {
            ilItemGroupAR::DISPLAY_WITHOUT_TITLE, ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE => $display,
            default => ilItemGroupAR::DISPLAY_WITH_TITLE,
        };
    }

    private function resolveBehaviour(array $a_set): string
    {
        if (isset($a_set['Behaviour'])) {
            return match ($a_set['Behaviour']) {
                '1' => ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
                default => ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_CLOSED,
            };
        }

        $toggleable_initially = $a_set['ToggleableInitially'] ?? '';
        return match ($toggleable_initially) {
            ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN => $toggleable_initially,
            default => ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_CLOSED,
        };
    }
}
