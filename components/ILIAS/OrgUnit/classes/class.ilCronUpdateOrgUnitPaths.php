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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\AbstractCronJob;

/**
 * Class ilCronUpdateOrgUnitPaths
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilCronUpdateOrgUnitPaths extends AbstractCronJob
{
    public const ID = "orgunit_paths";

    public function getId(): string
    {
        return self::ID;
    }

    public function getTitle(): string
    {
        return $this->language->txt("update_orgunits");
    }

    public function getDescription(): string
    {
        return $this->language->txt("update_orgunits_desc");
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function run(): JobResult
    {
        foreach (ilOrgUnitPathStorage::getAllOrguRefIds() as $ref_id) {
            ilOrgUnitPathStorage::writePathByRefId($ref_id);
        }
        ilOrgUnitPathStorage::clearDeleted();
        $result = new JobResult();
        $result->setStatus(JobResult::STATUS_OK);

        return $result;
    }
}
