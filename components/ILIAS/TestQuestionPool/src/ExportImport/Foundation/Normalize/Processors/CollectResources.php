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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\NormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\DenormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;
use Psr\Log\LoggerInterface;

/**
 * Collects resources during normalization and replaces mapped IDs during denormalization.
 */
class CollectResources implements Processor
{
    /**
     * @var array<string, StorableResource> $resources
     */
    private array $resources = [];

    /**
     * @var array<string, ResourceIdentification> $import_mapping
     */
    private array $import_mapping = [];

    public function __construct(
        private readonly IRSS            $irss,
        private readonly LoggerInterface $log
    )
    {
    }

    /**
     * Get all resources collected during normalization.
     *
     * @return array<string, StorableResource>
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Store a mapping of an old resource id to a new resource id.
     * This is used to replace the old resource ids with the new resource ids during denormalization.
     */
    public function storeMapping(string $old_id, ResourceIdentification $new_id): void
    {
        $this->import_mapping[$old_id] = $new_id;
    }

    /**
     * @inheritDoc
     */
    public function process(object $carry): void
    {
        if ($carry instanceof NormalizeCarry && $carry->value() instanceof ResourceIdentification) {
            $this->handleNormalization($carry->value());
        }

        if (
            $carry instanceof DenormalizeCarry
            && $carry->expected() === ResourceIdentification::class
            && $carry->hasResult()
        ) {
            $carry->setResult($this->replaceRid($carry->result()));
        }
    }

    private function handleNormalization(ResourceIdentification $rid): void
    {
        $this->resources[$rid->serialize()] = $this->irss->manage()->getResource($rid);
    }

    private function replaceRid(ResourceIdentification $rid): ResourceIdentification
    {
        $id = $rid->serialize();
        if (!isset($this->import_mapping[$id])) {
            $this->log->warning("Unresolved resource id {$id}");
            return $rid;
        }

        $this->log->debug("Replaced resource id {$id} with {$this->import_mapping[$id]->serialize()}");
        return $this->import_mapping[$id];
    }
}
