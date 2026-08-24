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

/**
 * An Exporter is responsible for orchestrating the export process. It prepares the dependencies, collects the data to
 * export, transforms the data and writes the data to the serializer and the file system.
 */
interface Exporter
{
    /**
     * Prepares the export by initializing the export components (collectors, transformations, etc.).
     *
     * Required dependencies:
     *  - Target
     *  - Config
     *  - Logger
     */
    public function prepare(ExportDependencies $state): void;

    /**
     * Processes the export by collecting the data to export, transforming the data and writing the data to the
     * serializer.
     *
     * Required dependencies:
     *  - Target
     *  - Config
     *  - Logger
     *  - Serializer
     */
    public function process(ExportDependencies $state): void;

    /**
     * Finalizes the export by persisting additional data (which cannot be provided by the serializer) to the file
     * system.
     *
     * Required dependencies:
     *  - Target
     *  - Config
     *  - Logger
     *  - Serializer
     *  - Writer
     *  - Path
     */
    public function write(ExportDependencies $state): void;
}
