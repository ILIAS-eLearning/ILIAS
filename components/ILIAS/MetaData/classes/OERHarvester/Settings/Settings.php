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

namespace ILIAS\MetaData\OERHarvester\Settings;

class Settings implements SettingsInterface
{
    protected const STORAGE_IDENTIFIER = 'meta_oer';
    protected const COLLECTED_TYPES = [
        'file'
    ];

    protected \ilSetting $settings;

    /**
     * @var string[]
     */
    protected array $selected_obj_types;

    /**
     * @var int[]
     */
    protected array $selected_cp_entry_ids;
    protected int $target_for_harvesting_ref_id;
    protected int $source_for_exposing_ref_id;

    public function __construct()
    {
        $this->settings = new \ilSetting(self::STORAGE_IDENTIFIER);
    }

    /**
     * @return string[]
     */
    public function getObjectTypesSelectedForHarvesting(): array
    {
        return self::COLLECTED_TYPES;
    }

    public function isObjectTypeSelectedForHarvesting(string $type): bool
    {
        return in_array($type, self::COLLECTED_TYPES);
    }

    /**
     * @return int[]
     */
    public function getCopyrightEntryIDsSelectedForHarvesting(): array
    {
        if (isset($this->selected_cp_entry_ids)) {
            return $this->selected_cp_entry_ids;
        }
        $ids_from_storage = unserialize(
            $this->settings->get('templates', serialize([])),
            ['allowed_classes' => false]
        );
        $this->selected_cp_entry_ids = [];
        foreach ($ids_from_storage as $id) {
            $this->selected_cp_entry_ids[] = (int) $id;
        }
        return $this->selected_cp_entry_ids;
    }

    public function isCopyrightEntryIDSelectedForHarvesting(int $id): bool
    {
        $entry_ids = $this->getCopyrightEntryIDsSelectedForHarvesting();
        return in_array($id, $entry_ids);
    }

    public function saveCopyrightEntryIDsSelectedForHarvesting(int ...$ids): void
    {
        $this->selected_cp_entry_ids = $ids;
        $this->settings->set('templates', serialize($ids));
    }

    public function getContainerRefIDForHarvesting(): int
    {
        if (isset($this->target_for_harvesting_ref_id)) {
            return $this->target_for_harvesting_ref_id;
        }
        return $this->target_for_harvesting_ref_id = (int) $this->settings->get(
            'target',
            '0'
        );
    }

    public function saveContainerRefIDForHarvesting(int $ref_id): void
    {
        $this->target_for_harvesting_ref_id = $ref_id;
        $this->settings->set('target', (string) $ref_id);
    }

    public function getContainerRefIDForExposing(): int
    {
        if (isset($this->source_for_exposing_ref_id)) {
            return $this->source_for_exposing_ref_id;
        }
        return $this->source_for_exposing_ref_id = (int) $this->settings->get(
            'exposed_container',
            '0'
        );
    }

    public function saveContainerRefIDForExposing(int $ref_id): void
    {
        $this->source_for_exposing_ref_id = $ref_id;
        $this->settings->set('exposed_container', (string) $ref_id);
    }
}
