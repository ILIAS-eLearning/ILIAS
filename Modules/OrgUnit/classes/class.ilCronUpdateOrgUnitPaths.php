<?php

/**
 * Class ilCronUpdateOrgUnitPaths
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilCronUpdateOrgUnitPaths extends ilCronJob
{
    public const ID = "orgunit_paths";
    protected ilDBInterface $db;
    protected ilLogger $log;
    protected ilTree $tree;

    private ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];

    }

    public function getId() : string
    {
        return self::ID;
    }

    public function getTitle() : string
    {
        return $this->lng->txt("update_orgunits");
    }

    public function getDescription() : string
    {
        return $this->lng->txt("update_orgunits_desc");
    }

    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

     public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }

    public function run() : ilCronJobResult
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
