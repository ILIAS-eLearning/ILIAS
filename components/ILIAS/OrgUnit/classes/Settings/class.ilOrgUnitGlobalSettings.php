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
