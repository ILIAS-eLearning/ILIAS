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

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilAccountCode
{
    public const DB_TABLE = 'usr_account_codes';
    public const CODE_LENGTH = 10;

    public static function create(
        string $valid_until,
        int $stamp
    ): int {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $code = "";
        $id = $ilDB->nextId(self::DB_TABLE);

        // create unique code
        $found = true;
        while ($found) {
            $code = self::generateRandomCode();
            $chk = $ilDB->queryF("SELECT code_id FROM " . self::DB_TABLE . " WHERE code = %s", array("text"), array($code));
            $found = (bool) $ilDB->numRows($chk);
        }

        $data = array(
            'code_id' => array('integer', $id),
            'code' => array('text', $code),
            'generated' => array('integer', $stamp),
            'valid_until' => array('text', $valid_until)
            );

        $ilDB->insert(self::DB_TABLE, $data);
        return $id;
    }

    protected static function generateRandomCode(): string
    {
        // missing : 01iloO
        $map = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";

        $code = "";
        $max = strlen($map) - 1;
        for ($loop = 1; $loop <= self::CODE_LENGTH; $loop++) {
            $code .= $map[random_int(0, $max)];
        }
        return $code;
    }

    public static function getCodesData(
        string $order_field,
        string $order_direction,
        int $offset,
        int $limit,
        string $filter_code,
        string $filter_valid_until,
        string $filter_generated
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // filter
        $where = self::filterToSQL($filter_code, $filter_valid_until, $filter_generated);

        // count query
        $set = $ilDB->query("SELECT COUNT(*) AS cnt FROM " . self::DB_TABLE . $where);
        $cnt = 0;
        if ($rec = $ilDB->fetchAssoc($set)) {
            $cnt = $rec["cnt"];
        }

        $sql = "SELECT * FROM " . self::DB_TABLE . $where;
        if ($order_field) {
            $sql .= " ORDER BY " . $order_field . " " . $order_direction;
        }

        // set query
        $ilDB->setLimit($limit, $offset);
        $set = $ilDB->query($sql);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        return array("cnt" => $cnt, "set" => $result);
    }

    /**
     * @return array<string,string>[]
     */
    public static function loadCodesByIds(array $ids): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query("SELECT * FROM " . self::DB_TABLE . " WHERE " . $ilDB->in("code_id", $ids, false, "integer"));
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        return $result;
    }

    /**
     * @param string[] $ids
     */
    public static function deleteCodes(array $ids): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (count($ids)) {
            return $ilDB->manipulate("DELETE FROM " . self::DB_TABLE . " WHERE " . $ilDB->in("code_id", $ids, false, "integer"));
        }
        return false;
    }

    /**
     * @return string[]
     */
    public static function getGenerationDates(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query("SELECT DISTINCT(generated) AS generated FROM " . self::DB_TABLE . " ORDER BY generated");
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec["generated"];
        }
        return $result;
    }

    protected static function filterToSQL(
        string $filter_code,
        string $filter_valid_until,
        string $filter_generated
    ): string {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $where = array();
        if ($filter_code) {
            $where[] = $ilDB->like("code", "text", "%" . $filter_code . "%");
        }
        if ($filter_valid_until) {
            $where[] = "valid_until = " . $ilDB->quote($filter_valid_until, "text");
        }
        if ($filter_generated) {
            $where[] = "generated = " . $ilDB->quote($filter_generated, "text");
        }
        if (count($where)) {
            return " WHERE " . implode(" AND ", $where);
        } else {
            return "";
        }
    }

    public static function getCodesForExport(
        string $filter_code,
        string $filter_valid_until,
        string $filter_generated
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // filter
        $where = self::filterToSQL($filter_code, $filter_valid_until, $filter_generated);

        // set query
        $set = $ilDB->query("SELECT code FROM " . self::DB_TABLE . $where . " ORDER BY code_id");
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec["code"];
        }
        return $result;
    }

    public static function isUnusedCode(string $code): bool
    {
        return ilRegistrationCode::isUnusedCode($code);
    }

    public static function useCode(string $code): bool
    {
        return ilRegistrationCode::useCode($code);
    }

    public static function getCodeValidUntil(string $code): string
    {
        $code_data = ilRegistrationCode::getCodeData($code);

        if ($code_data["alimit"]) {
            switch ($code_data["alimit"]) {
                case "absolute":
                    return $code_data['alimitdt'];
            }
        }
        return "0";
    }

    public static function applyRoleAssignments(
        ilObjUser $user,
        string $code
    ): bool {
        $recommended_content_manager = new ilRecommendedContentManager();

        $grole = ilRegistrationCode::getCodeRole($code);
        if ($grole) {
            $GLOBALS['DIC']['rbacadmin']->assignUser($grole, $user->getId());
        }
        $code_data = ilRegistrationCode::getCodeData($code);
        if ($code_data["role_local"]) {
            $code_local_roles = explode(";", $code_data["role_local"]);
            foreach ($code_local_roles as $role_id) {
                $GLOBALS['DIC']['rbacadmin']->assignUser($role_id, $user->getId());

                // patch to remove for 45 due to mantis 21953
                $role_obj = $GLOBALS['DIC']['rbacreview']->getObjectOfRole($role_id);
                switch (ilObject::_lookupType($role_obj)) {
                    case 'crs':
                    case 'grp':
                        $role_refs = ilObject::_getAllReferences($role_obj);
                        $role_ref = end($role_refs);
                        // deactivated for now, see discussion at
                        // https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html
                        //$recommended_content_manager->addObjectRecommendation($user->getId(), $role_ref);
                        break;
                }
            }
        }
        return true;
    }

    public static function applyAccessLimits(
        ilObjUser $user,
        string $code
    ): void {
        $code_data = ilRegistrationCode::getCodeData($code);

        if ($code_data["alimit"]) {
            switch ($code_data["alimit"]) {
                case "absolute":
                    $end = new ilDateTime($code_data['alimitdt'], IL_CAL_DATE);
                    //$user->setTimeLimitFrom(time());
                    $user->setTimeLimitUntil($end->get(IL_CAL_UNIX));
                    $user->setTimeLimitUnlimited(0);
                    break;

                case "relative":

                    $rel = unserialize($code_data["alimitdt"], ["allowed_classes" => false]);

                    $end = new ilDateTime(time(), IL_CAL_UNIX);

                    if ($rel['y'] > 0) {
                        $end->increment(IL_CAL_YEAR, $rel['y']);
                    }

                    if ($rel['m'] > 0) {
                        $end->increment(IL_CAL_MONTH, $rel['m']);
                    }

                    if ($rel['d'] > 0) {
                        $end->increment(IL_CAL_DAY, $rel['d']);
                    }

                    //$user->setTimeLimitFrom(time());
                    $user->setTimeLimitUntil($end->get(IL_CAL_UNIX));
                    $user->setTimeLimitUnlimited(0);
                    break;

                case 'unlimited':
                    $user->setTimeLimitUnlimited(1);
                    break;
            }
        } else {
            $user->setTimeLimitUnlimited(1);
        }
    }
}
