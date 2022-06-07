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

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Global settings for org units
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilOrgUnitGlobalSettings
{
    private static ?ilOrgUnitGlobalSettings $instance = null;
    protected ?ilObjectDefinition $object_definition = null;
    /** @var ilOrgUnitObjectTypePositionSetting[] */
    private array $position_settings = [];
    /**
     * Array with key obj_id => active status
     * @param bool[]
     */
    private array $object_position_cache = [];

    private function __construct()
    {
        $this->object_definition = $GLOBALS['DIC']['objDefinition'];
        $this->readSettings();
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getObjectPositionSettingsByType(string $a_obj_type): ilOrgUnitObjectTypePositionSetting
    {
        if (!isset($this->position_settings[$a_obj_type])) {
            throw new \InvalidArgumentException('Object type passed does not support position settings: '
                . $a_obj_type);
        }

        return $this->position_settings[$a_obj_type];
    }

    /**
     * Check of position access is activate for object
     */
    public function isPositionAccessActiveForObject(int $a_obj_id): bool
    {
        if (isset($this->object_position_cache[$a_obj_id])) {
            return $this->object_position_cache[$a_obj_id];
        }
        $type = ilObject::_lookupType($a_obj_id);
        try {
            $type_settings = $this->getObjectPositionSettingsByType($type);
        } catch (\InvalidArgumentException $invalid_type_exception) {
            $this->object_position_cache[$a_obj_id] = false;

            return false;
        }

        if (!$type_settings->isActive()) {
            $this->object_position_cache[$a_obj_id] = false;

            return false;
        }
        if (!$type_settings->isChangeableForObject()) {
            $this->object_position_cache[$a_obj_id] = true;

            return true;
        }
        $object_position = new ilOrgUnitObjectPositionSetting($a_obj_id);

        if ($object_position->hasObjectSpecificActivation()) {
            $this->object_position_cache[$a_obj_id] = $object_position->isActive();
        } else {
            $this->object_position_cache[$a_obj_id] = (bool) $type_settings->getActivationDefault();
        }

        return $this->object_position_cache[$a_obj_id];
    }

    /**
     * Set and save the default activation status according to settings.
     * @param int $a_obj_id
     */
    public function saveDefaultPositionActivationStatus(int $a_obj_id): void
    {
        $type = ilObject::_lookupType($a_obj_id);
        try {
            $type_settings = $this->getObjectPositionSettingsByType($type);
        } catch (\InvalidArgumentException $ex) {
            return;
        }
        if ($type_settings->isActive()) {
            $object_setting = new ilOrgUnitObjectTypePositionSetting($a_obj_id);
            $object_setting->setActive($type_settings->getActivationDefault());
            $object_setting->update();
        }
    }

    private function readSettings(): void
    {
        foreach ($this->object_definition->getOrgUnitPermissionTypes() as $type) {
            $this->position_settings[$type] = new ilOrgUnitObjectTypePositionSetting($type);
        }
    }

    /**
     * @return ilOrgUnitObjectTypePositionSetting[]
     */
    public function getPositionSettings(): array
    {
        return $this->position_settings;
    }
}
