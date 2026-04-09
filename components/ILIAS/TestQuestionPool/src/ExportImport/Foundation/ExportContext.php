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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfig;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Serializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use RuntimeException;

/**
 * The ExportContext is a data transfer object that contains the context of the export process. It is used to pass the
 * context between the exporter steps and to store the result of the export process. Its intended to only be modified by
 * the exporter steps.
 */
class ExportContext
{
    private ?Serializer $serializer = null;

    /**
     * @var array<string, array{component: string, entity: string, ids: array<string>}> $dependencies
     */
    private array $dependencies = [];

    public function __construct(
        private ObjectId $pool_id,
        private ExportConfig $config,
        private Transformations $transformations,
    ) {
    }

    public function getPoolId(): ObjectId
    {
        return $this->pool_id;
    }

    public function getConfig(): ExportConfig
    {
        return $this->config;
    }

    public function getTransformations(): Transformations
    {
        return $this->transformations;
    }

    public function getSerializer(): Serializer
    {
        if ($this->serializer === null) {
            throw new RuntimeException(
                'Serializer not set. This may happen if the exporter steps are not executed in the correct order.'
            );
        }

        return $this->serializer;
    }

    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function getDependencies(): array
    {
        return array_values($this->dependencies);
    }

    public function addDependency(string $component, string $entity, array $ids): void
    {
        $key = "{$component}::{$entity}";

        if (!isset($this->dependencies[$key])) {
            $this->dependencies[$key] = [
                'component' => $component,
                'entity' => $entity,
                'ids' => [],
            ];
        }

        $this->dependencies[$key]['ids'] = array_values(array_unique(array_merge(
            $this->dependencies[$key]['ids'],
            $ids
        )));
    }

    public function getContent(): string
    {
        return $this->getSerializer()->write();
    }
}
