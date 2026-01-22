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

use ILIAS\ResourceStorage\Collection\Repository\CollectionRepository;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Events\DataContainer;
use ILIAS\ResourceStorage\Events\CollectionData;

/**
 * Class CollectionDBRepository
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class CollectionDBRepository implements CollectionRepository
{
    public const COLLECTION_TABLE_NAME = 'il_resource_rc';
    public const COLLECTION_ASSIGNMENT_TABLE_NAME = 'il_resource_rca';
    public const R_IDENTIFICATION = 'rid';
    public const C_IDENTIFICATION = 'rcid';

    /** @var array<string, string[]> */
    private array $resource_ids_cache = [];

    public function __construct(protected \ilDBInterface $db)
    {
    }

    /**
     * @return string[]
     */
    public function getNamesForLocking(): array
    {
        return [self::COLLECTION_TABLE_NAME, self::COLLECTION_ASSIGNMENT_TABLE_NAME];
    }

    public function blank(
        ResourceCollectionIdentification $identification,
        ?int $owner_id = null,
        ?string $title = null
    ): ResourceCollection {
        $collection = new ResourceCollection(
            $identification,
            $owner_id ?? ResourceCollection::NO_SPECIFIC_OWNER,
            $title ?? ''
        );

        $rcid = $identification->serialize();
        if (!isset($this->resource_ids_cache[$rcid])) {
            $this->resource_ids_cache[$rcid] = [];
        }

        return $collection;
    }

    public function existing(ResourceCollectionIdentification $identification): ResourceCollection
    {
        $q = "SELECT owner_id, title FROM " . self::COLLECTION_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s";
        $r = $this->db->queryF($q, ['text'], [$identification->serialize()]);
        $d = $this->db->fetchObject($r);
        $owner_id = (int) ($d->owner_id ?? ResourceCollection::NO_SPECIFIC_OWNER);
        $title = (string) ($d->title ?? '');

        return $this->blank($identification, $owner_id, $title);
    }

    public function has(ResourceCollectionIdentification $identification): bool
    {
        $q = "SELECT EXISTS (
              SELECT 1 FROM " . self::COLLECTION_TABLE_NAME . " 
              WHERE " . self::C_IDENTIFICATION . " = %s
          ) AS found";

        $r = $this->db->queryF($q, ['text'], [$identification->serialize()]);
        $d = $this->db->fetchAssoc($r);

        return (bool) ($d['found'] ?? false);
    }

    /**
     * @return \Generator|string[]
     */
    public function getResourceIdStrings(ResourceCollectionIdentification $identification): \Generator
    {
        $rcid = $identification->serialize();

        if (!isset($this->resource_ids_cache[$rcid])) {
            $this->preload([$rcid]);
        }

        foreach ($this->resource_ids_cache[$rcid] ?? [] as $rid) {
            yield $rid;
        }
    }

    public function clear(ResourceCollectionIdentification $identification): void
    {
        $rcid = $identification->serialize();
        $q = "DELETE FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s";
        $this->db->manipulateF($q, ['text'], [$rcid]);

        $this->resource_ids_cache[$rcid] = [];
    }

    public function update(ResourceCollection $collection, DataContainer $event_data_container): void
    {
        $identification = $collection->getIdentification();
        $resource_identifications = $collection->getResourceIdentifications();
        $owner_id = $collection->getOwner();
        $title = $collection->getTitle();

        $resource_identification_strings = array_map(
            static fn(ResourceIdentification $i): string => $i->serialize(),
            $resource_identifications
        );

        $q = "DELETE FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s AND "
            . $this->db->in(self::R_IDENTIFICATION, $resource_identification_strings, true, 'text');
        $r = $this->db->manipulateF($q, ['text'], [$identification->serialize()]);

        $missing_resource_identification_string = array_diff(
            $resource_identification_strings,
            iterator_to_array($this->getResourceIdStrings($identification))
        );
        foreach ($missing_resource_identification_string as $position => $resource_identification_string) {
            $this->db->insert(self::COLLECTION_ASSIGNMENT_TABLE_NAME, [
                self::C_IDENTIFICATION => ['text', $identification->serialize()],
                self::R_IDENTIFICATION => ['text', $resource_identification_string],
                'position' => ['integer', (int) $position + 1],
            ]);
            $event_data_container->append(
                new CollectionData(['rid' => $resource_identification_string, 'rcid' => $identification->serialize()])
            );
        }
        foreach ($resource_identification_strings as $position => $resource_identification_string) {
            $this->db->update(
                self::COLLECTION_ASSIGNMENT_TABLE_NAME,
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()],
                    self::R_IDENTIFICATION => ['text', $resource_identification_string],
                    'position' => ['integer', (int) $position + 1],
                ],
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()],
                    self::R_IDENTIFICATION => ['text', $resource_identification_string],
                ]
            );
        }

        $this->resource_ids_cache[$identification->serialize()] = array_values($resource_identification_strings);
        if ($this->has($identification)) {
            $this->db->update(
                self::COLLECTION_TABLE_NAME,
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()],
                    'title' => ['text', $title ?? ''],
                    'owner_id' => ['integer', $owner_id],
                ],
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()]
                ]
            );
        } else {
            $this->db->insert(
                self::COLLECTION_TABLE_NAME,
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()],
                    'title' => ['text', $title ?? ''],
                    'owner_id' => ['integer', $owner_id],
                ]
            );
        }
    }

    public function removeResourceFromAllCollections(ResourceIdentification $resource_identification): void
    {
        $rid = $resource_identification->serialize();

        $this->db->manipulateF(
            "DELETE FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME . " WHERE " . self::R_IDENTIFICATION . " = %s",
            ['text'],
            [$rid]
        );

        foreach ($this->resource_ids_cache as $rcid => $rids) {
            if (in_array($rid, $rids, true)) {
                $this->resource_ids_cache[$rcid] = array_values(array_diff($rids, [$rid]));
            }
        }
    }

    public function delete(ResourceCollectionIdentification $identification): void
    {
        $rcid = $identification->serialize();

        $this->db->manipulateF(
            "DELETE FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s",
            ['text'],
            [$rcid]
        );
        $this->db->manipulateF(
            "DELETE FROM " . self::COLLECTION_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s",
            ['text'],
            [$rcid]
        );

        unset($this->resource_ids_cache[$rcid]);
    }

    public function preload(array $identification_strings): void
    {
        if ($identification_strings === []) {
            return;
        }

        $identification_strings = array_values(array_unique($identification_strings));

        $to_load = [];
        foreach ($identification_strings as $rcid) {
            if (!isset($this->resource_ids_cache[$rcid])) {
                $this->resource_ids_cache[$rcid] = [];
                $to_load[] = $rcid;
            }
        }

        if ($to_load === []) {
            return;
        }

        $q = "SELECT " . self::C_IDENTIFICATION . ", " . self::R_IDENTIFICATION .
            " FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME .
            " WHERE " . $this->db->in(self::C_IDENTIFICATION, $to_load, false, 'text') .
            " ORDER BY position ASC";

        $res = $this->db->query($q);
        while ($row = $this->db->fetchAssoc($res)) {
            $rcid = (string) $row[self::C_IDENTIFICATION];
            $rid = (string) $row[self::R_IDENTIFICATION];
            $this->resource_ids_cache[$rcid][] = $rid;
        }
    }

    /**
     * @param string[] $collection_identifications
     * @return ResourceIdentification[]
     */
    public function getResourceIdsForCollections(array $collection_identifications): array
    {
        if ($collection_identifications === []) {
            return [];
        }

        $collection_identifications = array_values(array_unique($collection_identifications));

        $to_preload = [];
        foreach ($collection_identifications as $rcid) {
            if (!isset($this->resource_ids_cache[$rcid])) {
                $to_preload[] = $rcid;
            }
        }
        if ($to_preload !== []) {
            $this->preload($to_preload);
        }

        $result_rids = [];
        foreach ($collection_identifications as $rcid) {
            foreach ($this->resource_ids_cache[$rcid] ?? [] as $rid) {
                $result_rids[] = $rid;
            }
        }

        $result_rids = array_values(array_unique($result_rids));

        return array_map(
            static fn(string $rid): ResourceIdentification => new ResourceIdentification($rid),
            $result_rids
        );
    }

    public function populateFromArray(array $data): void
    {
        // Nothing to do here
    }
}
