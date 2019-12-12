<?php

/**
 * Class ilCronUpdateOrgUnitPaths
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilCronUpdateOrgUnitPaths extends ilCronJob
{
    const ID = "orgunit_paths";
    /**
     * @var ilDB
     */
    protected $db;
    /**
     * @var ilLog
     */
    protected $log;
    /**
     * @var ilTree
     */
    protected $tree;


    /**
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt("update_orgunits");
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt("update_orgunits_desc");
    }


    /**
     * @return bool
     */
    public function hasAutoActivation()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }


    /**
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }


    /**
     *
     */
    public function getDefaultScheduleValue()
    {
        return;
    }


    /**
     * @return ilCronJobResult
     */
    public function run()
    {
        foreach (ilOrgUnitPathStorage::getAllOrguRefIds() as $ref_id) {
            ilOrgUnitPathStorage::writePathByRefId($ref_id);
        }
        ilOrgUnitPathStorage::clearDeleted();
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }
}
