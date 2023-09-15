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

namespace ILIAS\ResourceStorage\Resource\Repository;

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Flavour\FlavourIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FlavourDBRepository implements FlavourRepository
{
    protected const TABLE_NAME = 'il_resource_flavour';
    protected const F_RESOURCE_ID = 'rid';
    protected const F_REVISION = 'revision';
    protected const F_DEFINITION = 'definition_id';
    protected const F_VARIANT = 'variant';
    private array $results_cache = [];
    private \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }


    public function has(ResourceIdentification $rid, int $revision, FlavourDefinition $definition): bool
    {
        return $this->buildResult($rid, $revision, $definition, false)->numRows() > 0;
    }

    public function store(Flavour $flavour): void
    {
        if (!$this->has($flavour->getResourceId(), $flavour->getRevision(), $flavour->getDefinition())) {
            $this->db->insert(
                self::TABLE_NAME,
                [
                    self::F_RESOURCE_ID => ['text', $flavour->getResourceId()->serialize()],
                    self::F_REVISION => ['integer', $flavour->getRevision()],
                    self::F_DEFINITION => ['text', $flavour->getDefinition()->getId()],
                    self::F_VARIANT => ['text', $flavour->getDefinition()->getVariantName() ?? '']
                ]
            );
        }
    }

    public function get(ResourceIdentification $rid, int $revision, FlavourDefinition $definition): Flavour
    {
        return new Flavour(
            $definition,
            $rid,
            $revision
        );
    }


    public function delete(Flavour $flavour): void
    {
        $rid = $flavour->getResourceId();
        $definition = $flavour->getDefinition();

        $r = $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME
            . " WHERE " . self::F_RESOURCE_ID . " = %s AND "
            . self::F_REVISION . " = %s AND "
            . self::F_DEFINITION . " = %s AND (" . self::F_VARIANT . " = %s OR " . self::F_VARIANT . " IS NULL)",
            ['text', 'integer', 'text', 'text'],
            [$rid->serialize(), $flavour->getRevision(), $definition->getId(), $definition->getVariantName()]
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


    private function buildResult(
        ResourceIdentification $rid,
        int $revision,
        FlavourDefinition $definition,
        bool $use_cache = true
    ): \ilDBStatement {
        $rcache = $rid->serialize() . $definition->getId() . $definition->getVariantName();
        if ($use_cache && isset($this->results_cache[$rcache])) {
            return $this->results_cache[$rcache];
        }

        $r = $this->db->queryF(
            "SELECT *  FROM " . self::TABLE_NAME
            . " WHERE " . self::F_RESOURCE_ID . " = %s AND "
            . self::F_REVISION . " = %s AND "
            . self::F_DEFINITION . " = %s AND (" . self::F_VARIANT . " = %s OR " . self::F_VARIANT . " IS NULL)",
            [
                'text',
                'integer',
                'text',
                'text'
            ],
            [
                $rid->serialize(),
                $revision,
                $definition->getId(),
                $definition->getVariantName() ?? ''
            ]
        );
        return $this->results_cache[$rcache] = $r;
    }
}
