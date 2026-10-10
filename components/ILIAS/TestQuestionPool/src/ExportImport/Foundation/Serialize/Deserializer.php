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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Serialize;

/**
 * Interface for deserializers that can be used to read data from a file or memory using a specific data format (e.g.
 * XML, JSON, etc.).
 */
interface Deserializer
{
    /**
     * Registers a handler that is invoked during process() with the data of the matching group.
     *
     * @param callable(array): void $handler
     */
    public function addHandler(string $group, callable $handler): void;

    /**
     * Read and process the data, invoking the registered handlers for each group found in the source.
     */
    public function process(): void;
}
