<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Recalculate quota for personal workspace
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 * @package Services/PersonalWorkspace
 */
class ilCronPersonalWorkspaceRecalculateQuota extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var int
     */
    protected $job_status;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $ilDB = $DIC->database();

        $this->db = $ilDB;
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return "pwsp_recalc_quota";
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        $lng = $this->lng;
        
        return $lng->txt("pwsp_recalculate_disk_quota");
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        $lng = $this->lng;

        return $lng->txt("pwsp_recalculate_disk_quota_desc");
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScheduleValue()
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomSettings()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->job_status = ilCronJobResult::STATUS_NO_ACTION;

        $this->recalculate();

        $this->job_status = ilCronJobResult::STATUS_OK;

        $result = new ilCronJobResult();
        $result->setStatus($this->job_status);
        return $result;
    }
    
    /**
     * Recalculate
     */
    public function recalculate()
    {
        $ilDB = $this->db;

        //
        // Files (workspace, blogs and portfolios)
        //

        $ilDB->manipulate("DELETE FROM il_disk_quota" .
            " WHERE src_type = " . $ilDB->quote("file", "text"));

        $quota_done = array();

        // get all workspace files
        $set = $ilDB->query("SELECT od.owner, od.obj_id" .
            " FROM object_data od" .
            " JOIN object_reference_ws ref ON (ref.obj_id = od.obj_id)" .
            " JOIN tree_workspace t ON (t.child = ref.wsp_id)" .
            " WHERE od.type = " . $ilDB->quote("file", "text") .
            " AND t.tree = od.owner");
        while ($row = $ilDB->fetchAssoc($set)) {
            $id = $row["owner"] . "-" . $row["obj_id"];
            if (!in_array($id, $quota_done)) {
                $this->quotaHandleFile($row["obj_id"], $row["owner"]);
                $quota_done[] = $id;
            }
        }

        // get all file usage for workspace blogs
        $set = $ilDB->query("SELECT od.owner, fu.id" .
            " FROM object_data od" .
            " JOIN object_reference_ws ref ON (ref.obj_id = od.obj_id)" .
            " JOIN tree_workspace t ON (t.child = ref.wsp_id)" .
            " JOIN il_blog_posting blp ON (blp.blog_id = od.obj_id)" .
            " JOIN file_usage fu ON (fu.usage_id = blp.id)" .
            " WHERE fu.usage_type = " . $ilDB->quote("blp:pg", "text") .
            " AND fu.usage_hist_nr = " . $ilDB->quote(0, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $id = $row["owner"] . "-" . $row["id"];
            if (!in_array($id, $quota_done)) {
                $this->quotaHandleFile($row["id"], $row["owner"]);
                $quota_done[] = $id;
            }
        }

        // get all file usage for portfolios
        $set = $ilDB->query($q = "SELECT od.owner, fu.id" .
            " FROM object_data od" .
            " JOIN usr_portfolio_page prtf ON (prtf.portfolio_id = od.obj_id)" .
            " JOIN file_usage fu ON (fu.usage_id = prtf.id)" .
            " WHERE fu.usage_type = " . $ilDB->quote("prtf:pg", "text") .
            " AND fu.usage_hist_nr = " . $ilDB->quote(0, "integer"));

        while ($row = $ilDB->fetchAssoc($set)) {
            $id = $row["owner"] . "-" . $row["id"];
            if (!in_array($id, $quota_done)) {
                $this->quotaHandleFile($row["id"], $row["owner"]);
                $quota_done[] = $id;
            }
        }


        //
        // Media objects (blogs and portfolios)
        //

        $ilDB->manipulate("DELETE FROM il_disk_quota" .
            " WHERE src_type = " . $ilDB->quote("mob", "text"));

        $quota_done = array();

        // get all mob usage for workspace blogs
        $set = $ilDB->query("SELECT od.owner, mu.id" .
            " FROM object_data od" .
            " JOIN object_reference_ws ref ON (ref.obj_id = od.obj_id)" .
            " JOIN tree_workspace t ON (t.child = ref.wsp_id)" .
            " JOIN il_blog_posting blp ON (blp.blog_id = od.obj_id)" .
            " JOIN mob_usage mu ON (mu.usage_id = blp.id)" .
            " WHERE mu.usage_type = " . $ilDB->quote("blp:pg", "text") .
            " AND mu.usage_hist_nr = " . $ilDB->quote(0, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $id = $row["owner"] . "-" . $row["id"];
            if (!in_array($id, $quota_done)) {
                $this->quotaHandleMob($row["id"], $row["owner"]);
                $quota_done[] = $id;
            }
        }

        // get all mob usage for portfolios
        $set = $ilDB->query("SELECT od.owner, mu.id" .
            " FROM object_data od" .
            " JOIN usr_portfolio_page prtf ON (prtf.portfolio_id = od.obj_id)" .
            " JOIN mob_usage mu ON (mu.usage_id = prtf.id)" .
            " WHERE mu.usage_type = " . $ilDB->quote("prtf:pg", "text") .
            " AND mu.usage_hist_nr = " . $ilDB->quote(0, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $id = $row["owner"] . "-" . $row["id"];
            if (!in_array($id, $quota_done)) {
                $this->quotaHandleMob($row["id"], $row["owner"]);
                $quota_done[] = $id;
            }
        }

        //
        // Portfolio / Blog images
        //

        $ilDB->manipulate("DELETE FROM il_disk_quota" .
            " WHERE src_type = " . $ilDB->quote("prtf", "text"));
        $ilDB->manipulate("DELETE FROM il_disk_quota" .
            " WHERE src_type = " . $ilDB->quote("blog", "text"));

        // portfolios
        $set = $ilDB->query("SELECT od.owner, od.obj_id" .
            " FROM object_data od" .
            " JOIN usr_portfolio prtf ON (prtf.id = od.obj_id)" .
            " WHERE od.type = " . $ilDB->quote("prtf", "text") .
            " AND prtf.img IS NOT NULL");
        while ($row = $ilDB->fetchAssoc($set)) {
            $this->quotaHandleFileStorage("prtf", $row["obj_id"], $row["owner"], "sec/ilPortfolio");
        }

        // (workspace) blogs
        $set = $ilDB->query("SELECT od.owner, od.obj_id" .
            " FROM object_data od" .
            " JOIN object_reference_ws ref ON (ref.obj_id = od.obj_id)" .
            " JOIN tree_workspace t ON (t.child = ref.wsp_id)" .
            " JOIN il_blog blog ON (blog.id = od.obj_id)" .
            " WHERE od.type = " . $ilDB->quote("blog", "text") .
            " AND blog.img IS NOT NULL" .
            " AND t.tree = od.owner");
        while ($row = $ilDB->fetchAssoc($set)) {
            $this->quotaHandleFileStorage("blog", $row["obj_id"], $row["owner"], "sec/ilBlog");
        }

        return;

        //
        // Verifications
        //

        $ilDB->manipulate("DELETE FROM il_disk_quota" .
            " WHERE src_type = " . $ilDB->quote("tstv", "text"));
        $ilDB->manipulate("DELETE FROM il_disk_quota" .
            " WHERE src_type = " . $ilDB->quote("excv", "text"));

        // (workspace) verifications
        $set = $ilDB->query("SELECT od.owner, od.obj_id, od.type" .
            " FROM object_data od" .
            " JOIN object_reference_ws ref ON (ref.obj_id = od.obj_id)" .
            " JOIN tree_workspace t ON (t.child = ref.wsp_id)" .
            " WHERE " . $ilDB->in("od.type", array("tstv", "excv"), "", "text") .
            " AND t.tree = od.owner");
        while ($row = $ilDB->fetchAssoc($set)) {
            $this->quotaHandleVerification($row["type"], $row["obj_id"], $row["owner"]);
        }
    }

    /**
     * @param int $a_obj_id
     * @param int $a_owner_id
     */
    public function quotaHandleFile($a_obj_id, $a_owner_id)
    {
        $ilDB = $this->db;

        // see ilFileSystemStorage::_createPathFromId()
        $tpath = array();
        $tfound = false;
        $tnum = $a_obj_id;
        for ($i = 3; $i > 0; $i--) {
            $factor = pow(100, $i);
            if (($tmp = (int) ($tnum / $factor)) or $tfound) {
                $tpath[] = $tmp;
                $tnum = $tnum % $factor;
                $tfound = true;
            }
        }

        $file_path = ilUtil::getDataDir() . "/ilFile/";
        if (count($tpath)) {
            $file_path .= (implode('/', $tpath) . '/');
        }
        $file_path .= "file_" . $a_obj_id;
        if (file_exists($file_path)) {
            $file_size = (int) ilUtil::dirsize($file_path);
            if ($file_size > 0) {
                $ilDB->manipulate("INSERT INTO il_disk_quota" .
                    " (owner_id, src_type, src_obj_id, src_size)" .
                    " VALUES (" . $ilDB->quote($a_owner_id, "integer") .
                    ", " . $ilDB->quote("file", "text") .
                    ", " . $ilDB->quote($a_obj_id, "integer") .
                    ", " . $ilDB->quote($file_size, "integer") . ")");
            }
        }
    }

    /**
     * @param int $a_obj_id
     * @param int $a_owner_id
     */
    public function quotaHandleMob($a_obj_id, $a_owner_id)
    {
        $ilDB = $this->db;

        $file_path = CLIENT_WEB_DIR . "/mobs/mm_" . $a_obj_id;
        if (file_exists($file_path)) {
            $file_size = (int) ilUtil::dirsize($file_path);
            if ($file_size > 0) {
                $ilDB->manipulate("INSERT INTO il_disk_quota" .
                    " (owner_id, src_type, src_obj_id, src_size)" .
                    " VALUES (" . $ilDB->quote($a_owner_id, "integer") .
                    ", " . $ilDB->quote("mob", "text") .
                    ", " . $ilDB->quote($a_obj_id, "integer") .
                    ", " . $ilDB->quote($file_size, "integer") . ")");
            }
        }
    }

    /**
     * @param string $a_type
     * @param int $a_obj_id
     * @param int $a_owner_id
     * @param string $a_dir
     */
    public function quotaHandleFileStorage($a_type, $a_obj_id, $a_owner_id, $a_dir)
    {
        $ilDB = $this->db;

        // see ilFileSystemStorage::_createPathFromId()
        $tpath = array();
        $tfound = false;
        $tnum = $a_obj_id;
        for ($i = 3; $i > 0; $i--) {
            $factor = pow(100, $i);
            if (($tmp = (int) ($tnum / $factor)) or $tfound) {
                $tpath[] = $tmp;
                $tnum = $tnum % $factor;
                $tfound = true;
            }
        }

        $file_path = CLIENT_WEB_DIR . "/" . $a_dir . "/";
        if (count($tpath)) {
            $file_path .= (implode('/', $tpath) . '/');
        }
        $file_path .= $a_type . "_" . $a_obj_id;

        if (file_exists($file_path)) {
            $file_size = (int) ilUtil::dirsize($file_path);
            if ($file_size > 0) {
                $ilDB->manipulate("INSERT INTO il_disk_quota" .
                    " (owner_id, src_type, src_obj_id, src_size)" .
                    " VALUES (" . $ilDB->quote($a_owner_id, "integer") .
                    ", " . $ilDB->quote($a_type, "text") .
                    ", " . $ilDB->quote($a_obj_id, "integer") .
                    ", " . $ilDB->quote($file_size, "integer") . ")");
            }
        }
    }

    /**
     * @param strin $a_type
     * @param int $a_obj_id
     * @param int $a_owner_id
     */
    public function quotaHandleVerification($a_type, $a_obj_id, $a_owner_id)
    {
        $ilDB = $this->db;

        // see ilFileSystemStorage::_createPathFromId()
        $tpath = array();
        $tfound = false;
        $tnum = $a_obj_id;
        for ($i = 3; $i > 0;$i--) {
            $factor = pow(100, $i);
            if (($tmp = (int) ($tnum / $factor)) or $tfound) {
                $tpath[] = $tmp;
                $tnum = $tnum % $factor;
                $tfound = true;
            }
        }

        $file_path = ilUtil::getDataDir() . "/ilVerification/";
        if (count($tpath)) {
            $file_path .= (implode('/', $tpath) . '/');
        }
        $file_path .= "vrfc_" . $a_obj_id;
        if (file_exists($file_path)) {
            $file_size = (int) ilUtil::dirsize($file_path);
            if ($file_size > 0) {
                $ilDB->manipulate("INSERT INTO il_disk_quota" .
                    " (owner_id, src_type, src_obj_id, src_size)" .
                    " VALUES (" . $ilDB->quote($a_owner_id, "integer") .
                    ", " . $ilDB->quote($a_type, "text") .
                    ", " . $ilDB->quote($a_obj_id, "integer") .
                    ", " . $ilDB->quote($file_size, "integer") . ")");
            }
        }
    }
}
