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
 ********************************************************************
 */

declare(strict_types=1);

/**
 * Object-specific activation settings for orgunit position.
 */
class ilOrgUnitObjectPositionSetting
{
    protected ilDBInterface $db;
    private static array $instances = [];
    private ?bool $active = null;
    private ?ilOrgUnitObjectTypePositionSetting $settings_cache = null;

    public function __construct(
        private int $obj_id
    ) {
        $this->db = $GLOBALS['DIC']->database();
        $this->readSettings();
    }

    public static function getFor(int $obj_id): self
    {
        if (! array_key_exists($obj_id, self::$instances)) {
            self::$instances[$obj_id] = new self($obj_id);
        }
        return self::$instances[$obj_id];
    }

    public function isActive(): bool
    {
        if (! $this->isActiveForOwnType()) {
            return false;
        }

        if (! $this->isChangeable()) { //implicit: && isActiveForOwnType == true
            return true;
        }

        if ($this->active === null) { //implicit: isActiveForOwnType && isChangeable
            return (bool) $this->getGlobalSettingsForThisObjectType()->getActivationDefault();
        }

        return $this->active;
    }

    /**
     * convenience wrapper for specific objects to check on global settings
     */
    public function isActiveForOwnType(): bool
    {
        return $this->getGlobalSettingsForThisObjectType()?->isActive() ?? false;
    }

    /**
     * convenience wrapper for specific objects to check on global settings
     */
    public function isChangeable(): bool
    {
        return $this->getGlobalSettingsForThisObjectType()?->isChangeableForObject() ?? false;
    }

    public function setActive(bool $status): void
    {
        $this->active = $status;
    }

    public function update(): void
    {
        if ($this->isActiveForOwnType() && $this->isChangeable() && $this->active !== null) {
            $this->db->replace('orgu_obj_pos_settings', [
                'obj_id' => ['integer', $this->obj_id],
            ], [
                'active' => ['integer', (int) $this->isActive()],
            ]);
        }
    }

    public function delete(): void
    {
        $query = 'DELETE FROM orgu_obj_pos_settings WHERE obj_id = '
            . $this->db->quote($this->obj_id, 'integer');
        $this->db->manipulate($query);
    }

    private function readSettings(): void
    {
        $query = 'SELECT active FROM orgu_obj_pos_settings WHERE obj_id = '
            . $this->db->quote($this->obj_id, 'integer');

        $result = $this->db->query($query);
        if ($this->db->numRows($result) > 0) {
            $this->active = (bool) $this->db->fetchAssoc($result)['active'];
        }
    }

    private function getGlobalSettingsForThisObjectType(): ?ilOrgUnitObjectTypePositionSetting
    {
        if ($this->settings_cache === null) {
            $global_settings = ilOrgUnitGlobalSettings::getInstance();
            $type = \ilObject::_lookupType($this->obj_id);
            if (array_key_exists($type, $global_settings->getPositionSettings())) {
                $this->settings_cache = $global_settings->getObjectPositionSettingsByType($type);
            } else {
                $this->settings_cache = null;
            }
        }
        return $this->settings_cache;
    }


}
