<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Stakeholder\Repository;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface StakeholderDBRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class StakeholderDBRepository implements StakeholderRepository
{
    const TABLE_NAME = 'il_resource_stkh_u';
    const TABLE_NAME_REL = 'il_resource_stkh';
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
        return [self::TABLE_NAME, self::TABLE_NAME_REL];
    }

    public function register(ResourceIdentification $i, ResourceStakeholder $s) : bool
    {
        $identification = $i->serialize();
        $stakeholder_id = $s->getId();
        $stakeholder_class_name = $s->getFullyQualifiedClassName();

        if (strlen($stakeholder_id) > 64) {
            throw new \InvalidArgumentException('stakeholder ids MUST be shorter or equal to than 64 characters');
        }
        if (strlen($stakeholder_class_name) > 250) {
            throw new \InvalidArgumentException('stakeholder classnames MUST be shorter or equal to than 250 characters');
        }

        $r = $this->db->queryF(
            "SELECT " . self::IDENTIFICATION . " FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s AND stakeholder_id = %s",
            ['text', 'text'],
            [$identification, $stakeholder_id]
        );

        if ($r->numRows() === 0) {
            // CREATE
            $this->db->insert(
                self::TABLE_NAME,
                [
                    self::IDENTIFICATION => ['text', $identification],
                    'stakeholder_id' => ['text', $stakeholder_id],
                ]
            );
        }

        $r = $this->db->queryF(
            "SELECT id FROM " . self::TABLE_NAME_REL . " WHERE id = %s",
            ['text',],
            [$stakeholder_id]
        );
        if ($r->numRows() === 0) {

            $this->db->insert(
                self::TABLE_NAME_REL,
                [
                    'id' => ['text', $stakeholder_id],
                    'class_name' => ['text', $stakeholder_class_name],
                ]
            );
        }

        $this->cache[$identification][$stakeholder_id] = $s;

        return true;
    }

    public function deregister(ResourceIdentification $i, ResourceStakeholder $s) : bool
    {
        $r = $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::IDENTIFICATION . " = %s AND stakeholder_id = %s",
            ['text', 'text'],
            [$i->serialize(), $s->getId()]
        );
        unset($this->cache[$i->serialize()][$s->getId()]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getStakeholders(ResourceIdentification $i) : array
    {
        $rid = $i->serialize();
        if (isset($this->cache[$rid]) && is_array($this->cache[$rid])) {
            return $this->cache[$rid];
        }

        $r = $this->db->queryF(
            "SELECT class_name, stakeholder_id FROM " . self::TABLE_NAME . " 
            JOIN ".self::TABLE_NAME_REL." ON stakeholder_id = id
            WHERE " . self::IDENTIFICATION . " = %s",
            ['text'],
            [$rid]
        );
        while ($d = $this->db->fetchAssoc($r)) {
            $d['rid'] = $rid;
            $this->populateFromArray($d);
        }
        return $this->cache[$rid];
    }

    public function preload(array $identification_strings) : void
    {
        $r = $this->db->query(
            "SELECT rid, class_name, stakeholder_id FROM " . self::TABLE_NAME
            . " JOIN ".self::TABLE_NAME_REL." ON stakeholder_id = id 
            WHERE " . $this->db->in(self::IDENTIFICATION,
                $identification_strings, false, 'text')
        );
        while ($d = $this->db->fetchAssoc($r)) {
            $this->populateFromArray($d);
        }
    }

    public function populateFromArray(array $data) : void
    {
        $class_name = $data['class_name'];
        $stakeholder = new $class_name();
        $stakeholders[] = $stakeholder;
        $this->cache[$data['rid']][$data['stakeholder_id']] = $stakeholder;
    }
}
