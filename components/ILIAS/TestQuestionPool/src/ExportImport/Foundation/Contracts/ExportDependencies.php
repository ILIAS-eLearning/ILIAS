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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfig;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\HandlerInterface as ExportWriter;
use ILIAS\Export\ExportHandler\I\Info\Export\Path\HandlerInterface as ExportPath;
use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ExportTarget;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\ExportStep;
use Psr\Log\LoggerInterface;

/**
 * Aggregates service dependencies and state information for the export process. It is used to pass the
 * services between the core exporter component, the xml-exporter, and the exporter class steps.
 */
interface ExportDependencies
{
    public function target(): ExportTarget;

    public function config(): ExportConfig;

    public function getOption(): string;

    public function getStep(): ExportStep;

    public function setStep(ExportStep $step): void;

    public function logger(): LoggerInterface;

    public function setLogger(LoggerInterface $logger): void;

    public function path(): ExportPath;

    public function setPathInfo(ExportPath $path_info): void;

    public function collector(): DataCollector;

    public function setCollector(DataCollector $collector): void;

    public function transformations(): Transformations;

    public function setTransformations(Transformations $transformations): void;

    public function serializer(): Serializer;

    public function setSerializer(Serializer $serializer): void;

    public function writer(): ExportWriter;

    public function setWriter(ExportWriter $writer): void;

    /**
     * @return list<array{component: string, entity: string, ids: array<string>}>
     */
    public function getDependencies(): array;

    /**
     * @param array<string> $ids
     */
    public function addDependency(string $component, string $entity, array $ids): void;

    public function getContent(): string;
}
