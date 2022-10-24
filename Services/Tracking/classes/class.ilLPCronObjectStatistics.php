<?php

declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron for lp object statistics
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPCronObjectStatistics extends ilCronJob
{
    protected int $date = 0;

    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilLogger $logger;
    protected ilCronManager $cron_manager;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->trac();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("trac");
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->cron_manager = $DIC->cron()->manager();
    }

    public function getId(): string
    {
        return "lp_object_statistics";
    }

    public function getTitle(): string
    {
        return $this->lng->txt("trac_object_statistics");
    }

    public function getDescription(): string
    {
        return $this->lng->txt("trac_object_statistics_info");
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    public function run(): ilCronJobResult
    {
        // all date related operations are based on this timestamp
        // should be midnight of yesterday (see gatherUserData()) to always have full day
        $this->date = strtotime("yesterday");

        $status = ilCronJobResult::STATUS_NO_ACTION;
        $message = array();

        $count = 0;
        $count += $this->gatherCourseLPData();
        $count += $this->gatherTypesData();
        $count += $this->gatherUserData();

        if ($count) {
            $status = ilCronJobResult::STATUS_OK;
        }

        $result = new ilCronJobResult();
        $result->setStatus($status);

        return $result;
    }

    /**
     * gather course data
     */
    protected function gatherCourseLPData(): int
    {
        $count = 0;

        // process all courses
        $all_courses = array_keys(ilObject::_getObjectsByType("crs"));
        if ($all_courses) {
            // gather objects in trash
            $trashed_objects = $this->tree->getSavedNodeObjIds($all_courses);

            foreach ($all_courses as $crs_id) {
                // trashed objects will not change
                if (!in_array($crs_id, $trashed_objects)) {
                    $refs = ilObject::_getAllReferences($crs_id);
                    if (!count($refs)) {
                        $this->logger->warning(
                            'Found course without reference: obj_id = ' . $crs_id
                        );
                        continue;
                    }

                    // only if LP is active
                    $olp = ilObjectLP::getInstance($crs_id);
                    if (!$olp->isActive()) {
                        continue;
                    }

                    // only save once per day
                    $this->db->manipulate(
                        "DELETE FROM obj_lp_stat WHERE" .
                        " obj_id = " . $this->db->quote($crs_id, "integer") .
                        " AND fulldate = " . $this->db->quote(
                            date("Ymd", $this->date),
                            "integer"
                        )
                    );

                    $members = new ilCourseParticipants($crs_id);
                    $members = $members->getMembers();

                    $in_progress = count(
                        ilLPStatusWrapper::_lookupInProgressForObject(
                            $crs_id,
                            $members
                        )
                    );
                    $completed = count(
                        ilLPStatusWrapper::_lookupCompletedForObject(
                            $crs_id,
                            $members
                        )
                    );
                    $failed = count(
                        ilLPStatusWrapper::_lookupFailedForObject(
                            $crs_id,
                            $members
                        )
                    );

                    // calculate with other values - there is not direct method
                    $not_attempted = count(
                        $members
                    ) - $in_progress - $completed - $failed;

                    $set = array(
                        "type" => array("text", "crs"),
                        "obj_id" => array("integer", $crs_id),
                        "yyyy" => array("integer", date("Y", $this->date)),
                        "mm" => array("integer", date("m", $this->date)),
                        "dd" => array("integer", date("d", $this->date)),
                        "fulldate" => array("integer",
                                            date("Ymd", $this->date)
                        ),
                        "mem_cnt" => array("integer", count($members)),
                        "in_progress" => array("integer", $in_progress),
                        "completed" => array("integer", $completed),
                        "failed" => array("integer", $failed),
                        "not_attempted" => array("integer", $not_attempted)
                    );

                    $this->db->insert("obj_lp_stat", $set);
                    $count++;
                    $this->cron_manager->ping($this->getId());
                }
            }
        }
        return $count;
    }

    protected function gatherTypesData(): int
    {
        $count = 0;
        $data = ilTrQuery::getObjectTypeStatistics();
        foreach ($data as $type => $item) {
            // only save once per day
            $this->db->manipulate(
                "DELETE FROM obj_type_stat WHERE" .
                " type = " . $this->db->quote($type, "text") .
                " AND fulldate = " . $this->db->quote(
                    date("Ymd", $this->date),
                    "integer"
                )
            );

            $set = array(
                "type" => array("text", $type),
                "yyyy" => array("integer", date("Y", $this->date)),
                "mm" => array("integer", date("m", $this->date)),
                "dd" => array("integer", date("d", $this->date)),
                "fulldate" => array("integer", date("Ymd", $this->date)),
                "cnt_references" => array("integer", (int) $item["references"]),
                "cnt_objects" => array("integer", (int) $item["objects"]),
                "cnt_deleted" => array("integer", (int) $item["deleted"])
            );

            $this->db->insert("obj_type_stat", $set);

            $count++;
            $this->cron_manager->ping($this->getId());
        }
        return $count;
    }

    protected function gatherUserData(): int
    {
        $count = 0;
        $to = mktime(
            23,
            59,
            59,
            date("m", $this->date),
            date("d", $this->date),
            date("Y", $this->date)
        );

        $sql = "SELECT COUNT(DISTINCT(usr_id)) counter,obj_id FROM read_event" .
            " WHERE last_access >= " . $this->db->quote(
                $this->date,
                "integer"
            ) .
            " AND last_access <= " . $this->db->quote($to, "integer") .
            " GROUP BY obj_id";
        $set = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($set)) {
            // only save once per day
            $this->db->manipulate(
                "DELETE FROM obj_user_stat" .
                " WHERE fulldate = " . $this->db->quote(
                    date("Ymd", $this->date),
                    "integer"
                ) .
                " AND obj_id = " . $this->db->quote($row["obj_id"], "integer")
            );

            $iset = array(
                "obj_id" => array("integer", $row["obj_id"]),
                "yyyy" => array("integer", date("Y", $this->date)),
                "mm" => array("integer", date("m", $this->date)),
                "dd" => array("integer", date("d", $this->date)),
                "fulldate" => array("integer", date("Ymd", $this->date)),
                "counter" => array("integer", $row["counter"])
            );

            $this->db->insert("obj_user_stat", $iset);

            $count++;
            $this->cron_manager->ping($this->getId());
        }
        return $count;
    }
}
