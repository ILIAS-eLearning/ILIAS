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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\DenormalizeCarry;
use Psr\Log\LoggerInterface;

/**
 * Pipe that collects all resources by their identification during normalization. During denormalization, it will replace
 * the resource ids with the mapped new resource ids.
 */
class CollectResources implements Pipe
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
        private readonly IRSS $irss,
        private readonly LoggerInterface $log
    ) {
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
    public function handle(mixed $passable, \Closure $next): mixed
    {
        if ($passable instanceof NormalizeCarry && $passable->value instanceof ResourceIdentification) {
            $this->handleNormalization($passable->value);
        }

        if ($passable instanceof DenormalizeCarry && $passable->expected === ResourceIdentification::class) {
            $passable->setResult(
                $this->replaceRid($passable->result())
            );
        }

        return $next($passable);
    }

    private function handleNormalization(ResourceIdentification $rid): void
    {
        $resource = $this->irss->manage()->getResource($rid);

        $this->resources[$rid->serialize()] = $resource;
    }

    private function replaceRid(ResourceIdentification $rid): ResourceIdentification
    {
        $id = $rid->serialize();
        if (isset($this->import_mapping[$id])) {
            $this->log->debug("Replaced resource id {$id} with {$this->import_mapping[$id]->serialize()}");
            return $this->import_mapping[$id];
        } else {
            $this->log->warning("Unresolved resource id {$id}");
            return $rid;
        }
    }
}
