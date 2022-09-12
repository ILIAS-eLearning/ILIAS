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
use ILIAS\ResourceStorage\Collection\Repository\CollectionRepository;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Collection\ResourceCollection;

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
        return [self::COLLECTION_TABLE_NAME, self::COLLECTION_ASSIGNMENT_TABLE_NAME];
    }

    public function blank(
        ResourceCollectionIdentification $identification,
        ?int $owner = null,
        ?string $title = null
    ): ResourceCollection {
        return new ResourceCollection(
            $identification,
            $owner ?? ResourceCollection::NO_SPECIFIC_OWNER,
            $title ?? ''
        );
    }

    public function existing(ResourceCollectionIdentification $identification): ResourceCollection
    {
        $q = "SELECT owner, title FROM " . self::COLLECTION_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s";
        $r = $this->db->queryF($q, ['text'], [$identification->serialize()]);
        $d = $this->db->fetchObject($r);
        $owner_id = (int)($d->owner ?? ResourceCollection::NO_SPECIFIC_OWNER);
        $title = (string)($d->title ?? '');

        return $this->blank($identification, $owner_id, $title);
    }


    public function has(ResourceCollectionIdentification $identification): bool
    {
        $q = "SELECT " . self::C_IDENTIFICATION . " FROM " . self::COLLECTION_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s";
        $r = $this->db->queryF($q, ['text'], [$identification->serialize()]);

        return ($r->numRows() === 1);
    }

    /**
     * @return \Generator|string[]
     */
    public function getResourceIdStrings(ResourceCollectionIdentification $identification): \Generator
    {
        $q = "SELECT " . self::R_IDENTIFICATION . " FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s ORDER BY position ASC";
        $r = $this->db->queryF($q, ['text'], [$identification->serialize()]);
        while ($d = $this->db->fetchAssoc($r)) {
            yield (string)$d[self::R_IDENTIFICATION];
        }
    }

    public function clear(ResourceCollectionIdentification $identification): void
    {
        $q = "DELETE FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME . " WHERE " . self::C_IDENTIFICATION . " = %s";
        $r = $this->db->manipulateF($q, ['text'], [$identification->serialize()]);
    }

    public function update(ResourceCollection $collection): void
    {
        $identification = $collection->getIdentification();
        $resource_identifications = $collection->getResourceIdentifications();
        $owner_id = $collection->getOwner();
        $title = $collection->getTitle();

        $resource_identification_strings = array_map(function (ResourceIdentification $i): string {
            return $i->serialize();
        }, $resource_identifications);

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
                'position' => ['integer', (int)$position + 1],
            ]);
        }
        foreach ($resource_identification_strings as $position => $resource_identification_string) {
            $this->db->update(
                self::COLLECTION_ASSIGNMENT_TABLE_NAME,
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()],
                    self::R_IDENTIFICATION => ['text', $resource_identification_string],
                    'position' => ['integer', (int)$position + 1],
                ],
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()],
                    self::R_IDENTIFICATION => ['text', $resource_identification_string],
                ]
            );
        }
        if ($this->has($identification)) {
            $this->db->update(
                self::COLLECTION_TABLE_NAME,
                [
                    self::C_IDENTIFICATION => ['text', $identification->serialize()],
                    'title' => ['text', $title ?? ''],
                    'owner' => ['integer', $owner_id],
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
                    'owner' => ['integer', $owner_id],
                ]
            );
        }
    }

    public function removeResourceFromAllCollections(ResourceIdentification $resource_identification): void
    {
        $this->db->manipulateF(
            "DELETE FROM " . self::COLLECTION_ASSIGNMENT_TABLE_NAME . " WHERE " . self::R_IDENTIFICATION . " = %s",
            ['text'],
            [$resource_identification->serialize()]
        );
    }


    public function preload(array $identification_strings): void
    {
        // TODO: Implement preload() method.
    }

    public function populateFromArray(array $data): void
    {
        // TODO: Implement populateFromArray() method.
    }
}
