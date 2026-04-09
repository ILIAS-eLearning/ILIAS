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
 * Interface for deserializers that can be used to read data from a file or memory using a specific data format (e.g.
 * XML, JSON, etc.).
 */
interface Deserializer
{
    /**
     * Open the source (file path or string content) and prepare the deserializer for reading. This method should be
     * called before any other method. If the deserializer is used for reading from memory, the path parameter may
     * contain the raw content (e.g. JSON string) instead of a file path.
     *
     * @param string $path The path to the file to read from or the raw content (e.g. when reading from memory)
     *
     * @return static
     */
    public function open(string $path): static;

    /**
     * Register a handler for a group of data. When process() is called, the handler will be invoked with the data of
     * the matching group.
     *
     * @param string $group The name of the group
     * @param callable(array): void $handler Callable that receives the group data
     */
    public function addHandler(string $group, callable $handler): void;

    /**
     * Read and process the data, invoking the registered handlers for each group found in the source.
     */
    public function process(): void;
}
