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

namespace ILIAS\MetaData\Repository\Utilities;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Repository\Dictionary\ExpectedParameter;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Repository\Dictionary\ReturnedParameter;
use ILIAS\MetaData\Repository\Dictionary\LOMDictionaryInitiator;

class QueryExecutor implements QueryExecutorInterface
{
    protected \ilDBInterface $db;
    protected \ilLogger $logger;

    public function __construct(
        \ilDBInterface $db,
        \ilLogger $logger
    ) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function deleteAll(RessourceIDInterface $ressource_id): void
    {
        $rbac_id = $ressource_id->objID();
        $obj_id = $ressource_id->subID();
        $obj_type = $ressource_id->type();
        foreach (LOMDictionaryInitiator::TABLES as $table) {
            $query = "DELETE FROM " . $table . " " .
                "WHERE rbac_id = " . $this->db->quote($rbac_id, \ilDBConstants::T_INTEGER) . " " .
                "AND obj_id = " . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER) . " " .
                "AND obj_type = " . $this->db->quote($obj_type, \ilDBConstants::T_TEXT);

            $this->db->query($query);
        }
    }

    /**
     * @return string[]
     */
    public function read(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $super_id,
        int ...$parent_ids
    ): \Generator {
        $result = $this->execute(
            Action::READ,
            $tag,
            $ressource_id,
            0,
            '',
            $super_id,
            ...$parent_ids
        );
        while ($row = $this->db->fetchAssoc($result)) {
            yield $this->fetchID($tag, $row) => $this->fetchData($row);
        }
    }

    public function create(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        ?int $id,
        string $data_value,
        int $super_id,
        int ...$parent_ids
    ): int {
        $id = $super_id;
        if ($this->needsNextMDID($tag)) {
            $id = $this->db->nextId($tag->table());
        }
        $this->execute(
            Action::CREATE,
            $tag,
            $ressource_id,
            $id,
            $data_value,
            $super_id,
            ...$parent_ids
        );
        return $id;
    }

    protected function needsNextMDID(TagInterface $tag): bool
    {
        $expects_mdid = false;
        foreach ($tag->expectedParameters() as $parameter) {
            if ($parameter === ExpectedParameter::MD_ID) {
                $expects_mdid = true;
                break;
            }
        }
        return $expects_mdid && $tag->create();
    }

    public function update(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $id,
        string $data_value,
        int $super_id,
        int ...$parent_ids
    ): void {
        $this->execute(
            Action::UPDATE,
            $tag,
            $ressource_id,
            $id,
            $data_value,
            $super_id,
            ...$parent_ids
        );
    }

    public function delete(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $id,
        int $super_id,
        int ...$parent_ids
    ): void {
        $this->execute(
            Action::DELETE,
            $tag,
            $ressource_id,
            $id,
            '',
            $super_id,
            ...$parent_ids
        );
    }

    protected function fetchID(
        TagInterface $tag,
        array $row
    ): int {
        if (!isset($row[ReturnedParameter::MD_ID->value])) {
            throw new \ilMDRepositoryException(
                'Query for an element from table ' . $tag->table() .
                ' did not return an ID.'
            );
        }
        return (int) $row[ReturnedParameter::MD_ID->value];
    }

    protected function fetchData(
        array $row
    ): string {
        return $row[ReturnedParameter::DATA->value] ?? '';
    }

    protected function execute(
        Action $action,
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $id,
        string $data_value,
        int $super_id,
        int ...$parent_ids
    ): ?\ilDBStatement {
        $params = [];
        $param_types = [];
        foreach ($tag->expectedParameters() as $expected_param) {
            switch ($expected_param) {
                case ExpectedParameter::MD_ID:
                    if ($action !== Action::READ) {
                        $params[] = $id;
                        $param_types[] = \ilDBConstants::T_INTEGER;
                    }
                    break;

                case ExpectedParameter::PARENT_MD_ID:
                    if (empty($parent_ids)) {
                        throw new \ilMDRepositoryException(
                            'Parent ID is needed, but not set.'
                        );
                    }
                    $params[] = $parent_ids[array_key_last($parent_ids)];
                    $param_types[] = \ilDBConstants::T_INTEGER;
                    break;

                case ExpectedParameter::SECOND_PARENT_MD_ID:
                    if (count($parent_ids) < 2) {
                        throw new \ilMDRepositoryException(
                            'Second parent ID is needed, but not set.'
                        );
                    }
                    $params[] = $parent_ids[array_key_last($parent_ids) - 1];
                    $param_types[] = \ilDBConstants::T_INTEGER;
                    break;

                case ExpectedParameter::DATA:
                    if ($action === Action::CREATE || $action === Action::UPDATE) {
                        $params[] = $data_value;
                        $param_types[] = \ilDBConstants::T_TEXT;
                    }
                    break;

                case ExpectedParameter::SUPER_MD_ID:
                    $params[] = $super_id;
                    $param_types[] = \ilDBConstants::T_INTEGER;
                    break;

                case ExpectedParameter::RESSOURCE_IDS:
                    $params = array_merge(
                        $params,
                        [
                            $ressource_id->objID(),
                            $ressource_id->subID(),
                            $ressource_id->type()
                        ]
                    );
                    $param_types = array_merge(
                        $param_types,
                        [
                            \ilDBConstants::T_INTEGER,
                            \ilDBConstants::T_INTEGER,
                            \ilDBConstants::T_TEXT
                        ]
                    );
                    break;

                default:
                    throw new \ilMDRepositoryException(
                        'Unhandled expected parameter'
                    );
            }
        }
        switch ($action) {
            case Action::READ:
                return $this->db->queryF($tag->read(), $param_types, $params);

            case Action::CREATE:
                if ($tag->create()) {
                    $this->db->ManipulateF($tag->create(), $param_types, $params);
                }
                break;

            case Action::UPDATE:
                if ($tag->update()) {
                    $this->db->ManipulateF($tag->update(), $param_types, $params);
                }
                break;

            case Action::DELETE:
                if ($tag->delete()) {
                    $this->db->ManipulateF($tag->delete(), $param_types, $params);
                }
                break;

            default:
                throw new \ilMDRepositoryException(
                    'Unhandled manipulate action'
                );
        }
        return null;
    }
}
