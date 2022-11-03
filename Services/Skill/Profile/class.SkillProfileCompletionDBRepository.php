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
 ********************************************************************
 */

namespace ILIAS\Skill\Profile;

use ILIAS\Skill\Service;

/**
 * Repository for skill profile completion
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileCompletionDBRepository
{
    protected \ilDBInterface $db;
    protected Service\SkillInternalFactoryService $factory_service;

    public function __construct(
        \ilDBInterface $db = null,
        Service\SkillInternalFactoryService $factory_service = null
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internal()->factory();
    }

    /**
     * Get profile completion entries for given user-profile-combination
     * @return SkillProfileCompletion[]
     */
    public function getEntries(int $user_id, int $profile_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer")
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = $this->getFromRecord($rec);
        }

        return $entries;
    }

    protected function getFromRecord(array $rec): SkillProfileCompletion
    {
        $rec["profile_id"] = (int) $rec["profile_id"];
        $rec["user_id"] = (int) $rec["user_id"];
        $rec["fulfilled"] = (bool) $rec["fulfilled"];

        return $this->factory_service->profile()->profileCompletion(
            $rec["profile_id"],
            $rec["user_id"],
            $rec["date"],
            $rec["fulfilled"]
        );
    }

    /**
     * Add profile fulfilment entry to given user-profile-combination
     */
    public function addFulfilmentEntry(int $user_id, int $profile_id): void
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
            $now = \ilUtil::now();
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
    public function addNonFulfilmentEntry(int $user_id, int $profile_id): void
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
            $now = \ilUtil::now();
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
     * Get all fulfilled profile completion entries for a user
     * @return SkillProfileCompletion[]
     */
    public function getFulfilledEntriesForUser(int $user_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer") .
            " AND fulfilled = 1"
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = $this->getFromRecord($rec);
        }

        return $entries;
    }

    /**
     * Get all profile completion entries for a user
     * @return SkillProfileCompletion[]
     */
    public function getAllEntriesForUser(int $user_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer")
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = $this->getFromRecord($rec);
        }

        return $entries;
    }

    /**
     * Get all completion entries for a single profile
     * @return SkillProfileCompletion[]
     */
    public function getAllEntriesForProfile(int $profile_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_profile_completion " .
            " WHERE profile_id = " . $ilDB->quote($profile_id, "integer")
        );
        $entries = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $entries[] = $this->getFromRecord($rec);
        }

        return $entries;
    }

    /**
     * Delete all profile completion entries for a profile
     */
    public function deleteEntriesForProfile(int $profile_id): void
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
    public function deleteEntriesForUser(int $user_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_profile_completion WHERE "
            . " user_id = " . $ilDB->quote($user_id, "integer")
        );
    }
}
