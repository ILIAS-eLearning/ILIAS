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
 * Object settings regarding position permissions
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilOrgUnitObjectTypePositionSetting
{
    public const DEFAULT_OFF = 0;
    public const DEFAULT_ON = 1;
    private ilDBInterface $db;
    private string $type;
    private bool $active = false;
    private bool $changeable = false;
    private int $default = self::DEFAULT_OFF;

    public function __construct(string $a_obj_type)
    {
        $this->db = $GLOBALS['DIC']->database();
        $this->type = $a_obj_type;
        $this->read();
    }

    /**
     * set active for object type
     */
    public function setActive(bool $a_active): void
    {
        $this->active = $a_active;
    }

    public function setActivationDefault(int $a_default): void
    {
        $this->default = $a_default;
    }

    public function setChangeableForObject(bool $a_status): void
    {
        $this->changeable = $a_status;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getActivationDefault(): int
    {
        return $this->default;
    }

    public function isChangeableForObject(): bool
    {
        return $this->changeable;
    }

    public function update(): void
    {
        $this->db->replace('orgu_obj_type_settings', [
            'obj_type' => ['text', $this->type],
        ], [
            'active' => ['integer', (int) $this->isActive()],
            'activation_default' => ['integer', (int) $this->getActivationDefault()],
            'changeable' => ['integer', (int) $this->isChangeableForObject()],
        ]);
    }

    private function read(): void
    {
        $query = 'SELECT * FROM orgu_obj_type_settings ' . 'WHERE obj_type = '
            . $this->db->quote($this->type, 'text');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setActive((bool) $row->active);
            $this->setActivationDefault((int) $row->activation_default);
            $this->setChangeableForObject((bool) $row->changeable);
        }
    }

    public function getType(): string
    {
        return $this->type;
    }
}
