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

namespace ILIAS\Repository\Ownership;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\InternalDomainService;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalBase;

class OwnershipManagementRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected array $data = [];

    public function __construct(
        protected InternalDomainService $domain,
        protected int $user_id,
        protected array $objects,
        protected string $selected_type
    ) {
        $this->setData($this->objects, $this->selected_type);
    }

    public function setData(array $objects, string $selected_type): void
    {
        $access = $this->domain->access();
        $tree = $this->domain->repositoryTree();
        $is_admin = false;
        $a_type = '';

        if (!$this->user_id) {
            $is_admin = $access->checkAccess('visible', '', SYSTEM_FOLDER_ID);
        }

        $this->data = [];
        if (empty($objects[$selected_type])) {
            return;
        }

        foreach ($objects[$selected_type] as $id => $item) {
            $refs = \ilObject::_getAllReferences($id);
            if ($refs) {
                foreach ($refs as $ref_id) {
                    if (!$tree->isDeleted($ref_id)) {
                        if ($this->user_id) {
                            $readable = $access->checkAccessOfUser(
                                $this->user_id,
                                'read',
                                '',
                                $ref_id,
                                $a_type
                            );
                        } else {
                            $readable = $is_admin;
                        }

                        $this->data[] = [
                            'obj_id' => $id,
                            'ref_id' => $ref_id,
                            'id' => $ref_id,
                            'type' => \ilObject::_lookupType($id),
                            'title' => $item,
                            'path' => $this->buildPath($ref_id),
                            'readable' => $readable
                        ];
                    }
                }
            }
        }
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ['id', 'obj_id', 'ref_id']);
    }


    protected function buildPath(int $ref_id): string
    {
        $tree = $this->domain->repositoryTree();
        $path = '...';
        $counter = 0;
        $path_full = $tree->getPathFull($ref_id);
        foreach ($path_full as $data) {
            if (++$counter < (count($path_full) - 2)) {
                continue;
            }
            if ($ref_id != $data['ref_id']) {
                $path .= ' &raquo; ' . $data['title'];
            }
        }

        return $path;
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->data;

        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->data);
    }
}
