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
 * Repository for skill profile completion
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillProfileCompletionRepository
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
    }

    /**
     * Get profile completion entries for given user-profile-combination
     */
    public function getEntries(int $user_id, int $profile_id) : array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer")
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => $rec["profile_id"],
                "user_id" => $rec["user_id"],
                "date" => $rec["date"],
                "fulfilled" => $rec["fulfilled"]
            );
        }

        return $entries;
    }

    /**
     * Add profile fulfilment entry to given user-profile-combination
     */
    public function addFulfilmentEntry(int $user_id, int $profile_id) : void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer") .
            " ORDER BY date DESC" .
            " LIMIT 1"
        );

        $entry = null;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entry = $rec["fulfilled"];
        }

        if ($entry == 0) {
            $now = ilUtil::now();
            $ilDB->manipulate("INSERT INTO skl_profile_completion " .
                "(profile_id, user_id, date, fulfilled) VALUES (" .
                $ilDB->quote($profile_id, "integer") . "," .
                $ilDB->quote($user_id, "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote(1, "integer") .
                ")");
        }
    }

    /**
     * Add profile non-fulfilment entry to given user-profile-combination
     */
    public function addNonFulfilmentEntry(int $user_id, int $profile_id) : void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer") .
            " ORDER BY date DESC" .
            " LIMIT 1"
        );

        $entry = null;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entry = $rec["fulfilled"];
        }

        if (is_null($entry) || $entry == 1) {
            $now = ilUtil::now();
            $ilDB->manipulate("INSERT INTO skl_profile_completion " .
                "(profile_id, user_id, date, fulfilled) VALUES (" .
                $ilDB->quote($profile_id, "integer") . "," .
                $ilDB->quote($user_id, "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote(0, "integer") .
                ")");
        }
    }

    /**
     * Get all profile completion entries for a user
     * @return array{profile_id: int, user_id: int, date: string, fulfilled: int}[]
     */
    public function getFulfilledEntriesForUser(int $user_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer") .
            " AND fulfilled = 1"
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => (int) $rec["profile_id"],
                "user_id" => (int) $rec["user_id"],
                "date" => $rec["date"],
                "fulfilled" => (int) $rec["fulfilled"]
            );
        }

        return $entries;
    }

    /**
     * Get all profile completion entries for a user
     */
    public function getAllEntriesForUser(int $user_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer")
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => $rec["profile_id"],
                "user_id" => $rec["user_id"],
                "date" => $rec["date"],
                "fulfilled" => $rec["fulfilled"]
            );
        }

        return $entries;
    }

    /**
     * Get all completion entries for a single profile
     */
    public function getAllEntriesForProfile(int $profile_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = array(
                "profile_id" => $rec["profile_id"],
                "user_id" => $rec["user_id"],
                "date" => $rec["date"],
                "fulfilled" => $rec["fulfilled"]
            );
        }

        return $entries;
    }

    /**
     * Delete all profile completion entries for a profile
     */
    public function deleteEntriesForProfile(int $profile_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_completion WHERE "
            . " profile_id = " . $ilDB->quote($profile_id, "integer")
        );
    }

    /**
     * Delete all profile completion entries for a user
     */
    public function deleteEntriesForUser(int $user_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_completion WHERE "
            . " user_id = " . $ilDB->quote($user_id, "integer")
        );
    }
}
