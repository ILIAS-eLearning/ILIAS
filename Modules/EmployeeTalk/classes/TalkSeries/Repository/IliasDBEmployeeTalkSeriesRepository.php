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
 *********************************************************************/

namespace ILIAS\Modules\EmployeeTalk\TalkSeries\Repository;

use ILIAS\Modules\EmployeeTalk\TalkSeries\Entity\EmployeeTalkSerieSettings;
use ilObjEmployeeTalkSeries;
use ilObjUser;
use ilDBInterface;
use ILIAS\Modules\EmployeeTalk\TalkSeries\DTO\EmployeeTalkSerieSettingsDto;

/**
 * Class IliasDBEmployeeTalkSeriesRepository
 * @package ILIAS\Modules\EmployeeTalk\Talk\Repository
 */
final class IliasDBEmployeeTalkSeriesRepository
{
    private ilObjUser $currentUser;
    private ilDBInterface $database;

    /**
     * IliasDBEmployeeTalkSeriesRepository constructor.
     * @param ilObjUser     $currentUser
     * @param ilDBInterface $database
     */
    public function __construct(ilObjUser $currentUser, ilDBInterface $database)
    {
        $this->currentUser = $currentUser;
        $this->database = $database;
    }

    /**
     * @return ilObjEmployeeTalkSeries[]
     */
    public function findByOwnerAndEmployee() : array
    {
        $userId = $this->currentUser->getId();

        //TODO: Alter table talks and store series id, which makes the
        $statement = $this->database->prepare("
            SELECT DISTINCT od.obj_id AS objId, oRef.ref_id AS refId
            FROM (
                SELECT tree.parent AS parent, talk.employee AS employee
                FROM etal_data AS talk
                     INNER JOIN object_reference AS oRef ON oRef.obj_id = talk.object_id
                     INNER JOIN tree ON tree.child = oRef.ref_id
                WHERE oRef.deleted IS NULL
                ) AS talk
            INNER JOIN object_reference AS oRef ON oRef.ref_id = talk.parent
            INNER JOIN object_data AS od ON od.obj_id = oRef.obj_id
            WHERE od.type = 'tals' AND (talk.employee = ? OR od.owner = ?) AND oRef.deleted is null;
              ", ["integer", "integer"]);
        $statement = $statement->execute([$userId, $userId]);

        $talkSeries = [];
        while (($result = $statement->fetchObject()) !== null) {
            $talkSeries[] = new ilObjEmployeeTalkSeries($result->refId, true);
        }

        $this->database->free($statement);

        return $talkSeries;
    }

    public function storeEmployeeTalkSerieSettings(EmployeeTalkSerieSettingsDto $settingsDto): void
    {
        $activeRecord = new EmployeeTalkSerieSettings();

        $activeRecord->setId($settingsDto->getObjectId());
        $activeRecord->setEditingLocked((int) $settingsDto->isLockedEditing());
        $activeRecord->store();
    }

    public function readEmployeeTalkSerieSettings(int $obj_id): EmployeeTalkSerieSettingsDto
    {
        /** @var EmployeeTalkSerieSettings $activeRecord */
        $activeRecord = EmployeeTalkSerieSettings::findOrGetInstance($obj_id);
        $activeRecord->setId($obj_id);

        return new EmployeeTalkSerieSettingsDto($obj_id, (bool) $activeRecord->getEditingLocked());
    }
}
