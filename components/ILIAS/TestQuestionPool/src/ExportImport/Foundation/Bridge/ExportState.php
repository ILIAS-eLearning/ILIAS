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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge;

use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfig;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\HandlerInterface as ExportWriter;
use ILIAS\Export\ExportHandler\I\Info\Export\Path\HandlerInterface as ExportPath;
use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ExportTarget;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\DataCollector;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Serializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use Psr\Log\LoggerInterface as Logger;
use RuntimeException;

/**
 * The ExportState is a data transfer object that contains the state of the export process. It is used to pass the
 * context between the core exporter component, the xml exporter and the exporter class steps.
 */
class ExportState
{
    private ExportStep $step = ExportStep::INIT;
    private ?Logger $logger = null;
    private ?ExportPath $path_info = null;
    private ?Transformations $transformations = null;
    private ?DataCollector $collector = null;
    private ?Serializer $serializer = null;
    private ?ExportWriter $writer = null;

    /**
     * @var array<string, array{component: string, entity: string, ids: array<string>}> $dependencies
     */
    private array $dependencies = [];

    public function __construct(
        private ExportTarget $target,
        private ExportConfig $config,
        private string $option = ''
    ) {
    }

    public function target(): ExportTarget
    {
        return $this->target;
    }

    public function config(): ExportConfig
    {
        return $this->config;
    }

    public function getOption(): string
    {
        return $this->option;
    }

    public function getStep(): ExportStep
    {
        return $this->step;
    }

    public function setStep(ExportStep $step): void
    {
        $this->step = $step;
    }

    public function assertStep(ExportStep $step): void
    {
        if ($this->step->value < $step->value) {
            throw new RuntimeException("Expected step {$step->name}, but got {$this->step->name} instead");
        }

        $this->step = $step;
    }

    public function logger(): Logger
    {
        $this->assertNotNull($this->logger, 'logger');
        return $this->logger;
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function path(): ExportPath
    {
        $this->assertNotNull($this->path_info, 'path_info');
        return $this->path_info;
    }

    public function setPathInfo(ExportPath $path_info): void
    {
        $this->path_info = $path_info;
    }

    public function collector(): DataCollector
    {
        $this->assertNotNull($this->collector, 'collector');
        return $this->collector;
    }

    public function setCollector(DataCollector $collector): void
    {
        $this->collector = $collector;
    }

    public function transformations(): Transformations
    {
        $this->assertNotNull($this->transformations, 'transformations');
        return $this->transformations;
    }

    public function setTransformations(Transformations $transformations): void
    {
        $this->transformations = $transformations;
    }

    public function serializer(): Serializer
    {
        $this->assertNotNull($this->serializer, 'serializer');
        return $this->serializer;
    }

    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function writer(): ExportWriter
    {
        $this->assertNotNull($this->writer, 'writer');
        return $this->writer;
    }

    public function setWriter(ExportWriter $writer): void
    {
        $this->writer = $writer;
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
        return $this->serializer()->write();
    }

    private function assertNotNull(mixed $value, string $property): void
    {
        if ($value === null) {
            throw new RuntimeException(
                "{$property} not set. This may happen if the exporter steps are not executed in the correct order."
            );
        }
    }
}
