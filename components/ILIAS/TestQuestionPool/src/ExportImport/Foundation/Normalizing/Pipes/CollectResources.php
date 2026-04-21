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

/**
 * Pipe that collects all resources by their identification during normalization.
 */
class CollectResources implements Pipe
{
    /**
     * @var array<string, StorableResource> $resources
     */
    private array $resources = [];

    public function __construct(
        private readonly IRSS $irss
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(mixed $passable, \Closure $next): mixed
    {
        if ($passable instanceof NormalizeCarry && $passable->value instanceof ResourceIdentification) {
            $this->handleNormalization($passable->value);
        }

        return $next($passable);
    }

    private function handleNormalization(ResourceIdentification $rid): void
    {
        $resource = $this->irss->manage()->getResource($rid);

        $this->resources[$rid->serialize()] = $resource;
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
}
