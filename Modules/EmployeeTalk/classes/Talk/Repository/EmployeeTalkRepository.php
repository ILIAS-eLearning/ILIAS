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

interface EmployeeTalkRepository
{
    public function findByObjectId(int $objectId): EmployeeTalk;

    /**
     * @param int $iliasUserId
     *
     * @return EmployeeTalk[]
     */
    public function findByEmployee(int $iliasUserId): array;

    /**
     * @param string $seriesId
     *
     * @return EmployeeTalk[]
     */
    public function findBySeries(string $seriesId): array;
    public function create(EmployeeTalk $talk): EmployeeTalk;
    public function update(EmployeeTalk $talk): EmployeeTalk;
    public function delete(EmployeeTalk $talk): void;
    /**
     * @param int[] $employees
     *
     * @return EmployeeTalk[]
     */
    public function findByEmployees(array $employees): array;

    /**
     * @param int[] $employees
     * @param int   $owner
     * @return EmployeeTalk[]
     */
    public function findByEmployeesAndOwner(array $employees, int $owner): array;

    /**
     * @param int $employee
     * @param int $owner
     * @return EmployeeTalk[]
     */
    public function findTalksBetweenEmployeeAndOwner(int $employee, int $owner): array;

    /**
     * @return EmployeeTalk[]
     */
    public function findAll(): array;
}
