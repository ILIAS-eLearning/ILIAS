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
 * Stores user settings per workspace folder
 * Table: wfld_user_setting (rw)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWorkspaceFolderUserSettingsRepository
{
    protected int $user_id;
    protected ilDBInterface $db;

    /**
     * Constructor
     */
    public function __construct(
        int $user_id,
        ilDBInterface $db = null
    ) {
        global $DIC;

        $this->user_id = $user_id;
        $this->db = ($db != null)
            ? $db
            : $DIC->database();
    }

    public function getSortation(int $wfld_id) : int
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM wfld_user_setting " .
            " WHERE user_id = %s " .
            " AND wfld_id = %s ",
            array("integer", "integer"),
            array($this->user_id, $wfld_id)
        );
        $rec = $db->fetchAssoc($set);
        return (int) ($rec["sortation"] ?? 0);
    }

    public function getSortationMultiple(array $wfld_ids) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM wfld_user_setting " .
            " WHERE user_id = %s " .
            " AND " . $db->in("wfld_id", $wfld_ids, false, "integer"),
            array("integer"),
            array($this->user_id)
        );
        $ret = [];

        while ($rec = $db->fetchAssoc($set)) {
            $ret[$rec["wfld_id"]] = (int) $rec["sortation"];
        }
        foreach ($wfld_ids as $id) {
            if (!isset($ret[$id])) {
                $ret[$id] = 0;
            }
        }
        return $ret;
    }

    public function updateSortation(int $wfld_id, int $sortation)
    {
        $db = $this->db;

        $db->replace("wfld_user_setting", array(		// pk
                "user_id" => array("integer", $this->user_id),
                "wfld_id" => array("integer", $wfld_id)
            ), array(
                "sortation" => array("integer", $sortation)
            ));
    }
}
