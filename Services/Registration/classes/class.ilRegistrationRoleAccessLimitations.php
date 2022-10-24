<?php

declare(strict_types=1);

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


/**
 * Class class.ilRegistrationAccessLimitation
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 */
class ilRegistrationRoleAccessLimitations
{
    public const IL_REG_ACCESS_LIMITATION_MISSING_MODE = 1;
    public const IL_REG_ACCESS_LIMITATION_OUT_OF_DATE = 2;

    private array $access_limitations = [];
    public array $access_limits = [];

    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read();
    }

    private function read(): void
    {
        $query = "SELECT * FROM reg_access_limit ";
        $res = $this->db->query($query);

        $this->access_limitations = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->access_limitations[$row->role_id]['id'] = (int) $row->role_id;
            $this->access_limitations[$row->role_id]['absolute'] = (int) $row->limit_absolute;
            $this->access_limitations[$row->role_id]['relative_d'] = (int) $row->limit_relative_d;
            $this->access_limitations[$row->role_id]['relative_m'] = (int) $row->limit_relative_m;
            $this->access_limitations[$row->role_id]['mode'] = (string) $row->limit_mode;
        }
    }

    public function save(): bool
    {
        foreach ($this->access_limitations as $key => $data) {
            $limit_value = "";

            // Delete old entry
            $query = "DELETE FROM reg_access_limit " .
                "WHERE role_id = " . $this->db->quote($key, 'integer');
            $res = $this->db->manipulate($query);

            $query = "INSERT INTO reg_access_limit (role_id,limit_mode,limit_absolute," .
                "limit_relative_d,limit_relative_m) " .
                "VALUES( " .
                $this->db->quote($key, ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($data['mode'], ilDBConstants::T_TEXT) . ", " .
                $this->db->quote($data['absolute'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($data['relative_d'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($data['relative_m'], ilDBConstants::T_INTEGER) . " " .
                ")";
            $res = $this->db->manipulate($query);
        }
        return true;
    }

    public function validate(): int
    {
        foreach ($this->access_limitations as $data) {
            if ($data['mode'] === "null") {
                return self::IL_REG_ACCESS_LIMITATION_MISSING_MODE;
            }

            if ($data['mode'] === 'absolute' && $data['absolute'] < time()) {
                return self::IL_REG_ACCESS_LIMITATION_OUT_OF_DATE;
            }

            if ($data['mode'] === 'relative' && ($data['relative_d'] < 1 && $data['relative_m'] < 1)) {
                return self::IL_REG_ACCESS_LIMITATION_OUT_OF_DATE;
            }
        }
        return 0;
    }

    public function getMode(int $a_role_id): string
    {
        return isset($this->access_limitations[$a_role_id]) ? $this->access_limitations[$a_role_id]['mode'] : 'null';
    }

    public function setMode(string $a_mode, int $a_role_id): void
    {
        $this->access_limitations[$a_role_id]['mode'] = $a_mode;
    }

    public function getAbsolute(int $a_role_id): int
    {
        return $this->access_limitations[$a_role_id] ? $this->access_limitations[$a_role_id]['absolute'] : time();
    }

    public function setAbsolute(string $date, int $a_role_id): void
    {
        $this->access_limitations[$a_role_id]['absolute'] = strtotime($date);
    }

    public function getRelative(int $a_role_id, string $a_type): int
    {
        return $this->access_limitations[$a_role_id] ? $this->access_limitations[$a_role_id]['relative_' . $a_type] : 0;
    }

    public function setRelative(array $a_arr, int $a_role_id): void
    {
        $this->access_limitations[$a_role_id]['relative_d'] = $a_arr['dd'];
        $this->access_limitations[$a_role_id]['relative_m'] = $a_arr['MM'];
    }

    public function resetAccessLimitations(): void
    {
        $this->access_limitations = [];
    }
}
