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

declare(strict_types=1);

namespace ILIAS\Category;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalBase;

class AssignedRolesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected array $data = [];

    public function __construct(
        protected InternalDomainService $domain,
        protected int $ref_id,
        protected int $managed_user_id,
        protected int $managing_user_id
    ) {
    }


    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ['id', 'obj_id', 'ref_id']);
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {

        $rbacreview = $this->domain->rbac()->review();
        $roles = $this->domain->assignedRolesManager(
            $this->ref_id,
            $this->managed_user_id,
            $this->managing_user_id
        )->getAssignableRoles();
        $ass_roles = $rbacreview->assignedRoles($this->managed_user_id);
        $lng = $this->domain->lng();

        $data = [];
        foreach ($roles as $role) {
            $role_obj = \ilObjectFactory::getInstanceByObjId((int) $role['obj_id']);

            $assigned = in_array((int) $role['obj_id'], $ass_roles, true);
            $title = $role_obj?->getTitle() ?: "";
            $desc = $role_obj?->getDescription() ?: "";
            $type = ($role['role_type'] ?? '') === 'global' ?
                $lng->txt('global') :
                $lng->txt('local');
            $data[] = [
                "assigned" => $assigned,
                "desc" => $desc,
                "title" => $title,
                "id" => $role['obj_id'],
                "type" => $type
            ];
        }

        $data = $this->applyOrder($data, $order);
        $this->data = $this->applyRange($data, $range);

        foreach ($this->data as $row) {
            yield $row;
        }
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->data);
    }
}
