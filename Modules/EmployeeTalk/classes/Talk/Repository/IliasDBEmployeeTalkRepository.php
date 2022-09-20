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

namespace ILIAS\Modules\EmployeeTalk\Talk\Repository;

use ILIAS\Modules\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ilDBInterface;
use ilDateTime;
use ilTimeZone;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilOrgUnitPermissionQueries;
use ilObjEmployeeTalk;
use ilOrgUnitOperation;
use ILIAS\Modules\EmployeeTalk\Talk\EmployeeTalkPositionAccessLevel;

final class IliasDBEmployeeTalkRepository implements EmployeeTalkRepository
{
    private ilDBInterface $database;

    /**
     * IliasDBEmployeeTalkRepository constructor.
     * @param ilDBInterface $database
     */
    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    public function findAll(): array
    {
        $statement = $this->database->prepare('SELECT * FROM etal_data;');
        $statement = $statement->execute();
        $talks = [];
        while (($result = $statement->fetchObject()) !== null) {
            $talks[] = $this->parseFromStdClass($result);
        }

        $this->database->free($statement);

        return $talks;
    }

    public function findByEmployees(array $employees): array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM etal_data AS talk 
            WHERE ' . $this->database->in('employee', $employees, false, "integer")
        );
        $statement = $statement->execute();
        $talks = [];
        while (($result = $statement->fetchObject()) !== null) {
            $talks[] = $this->parseFromStdClass($result);
        }

        $this->database->free($statement);

        return $talks;
    }

    public function findByEmployeesAndOwner(array $employees, int $owner): array
    {
        $statement = $this->database->prepare('SELECT * FROM etal_data AS talk
            INNER JOIN object_data AS od ON od.obj_id = talk.object_id
            WHERE ' . $this->database->in('employee', $employees, false, "integer") . ' OR od.owner = ?;', ["integer"]);
        $statement = $statement->execute([$owner]);
        $talks = [];
        while (($result = $statement->fetchObject()) !== null) {
            $talks[] = $this->parseFromStdClass($result);
        }

        $this->database->free($statement);

        return $talks;
    }

    public function findTalksBetweenEmployeeAndOwner(int $employee, int $owner): array
    {
        $statement = $this->database->prepare('SELECT * FROM etal_data AS talk
            INNER JOIN object_data AS od ON od.obj_id = talk.object_id
            WHERE talk.employee = ? AND od.owner = ?;', ["integer", "integer"]);
        $statement = $statement->execute([$employee, $owner]);
        $talks = [];
        while (($result = $statement->fetchObject()) !== null) {
            $talks[] = $this->parseFromStdClass($result);
        }

        $this->database->free($statement);

        return $talks;
    }

    public function findUsersByPositionRights(int $user): array
    {
        return $managedUser;
    }

    public function findByObjectId(int $objectId): EmployeeTalk
    {
        $statement = $this->database->prepare('SELECT * FROM etal_data WHERE object_id=?;', ["integer"]);
        $statement = $statement->execute([$objectId]);
        $result = $statement->fetchObject();
        $this->database->free($statement);

        //TODO raise exception if result count is 0 or greater 1

        return $this->parseFromStdClass($result);
    }

    public function create(EmployeeTalk $talk): EmployeeTalk
    {
        $this->database->insert('etal_data', [
            'object_id'             => ['int', $talk->getObjectId()],
            'series_id'             => ['text', $talk->getSeriesId()],
            'start_date'            => ['int', $talk->getStartDate()->getUnixTime()],
            'end_date'              => ['int', $talk->getEndDate()->getUnixTime()],
            'all_day'               => ['int', $talk->isAllDay()],
            'location'              => ['text', $talk->getLocation()],
            'employee'              => ['int', $talk->getEmployee()],
            'completed'             => ['int', $talk->isCompleted()],
            'standalone_date'       => ['int', $talk->isStandalone()]
            ]);

        return $talk;
    }

    public function update(EmployeeTalk $talk): EmployeeTalk
    {
        $this->database->update('etal_data', [
            'series_id'             => ['text', $talk->getSeriesId()],
            'start_date'            => ['int', $talk->getStartDate()->getUnixTime()],
            'end_date'              => ['int', $talk->getEndDate()->getUnixTime()],
            'all_day'               => ['int', $talk->isAllDay()],
            'location'              => ['text', $talk->getLocation()],
            'employee'              => ['int', $talk->getEmployee()],
            'completed'             => ['int', $talk->isCompleted()],
            'standalone_date'       => ['int', $talk->isStandalone()]
        ], [
            'object_id'             => ['int', $talk->getObjectId()]
        ]);

        return $talk;
    }

    public function delete(EmployeeTalk $talk): void
    {
        $statement = $this->database->prepareManip('DELETE FROM etal_data WHERE object_id=?;', ["integer"]);
        $statement->execute([$talk->getObjectId()]);
        $this->database->free($statement);
    }

    public function findByEmployee(int $iliasUserId): array
    {
        $statement = $this->database->prepare('SELECT * FROM etal_data WHERE employee=?;', ["integer"]);
        $statement = $statement->execute([$iliasUserId]);

        $talks = [];
        while (($result = $statement->fetchObject()) !== null) {
            $talks[] = $this->parseFromStdClass($result);
        }

        $this->database->free($statement);

        return $talks;
    }

    public function findBySeries(string $seriesId): array
    {
        $statement = $this->database->prepare('SELECT * FROM etal_data WHERE series_id=?;', ["text"]);
        $statement = $statement->execute([$seriesId]);

        $talks = [];
        while (($result = $statement->fetchObject()) !== null) {
            $talks[] = $this->parseFromStdClass($result);
        }

        $this->database->free($statement);

        return $talks;
    }

    private function parseFromStdClass($stdClass): EmployeeTalk
    {
        return new EmployeeTalk(
            intval($stdClass->object_id),
            new ilDateTime($stdClass->start_date, IL_CAL_UNIX, ilTimeZone::UTC),
            new ilDateTime($stdClass->end_date, IL_CAL_UNIX, ilTimeZone::UTC),
            boolval($stdClass->all_day),
            $stdClass->series_id ?? '',
            $stdClass->location ?? '',
            intval($stdClass->employee),
            boolval($stdClass->completed),
            boolval($stdClass->standalone_date)
        );
    }
}
