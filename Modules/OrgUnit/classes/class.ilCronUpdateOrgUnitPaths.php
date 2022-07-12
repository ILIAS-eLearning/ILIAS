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

        $this->lng = $DIC->language();

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
