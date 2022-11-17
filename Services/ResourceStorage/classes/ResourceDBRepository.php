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

namespace ILIAS\ResourceStorage\Resource\Repository;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class ResourceDBRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class ResourceDBRepository implements ResourceRepository
{
    public const TABLE_NAME = 'il_resource';
    public const IDENTIFICATION = 'rid';

    /**
     * @var \ILIAS\ResourceStorage\Resource\StorableResource[]
     */
    protected array $cache = [];
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return string[]
     */
    public function getNamesForLocking(): array
    {
        return [self::TABLE_NAME];
    }

    /**
     * @inheritDoc
     */
    public function blank(ResourceIdentification $identification): StorableResource
    {
        return new StorableFileResource($identification);
    }

    /**
     * @inheritDoc
     */
    public function get(ResourceIdentification $identification): StorableResource
    {
        if (isset($this->cache[$identification->serialize()])) {
            return $this->cache[$identification->serialize()];
        }
        $resource = $this->blank($identification);

        $q = "SELECT " . self::IDENTIFICATION . ", storage_id FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s";
        $r = $this->db->queryF($q, ['text'], [$identification->serialize()]);
        $d = $this->db->fetchObject($r);

        $resource->setStorageID($d->storage_id);

        $this->cache[$identification->serialize()] = $resource;

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function has(ResourceIdentification $identification): bool
    {
        if (isset($this->cache[$identification->serialize()])) {
            return true;
        }
        $q = "SELECT " . self::IDENTIFICATION . " FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s";
        $r = $this->db->queryF($q, ['text'], [$identification->serialize()]);

        return (bool)$r->numRows() > 0;
    }

    /**
     * @inheritDoc
     */
    public function store(StorableResource $resource): void
    {
        $rid = $resource->getIdentification()->serialize();
        if ($this->has($resource->getIdentification())) {
            // UPDATE
            $this->db->update(
                self::TABLE_NAME,
                [
                    self::IDENTIFICATION => ['text', $rid],
                    'storage_id' => ['text', $resource->getStorageID()],
                ],
                [
                    self::IDENTIFICATION => ['text', $rid],
                ]
            );
        } else {
            // CREATE
            $this->db->insert(
                self::TABLE_NAME,
                [
                    self::IDENTIFICATION => ['text', $rid],
                    'storage_id' => ['text', $resource->getStorageID()],
                ]
            );
        }
        $this->cache[$rid] = $resource;
    }

    /**
     * @inheritDoc
     */
    public function delete(StorableResource $resource): void
    {
        $rid = $resource->getIdentification()->serialize();
        $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s",
            ['text'],
            [$rid]
        );
        unset($this->cache[$rid]);
    }

    /**
     * @inheritDoc
     */
    public function getAll(): \Generator
    {
        yield from [];
    }

    public function preload(array $identification_strings): void
    {
        $r = $this->db->query(
            "SELECT rid, storage_id FROM " . self::TABLE_NAME . " WHERE "
            . $this->db->in(self::IDENTIFICATION, $identification_strings, false, 'text')
        );
        while ($d = $this->db->fetchAssoc($r)) {
            $this->populateFromArray($d);
        }
    }

    public function populateFromArray(array $data): void
    {
        $resource = $this->blank(new ResourceIdentification($data['rid']));
        $resource->setStorageID($data['storage_id']);
        $this->cache[$data['rid']] = $resource;
    }
}
