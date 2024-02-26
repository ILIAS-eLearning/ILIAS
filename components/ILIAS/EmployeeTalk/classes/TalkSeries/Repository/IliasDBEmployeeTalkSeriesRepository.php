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

namespace ILIAS\EmployeeTalk\TalkSeries\Repository;

use ilObjEmployeeTalkSeries;
use ilObjUser;
use ilDBInterface;
use ILIAS\EmployeeTalk\TalkSeries\DTO\EmployeeTalkSerieSettingsDto;

/**
 * Class IliasDBEmployeeTalkSeriesRepository
 * @package ILIAS\EmployeeTalk\Talk\Repository
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
    public function findByOwnerAndEmployee(): array
    {
        $userId = $this->currentUser->getId();

        //TODO: Alter table talks and store series id, which makes the
        $result = $this->database->query("
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
            WHERE od.type = 'tals' AND (talk.employee = " . $this->database->quote($userId, 'integer') .
            " OR od.owner = " . $this->database->quote($userId, 'integer') .
            ") AND oRef.deleted is null");

        $talkSeries = [];
        while ($row = $result->fetchObject()) {
            $talkSeries[] = new ilObjEmployeeTalkSeries((int) $row->refId, true);
        }

        return $talkSeries;
    }

    public function storeEmployeeTalkSerieSettings(EmployeeTalkSerieSettingsDto $settings_dto): void
    {
        if ($this->hasStoredSettings($settings_dto->getObjectId())) {
            $this->database->update(
                'etal_serie',
                $this->getTableColumns($settings_dto),
                ['id' => ['integer', $settings_dto->getObjectId()]]
            );
            return;
        }
        $this->database->insert(
            'etal_serie',
            $this->getTableColumns($settings_dto)
        );
    }

    public function readEmployeeTalkSerieSettings(int $obj_id): EmployeeTalkSerieSettingsDto
    {
        $res = $this->database->query(
            'SELECT * FROM etal_serie WHERE id = ' . $this->database->quote($obj_id, 'integer')
        );

        $editing_locked = false;
        while ($row = $res->fetchObject()) {
            $editing_locked = (bool) $row->editing_locked;
        }

        return new EmployeeTalkSerieSettingsDto($obj_id, $editing_locked);
    }

    public function deleteEmployeeTalkSerieSettings(int $obj_id): void
    {
        $this->database->manipulate(
            'DELETE FROM etal_serie WHERE id = ' . $this->database->quote($obj_id, 'integer')
        );
    }

    protected function hasStoredSettings(int $obj_id): bool
    {
        $res = $this->database->query(
            'SELECT COUNT(*) AS count FROM etal_serie WHERE id = ' .
            $this->database->quote($obj_id, 'integer')
        );

        return $res->fetchObject()->count > 0;
    }

    protected function getTableColumns(EmployeeTalkSerieSettingsDto $settings_dto): array
    {
        return [
            'id' => ['integer', $settings_dto->getObjectId()],
            'editing_locked' => ['integer', (int) $settings_dto->isLockedEditing()],
        ];
    }
}
