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

namespace ILIAS\Exercise\Submission;

use ILIAS\Repository\IRSS\IRSSWrapper;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Exercise\InternalDataService;

class SubmissionRepository implements SubmissionRepositoryInterface
{
    protected const TABLE_NAME = "exc_returned";
    protected $log;
    protected \ilDBInterface $db;

    public function __construct(
        protected IRSSWrapper $irss,
        protected InternalDataService $data,
        ?\ilDBInterface $db = null
    ) {
        global $DIC;
        $this->log = $DIC->logger()->exc();
        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }

    public function getAllEntriesOfAssignment(int $ass_id): array
    {
        $recs = [];
        $set = $this->db->queryF(
            "SELECT * FROM exc_returned " .
            " WHERE ass_id = %s ",
            ["integer"],
            [$ass_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $recs[] = $rec;
        }
        return $recs;
    }

    /**
     * @return \Generator<Submission>
     */
    public function getSubmissionsOfTeam(
        int $ass_id,
        bool $type_uses_uploads,
        bool $type_uses_print_versions,
        int $team_id,
        ?array $submit_ids = null,
        bool $only_valid = false,
        ?string $min_timestamp = null,
        bool $print_versions = false
    ): \Generator {
        $where = " team_id = " . $this->db->quote($team_id, "integer") . " ";
        yield from $this->getSubmissions(
            $where,
            $ass_id,
            $type_uses_uploads,
            $type_uses_print_versions,
            $submit_ids,
            $only_valid,
            $min_timestamp,
            $print_versions
        );
    }

    /**
     * @return \Generator<Submission>
     */
    public function getSubmissionsOfUsers(
        int $ass_id,
        bool $type_uses_uploads,
        bool $type_uses_print_versions,
        array $user_ids,
        ?array $submit_ids = null,
        bool $only_valid = false,
        ?string $min_timestamp = null,
        bool $print_versions = false
    ): \Generator {
        $where = " " . $this->db->in("user_id", $user_ids, false, "integer") . " ";
        yield from $this->getSubmissions(
            $where,
            $ass_id,
            $type_uses_uploads,
            $type_uses_print_versions,
            $submit_ids,
            $only_valid,
            $min_timestamp,
            $print_versions
        );
    }

    /**
     * @return \Generator<Submission>
     */
    protected function getSubmissions(
        string $where,
        int $ass_id,
        bool $type_uses_uploads,
        bool $type_uses_print_versions,
        ?array $submit_ids = null,
        bool $only_valid = false,
        ?string $min_timestamp = null,
        bool $print_versions = false
    ): \Generator {
        $sql = "SELECT * FROM exc_returned" .
            " WHERE ass_id = " . $this->db->quote($ass_id, "integer");
        $sql .= " AND " . $where;

        if ($submit_ids) {
            $sql .= " AND " . $this->db->in("returned_id", $submit_ids, false, "integer");
        }

        if ($min_timestamp) {
            $sql .= " AND ts > " . $this->db->quote($min_timestamp, "timestamp");
        }

        $result = $this->db->query($sql);
        $delivered_files = array();
        while ($row = $this->db->fetchAssoc($result)) {
            // blog/portfolio/text submissions
            if ($only_valid &&
                !$row["filename"] &&
                !(trim((string) $row["atext"]))) {
                continue;
            }

            //$row["owner_id"] = $row["user_id"];
            $row["timestamp"] = $row["ts"];
            /*
            $row["timestamp14"] = substr($row["ts"], 0, 4) .
                substr($row["ts"], 5, 2) . substr($row["ts"], 8, 2) .
                substr($row["ts"], 11, 2) . substr($row["ts"], 14, 2) .
                substr($row["ts"], 17, 2);*/

            $row["rid"] = (string) $row["rid"];

            // see 22301, 22719
            if ($row["rid"] !== "" || (!$type_uses_uploads)) {
                $ok = true;

                if ($type_uses_print_versions) {
                    $is_print_version = false;
                    if (str_ends_with($row["filetitle"], "print")) {
                        $is_print_version = true;
                    }
                    if (str_ends_with($row["filetitle"], "print.zip")) {
                        $is_print_version = true;
                    }
                    if ($is_print_version != $print_versions) {
                        $ok = false;
                    }
                }
                if ($ok) {
                    yield $this->getSubmissionFromRecord($row);
                }
            }
        }
    }

    public function getUserId(int $submission_id): int
    {
        $q = "SELECT user_id FROM exc_returned " .
            " WHERE returned_id = " . $this->db->quote($submission_id, "integer");
        $usr_set = $this->db->query($q);

        $rec = $this->db->fetchAssoc($usr_set);
        return (int) ($rec["user_id"] ?? 0);
    }

    public function getAllSubmissionIdsOfOwner(int $ass_id, int $user_id): array
    {
        $set = $this->db->queryF(
            "SELECT returned_id FROM exc_returned " .
            " WHERE ass_id = %s AND user_id = %s",
            ["integer", "integer"],
            [$ass_id, $user_id]
        );
        $user_ids = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $user_ids[] = (int) $rec["returned_id"];
        }
        return $user_ids;
    }


    public function hasSubmissions(int $assignment_id): int
    {
        $query = "SELECT * FROM exc_returned " .
            " WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL";
        $res = $this->db->query($query);
        return $res->numRows();
    }

    // Update web_dir_access_time. It defines last HTML opening data.
    public function updateWebDirAccessTime(int $assignment_id, int $member_id): void
    {
        $this->db->manipulate("UPDATE exc_returned " .
            " SET web_dir_access_time = " . $this->db->quote(\ilUtil::now(), "timestamp") .
            " WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
            " AND user_id = " . $this->db->quote($member_id, "integer"));
    }

    /**
     * Checks if a user has submitted anything for a number of assignments.
     * This function should be performant, because it is being used for task
     * determination. It assumes, that team db entries only exist for team
     * assignment types and thus does not read the assignment types at all.
     */
    public function getUserSubmissionState(int $user_id, array $assignment_ids): array
    {
        $db = $this->db;

        $submitted = [];
        foreach ($assignment_ids as $id) {
            $submitted[(int) $id] = false;
        }

        $set = $db->queryF(
            "SELECT ass_id FROM exc_returned " .
            " WHERE " . $db->in("ass_id", $assignment_ids, false, "integer") .
            " AND user_id = %s " .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL",
            ["integer"],
            [$user_id]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $submitted[(int) $rec["ass_id"]] = true;
        }

        $set = $db->queryF(
            "SELECT ret.ass_id FROM exc_returned ret JOIN il_exc_team team " .
            " ON (ret.team_id = team.id AND ret.ass_id = team.ass_id) " .
            " WHERE " . $db->in("ret.ass_id", $assignment_ids, false, "integer") .
            " AND team.user_id = %s " .
            " AND (ret.filename IS NOT NULL OR ret.atext IS NOT NULL)" .
            " AND ret.ts IS NOT NULL",
            ["integer"],
            [$user_id]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $submitted[(int) $rec["ass_id"]] = true;
        }

        return $submitted;
    }

    public function updateLate(
        int $return_id,
        bool $late
    ): void {
        $this->db->manipulate("UPDATE exc_returned" .
            " SET late = " . $this->db->quote((int) $late, "integer") .
            " WHERE returned_id = " . $this->db->quote($return_id, "integer"));
    }

    /**
     * Get the number of max amount of files submitted by a single user in the assignment.
     * Used to add columns to the excel.
     */
    public function getMaxAmountOfSubmittedFiles(
        int $obj_id,
        int $ass_id,
        int $user_id = 0
    ): int {
        $db = $this->db;

        $and = "";
        if ($user_id > 0) {
            $and = " AND user_id = " . $db->quote($user_id, "integer");
        }

        $set = $db->queryF(
            "SELECT MAX(max_num) AS max" .
            " FROM (SELECT COUNT(user_id) AS max_num FROM exc_returned" .
            " WHERE obj_id= %s AND ass_id= %s " . $and . " AND mimetype IS NOT NULL" .
            " GROUP BY user_id) AS COUNTS",
            ["integer", "integer"],
            [$obj_id, $ass_id]
        );
        $row = $db->fetchAssoc($set);
        return (int) $row['max'];
    }

    /**
     * Get all user ids, that have submitted something
     * @return int[]
     */
    public function getUsersWithSubmission(int $ass_id): array
    {
        $db = $this->db;
        $user_ids = [];
        $set = $db->query("SELECT DISTINCT(user_id)" .
            " FROM exc_returned" .
            " WHERE ass_id = " . $db->quote($ass_id, "integer") .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)");
        while ($row = $db->fetchAssoc($set)) {
            $user_ids[] = (int) $row["user_id"];
        }
        return $user_ids;
    }

    public function addLocalFile(
        int $obj_id,
        int $ass_id,
        int $user_id,
        int $team_id,
        string $file,
        string $filename,
        bool $is_late,
        ResourceStakeholder $stakeholder
    ): bool {
        $db = $this->db;
        $rid = $this->irss->importLocalFile(
            $file,
            $filename,
            $stakeholder
        );
        $filename = \ilFileUtils::getValidFilename($filename);

        if ($rid !== "") {
            $info = $this->irss->getResourceInfo($rid);
            $next_id = $db->nextId("exc_returned");
            $query = sprintf(
                "INSERT INTO exc_returned " .
                "(returned_id, obj_id, user_id, filename, filetitle, mimetype, ts, ass_id, late, team_id, rid) " .
                "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $db->quote($next_id, "integer"),
                $db->quote($obj_id, "integer"),
                $db->quote($user_id, "integer"),
                $db->quote($filename, "text"),
                $db->quote($filename, "text"),
                $db->quote($info->getMimeType(), "text"),
                $db->quote(\ilUtil::now(), "timestamp"),
                $db->quote($ass_id, "integer"),
                $db->quote($is_late, "integer"),
                $db->quote($team_id, "integer"),
                $db->quote($rid, "text")
            );
            $db->manipulate($query);
            return true;
        }
        return false;
    }

    protected function getSubmissionFromRecord(array $rec): Submission
    {
        return $this->data->submission(
            (int) $rec["returned_id"],
            (int) $rec["ass_id"],
            (int) $rec["user_id"],
            (int) $rec["team_id"],
            (string) $rec["filetitle"],
            (string) $rec["atext"],
            (string) $rec["rid"],
            (string) $rec["mimetype"],
            (string) $rec["ts"],
            (bool) $rec["late"]
        );
    }

    public function addUpload(
        int $obj_id,
        int $ass_id,
        int $user_id,
        int $team_id,
        UploadResult $result,
        string $filename,
        bool $is_late,
        ResourceStakeholder $stakeholder
    ): bool {
        $db = $this->db;
        $rid = $this->irss->importFileFromUploadResult(
            $result,
            $stakeholder
        );
        $filename = \ilFileUtils::getValidFilename($filename);

        if ($rid !== "") {
            $info = $this->irss->getResourceInfo($rid);
            $next_id = $db->nextId("exc_returned");
            $query = sprintf(
                "INSERT INTO exc_returned " .
                "(returned_id, obj_id, user_id, filename, filetitle, mimetype, ts, ass_id, late, team_id, rid) " .
                "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $db->quote($next_id, "integer"),
                $db->quote($obj_id, "integer"),
                $db->quote($user_id, "integer"),
                $db->quote($filename, "text"),
                $db->quote($filename, "text"),
                $db->quote($info->getMimeType(), "text"),
                $db->quote(\ilUtil::now(), "timestamp"),
                $db->quote($ass_id, "integer"),
                $db->quote($is_late, "integer"),
                $db->quote($team_id, "integer"),
                $db->quote($rid, "text")
            );
            $db->manipulate($query);
            return true;
        }
        return false;
    }

    public function addZipUpload(
        int $obj_id,
        int $ass_id,
        int $user_id,
        int $team_id,
        UploadResult $result,
        bool $is_late,
        ResourceStakeholder $stakeholder
    ): array {
        global $DIC;

        $db = $this->db;
        $filenames = [];
        $this->log->debug("5");
        $rid = $this->irss->importFileFromUploadResult(
            $result,
            $stakeholder
        );
        $this->log->debug("6");
        $stream = $this->irss->stream($rid);

        foreach ($DIC->archives()->unzip($stream)->getFileStreams() as $stream) {
            $this->log->debug("7");
            $rid = $this->irss->importStream(
                $stream,
                $stakeholder
            );
            $info = $this->irss->getResourceInfo($rid);
            $filename = $info->getTitle();
            $this->log->debug("8");
            if ($rid !== "") {
                $this->log->debug("9");
                $info = $this->irss->getResourceInfo($rid);
                $next_id = $db->nextId("exc_returned");
                $query = sprintf(
                    "INSERT INTO exc_returned " .
                    "(returned_id, obj_id, user_id, filename, filetitle, mimetype, ts, ass_id, late, team_id, rid) " .
                    "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                    $db->quote($next_id, "integer"),
                    $db->quote($obj_id, "integer"),
                    $db->quote($user_id, "integer"),
                    $db->quote($filename, "text"),
                    $db->quote($filename, "text"),
                    $db->quote($info->getMimeType(), "text"),
                    $db->quote(\ilUtil::now(), "timestamp"),
                    $db->quote($ass_id, "integer"),
                    $db->quote($is_late, "integer"),
                    $db->quote($team_id, "integer"),
                    $db->quote($rid, "text")
                );
                $db->manipulate($query);
                $filenames[] = $filename;
            }
        }
        return $filenames;
    }

    public function delete(
        int $id,
        ResourceStakeholder $stakeholder
    ): void {
        $set = $this->db->queryF(
            "SELECT rid FROM exc_returned " .
            " WHERE returned_id = %s ",
            ["integer"],
            [$id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            $rid = (string) ($rec["rid"] ?? "");
            if ($rid !== "") {
                $this->irss->deleteResource(
                    $rid,
                    $stakeholder
                );
            }
            $this->db->manipulateF(
                "DELETE FROM exc_returned WHERE " .
                " returned_id = %s",
                ["integer"],
                [$id]
            );
        }
    }

    public function deliverFile(
        int $ass_id,
        int $user_id,
        string $rid,
        string $filetitle = ""
    ): void {
        if ($filetitle !== "") {
            $this->irss->renameCurrentRevision($rid, $filetitle);
        }
        $this->irss->deliverFile($rid);
    }

    public function getStream(
        int $ass_id,
        string $rid
    ): ?FileStream {
        return $this->irss->stream($rid);
    }

}
