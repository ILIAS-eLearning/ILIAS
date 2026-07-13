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
    protected const string STORAGE_IDENTIFIER = 'meta_oer';
    protected const array ELIGIBLE_TYPES = [
        'blog',
        'copa',
        'dcl',
        'exc',
        'file',
        'glo',
        'lm',
        'htlm',
        'sahs',
        'mcst',
        'mep',
        'qpl',
        'spl',
        'webr',
        'wiki'
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
    protected bool $editorial_step_enabled;
    protected int $editorial_ref_id;
    protected int $source_for_exposing_ref_id;
    protected bool $manual_publishing_enabled;
    protected bool $automatic_publishing_enabled;

    public function __construct()
    {
        $this->settings = new \ilSetting(self::STORAGE_IDENTIFIER);
    }

    /**
     * @return string[]
     */
    public function getObjectTypesEligibleForPublishing(): array
    {
        return self::ELIGIBLE_TYPES;
    }

    /**
     * @return string[]
     */
    public function getObjectTypesSelectedForPublishing(): array
    {
        if (isset($this->selected_obj_types)) {
            return $this->selected_obj_types;
        }
        $types_from_storage = unserialize(
            $this->settings->get(
                'collected_types',
                serialize($this->getObjectTypesEligibleForPublishing()),
            ),
            ['allowed_classes' => false]
        );
        return $this->selected_obj_types = array_intersect(
            $types_from_storage,
            $this->getObjectTypesEligibleForPublishing()
        );
    }

    public function isObjectTypeSelectedForPublishing(string $type): bool
    {
        $types = $this->getObjectTypesSelectedForPublishing();
        return in_array($type, $types);
    }

    public function saveObjectTypesSelectedForPublishing(string ...$types): void
    {
        $this->selected_obj_types = $types;
        $this->settings->set('collected_types', serialize($types));
    }

    /**
     * @return int[]
     */
    public function getCopyrightEntryIDsSelectedForPublishing(): array
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

    public function isCopyrightEntryIDSelectedForPublishing(int $id): bool
    {
        $entry_ids = $this->getCopyrightEntryIDsSelectedForPublishing();
        return in_array($id, $entry_ids);
    }

    public function saveCopyrightEntryIDsSelectedForPublishing(int ...$ids): void
    {
        $this->selected_cp_entry_ids = $ids;
        $this->settings->set('templates', serialize($ids));
    }

    public function isEditorialStepEnabled(): bool
    {
        if (isset($this->editorial_step_enabled)) {
            return $this->editorial_step_enabled;
        }
        return $this->editorial_step_enabled = (bool) $this->settings->get(
            'editorial_step',
            '0'
        );
    }

    public function saveEditorialStepEnabled(bool $enabled): void
    {
        $this->editorial_step_enabled = $enabled;
        $this->settings->set('editorial_step', $enabled ? '1' : '0');
    }

    public function getContainerRefIDForEditorialStep(): int
    {
        if (isset($this->editorial_ref_id)) {
            return $this->editorial_ref_id;
        }
        return $this->editorial_ref_id = (int) $this->settings->get(
            'target',
            '0'
        );
    }

    public function saveContainerRefIDForEditorialStep(int $ref_id): void
    {
        $this->editorial_ref_id = $ref_id;
        $this->settings->set('target', (string) $ref_id);
    }

    public function getContainerRefIDForPublishing(): int
    {
        if (isset($this->source_for_exposing_ref_id)) {
            return $this->source_for_exposing_ref_id;
        }
        return $this->source_for_exposing_ref_id = (int) $this->settings->get(
            'exposed_container',
            '0'
        );
    }

    public function saveContainerRefIDForPublishing(int $ref_id): void
    {
        $this->source_for_exposing_ref_id = $ref_id;
        $this->settings->set('exposed_container', (string) $ref_id);
    }



    public function isManualPublishingEnabled(): bool
    {
        if (isset($this->manual_publishing_enabled)) {
            return $this->manual_publishing_enabled;
        }
        return $this->manual_publishing_enabled = (bool) $this->settings->get(
            'manual_publishing',
            '0'
        );
    }

    public function saveManualPublishingEnabled(bool $enabled): void
    {
        $this->editorial_step_enabled = $enabled;
        $this->settings->set('manual_publishing', $enabled ? '1' : '0');
    }

    public function isAutomaticPublishingEnabled(): bool
    {
        if (isset($this->automatic_publishing_enabled)) {
            return $this->automatic_publishing_enabled;
        }
        return $this->automatic_publishing_enabled = (bool) $this->settings->get(
            'automatic_publishing',
            '0'
        );
    }

    public function saveAutomaticPublishingEnabled(bool $enabled): void
    {
        $this->editorial_step_enabled = $enabled;
        $this->settings->set('automatic_publishing', $enabled ? '1' : '0');
    }
}
