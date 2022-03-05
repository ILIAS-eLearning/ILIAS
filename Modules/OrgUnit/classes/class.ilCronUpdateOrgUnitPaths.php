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

    final public function getId() : string
    {
        return self::ID;
    }

    final public function getTitle() : string
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt("update_orgunits");
    }

    final public function getDescription() : string
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt("update_orgunits_desc");
    }

    final public function hasAutoActivation() : bool
    {
        return true;
    }

    final public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    final  public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    final public function getDefaultScheduleValue() : ?int
    {
        return null;
    }

    final public function run() : ilCronJobResult
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
