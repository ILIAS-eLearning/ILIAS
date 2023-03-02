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
use ilDate;
use ilTimeZone;
use ILIAS\MyStaff\ilMyStaffAccess;

final class IliasDBEmployeeTalkRepository implements EmployeeTalkRepository
{
    private ilDBInterface $database;

    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @return EmployeeTalk[]
     */
    public function findAll(): array
    {
        $result = $this->database->query('SELECT * FROM etal_data');
        $talks = [];
        while ($row = $result->fetchObject()) {
            $talks[] = $this->parseFromStdClass($row);
        }
        return $talks;
    }

    /**
     * @param int[] $employees
     * @return EmployeeTalk[]
     */
    public function findByEmployees(array $employees): array
    {
        $result = $this->database->query(
            'SELECT * FROM etal_data AS talk 
            WHERE ' . $this->database->in('employee', $employees, false, 'integer')
        );
        $talks = [];
        while ($row = $result->fetchObject()) {
            $talks[] = $this->parseFromStdClass($row);
        }
        return $talks;
    }

    /**
     * @param int   $user
     * @param int[] $employees
     * @return EmployeeTalk[]
     */
    public function findByUserOrTheirEmployees(int $user, array $employees): array
    {
        $result = $this->database->query($q = 'SELECT * FROM etal_data AS talk
            INNER JOIN object_data AS od ON od.obj_id = talk.object_id
            WHERE (' . $this->database->in('employee', $employees, false, 'integer') .
            ' AND ' . $this->database->in('od.owner', $employees, false, 'integer') .
            ') OR od.owner = ' . $this->database->quote($user, 'integer') .
            ' OR employee = ' . $this->database->quote($user, 'integer'));
        $talks = [];
        while ($row = $result->fetchObject()) {
            $talks[] = $this->parseFromStdClass($row);
        }
        return $talks;
    }

    /**
     * @param int $employee
     * @param int $owner
     * @return EmployeeTalk[]
     */
    public function findTalksBetweenEmployeeAndOwner(int $employee, int $owner): array
    {
        $result = $this->database->query('SELECT * FROM etal_data AS talk
            INNER JOIN object_data AS od ON od.obj_id = talk.object_id
            WHERE talk.employee = ' . $this->database->quote($employee, 'integer') .
            ' AND od.owner = ' . $this->database->quote($owner, 'integer'));
        $talks = [];
        while ($row = $result->fetchObject()) {
            $talks[] = $this->parseFromStdClass($row);
        }
        return $talks;
    }

    public function findByObjectId(int $objectId): EmployeeTalk
    {
        $result = $this->database->query('SELECT * FROM etal_data WHERE object_id = ' .
            $this->database->quote($objectId, 'integer'));
        while ($row = $result->fetchObject()) {
            return $this->parseFromStdClass($row);
        }
        throw new \ilEmployeeTalkDBException('No EmployeeTalk found with obj_id ' . $objectId);
    }

    public function create(EmployeeTalk $talk): EmployeeTalk
    {
        $this->database->insert('etal_data', [
            'object_id'             => ['int', $talk->getObjectId()],
            'series_id'             => ['text', $talk->getSeriesId()],
            'start_date'            => ['int', $talk->getStartDate()->getUnixTime()],
            'end_date'              => ['int', $talk->getEndDate()->getUnixTime()],
            'all_day'               => ['int', (int) $talk->isAllDay()],
            'location'              => ['text', $talk->getLocation()],
            'employee'              => ['int', $talk->getEmployee()],
            'completed'             => ['int', (int) $talk->isCompleted()],
            'standalone_date'       => ['int', (int) $talk->isStandalone()]
            ]);

        return $talk;
    }

    public function update(EmployeeTalk $talk): EmployeeTalk
    {
        $this->database->update('etal_data', [
            'series_id'             => ['text', $talk->getSeriesId()],
            'start_date'            => ['int', $talk->getStartDate()->getUnixTime()],
            'end_date'              => ['int', $talk->getEndDate()->getUnixTime()],
            'all_day'               => ['int', (int) $talk->isAllDay()],
            'location'              => ['text', $talk->getLocation()],
            'employee'              => ['int', $talk->getEmployee()],
            'completed'             => ['int', (int) $talk->isCompleted()],
            'standalone_date'       => ['int', (int) $talk->isStandalone()]
        ], [
            'object_id'             => ['int', $talk->getObjectId()]
        ]);

        return $talk;
    }

    public function delete(EmployeeTalk $talk): void
    {
        $this->database->manipulate('DELETE FROM etal_data WHERE object_id = ' .
            $this->database->quote($talk->getObjectId(), 'integer'));
    }

    /**
     * @param int $iliasUserId
     * @return EmployeeTalk[]
     */
    public function findByEmployee(int $iliasUserId): array
    {
        $result = $this->database->query('SELECT * FROM etal_data WHERE employee = ' .
            $this->database->quote($iliasUserId, 'integer'));
        $talks = [];
        while ($row = $result->fetchObject()) {
            $talks[] = $this->parseFromStdClass($row);
        }
        return $talks;
    }

    /**
     * @param string $seriesId
     * @return EmployeeTalk[]
     */
    public function findBySeries(string $seriesId): array
    {
        $result = $this->database->query('SELECT * FROM etal_data WHERE series_id = ' .
            $this->database->quote($seriesId, 'text'));
        $talks = [];
        while ($row = $result->fetchObject()) {
            $talks[] = $this->parseFromStdClass($row);
        }
        return $talks;
    }

    private function parseFromStdClass($stdClass): EmployeeTalk
    {
        $all_day = boolval($stdClass->all_day);
        if ($all_day) {
            $start_date = new ilDate($stdClass->start_date, IL_CAL_UNIX);
            $end_date = new ilDate($stdClass->start_date, IL_CAL_UNIX);
        } else {
            $start_date = new ilDateTime($stdClass->start_date, IL_CAL_UNIX, ilTimeZone::UTC);
            $end_date = new ilDateTime($stdClass->start_date, IL_CAL_UNIX, ilTimeZone::UTC);
        }

        return new EmployeeTalk(
            intval($stdClass->object_id),
            $start_date,
            $end_date,
            $all_day,
            $stdClass->series_id ?? '',
            $stdClass->location ?? '',
            intval($stdClass->employee),
            boolval($stdClass->completed),
            boolval($stdClass->standalone_date)
        );
    }
}
