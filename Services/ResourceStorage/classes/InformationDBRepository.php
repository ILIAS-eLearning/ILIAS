<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Information\Repository;

use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;

/**
 * Interface InformationDBRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class InformationDBRepository implements InformationRepository
{
    const TABLE_NAME = 'il_resource_info';
    const IDENTIFICATION = 'rid';

    /**
     * @var \ilDBInterface
     */
    protected $db;

    protected $cache = [];

    /**
     * @param \ilDBInterface $db
     */
    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getNamesForLocking() : array
    {
        return [self::TABLE_NAME];
    }

    /**
     * @inheritDoc
     */
    public function blank()
    {
        return new FileInformation();
    }

    /**
     * @inheritDoc
     */
    public function store(Information $information, Revision $revision) : void
    {
        $rid = $revision->getIdentification()->serialize();
        $r = $this->db->queryF(
            "SELECT " . self::IDENTIFICATION . " FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s AND version_number = %s",
            [
                'text',
                'integer'
            ],
            [
                $rid,
                $revision->getVersionNumber()
            ]
        );

        if ($r->numRows() > 0) {
            // UPDATE
            $this->db->update(
                self::TABLE_NAME,
                [
                    'title' => ['text', $information->getTitle()],
                    'mime_type' => ['text', $information->getMimeType()],
                    'suffix' => ['text', $information->getSuffix()],
                    'size' => ['integer', $information->getSize()],
                    'creation_date' => ['integer', $information->getCreationDate()->getTimestamp()],
                ],
                [
                    self::IDENTIFICATION => ['text', $rid],
                    'version_number' => ['integer', $revision->getVersionNumber()]
                ]
            );
        } else {
            // CREATE
            $this->db->insert(
                self::TABLE_NAME,
                [
                    self::IDENTIFICATION => ['text', $rid],
                    'version_number' => ['integer', $revision->getVersionNumber()],
                    'title' => ['text', $information->getTitle()],
                    'mime_type' => ['text', $information->getMimeType()],
                    'suffix' => ['text', $information->getSuffix()],
                    'size' => ['integer', $information->getSize()],
                    'creation_date' => ['integer', $information->getCreationDate()->getTimestamp()],
                ]
            );
        }
        $this->cache[$rid][$revision->getVersionNumber()] = $information;
    }

    /**
     * @inheritDoc
     */
    public function get(Revision $revision) : Information
    {
        $rid = $revision->getIdentification()->serialize();
        if (isset($this->cache[$rid][$revision->getVersionNumber()])) {
            return $this->cache[$rid][$revision->getVersionNumber()];
        }
        $r = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s AND version_number = %s",
            [
                'text',
                'integer'
            ],
            [
                $rid,
                $revision->getVersionNumber()
            ]
        );

        $d = $this->db->fetchAssoc($r);
        $i = $this->getFileInfoFromArrayData($d);

        $this->cache[$rid][$revision->getVersionNumber()] = $i;

        return $i;
    }

    public function delete(Information $information, Revision $revision) : void
    {
        $rid = $revision->getIdentification()->serialize();
        $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s AND version_number = %s",
            [
                'text',
                'integer'
            ],
            [
                $rid,
                $revision->getVersionNumber()
            ]
        );
        unset($this->cache[$rid][$revision->getVersionNumber()]);
    }

    public function preload(array $identification_strings) : void
    {
        $r = $this->db->query(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE " . $this->db->in(self::IDENTIFICATION,
                $identification_strings, false, 'text')
        );

        while ($d = $this->db->fetchAssoc($r)) {
            $this->populateFromArray($d);
        }
    }

    public function populateFromArray(array $data) : void
    {
        $this->cache[$data['rid']][$data['version_number']] = $this->getFileInfoFromArrayData($data);
    }

    private function getFileInfoFromArrayData(array $data) : FileInformation
    {
        $i = new FileInformation();
        $i->setTitle((string) $data['title']);
        $i->setSize((int) $data['size']);
        $i->setMimeType((string) $data['mime_type']);
        $i->setSuffix((string) $data['suffix']);
        $i->setCreationDate((new \DateTimeImmutable())->setTimestamp((int) $data['creation_date'] ?? 0));

        return $i;
    }
}
