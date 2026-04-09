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
 * Interface for serializers that can be used to write data to a file or memory using a specific data format (e.g. XML,
 * JSON, etc.).
 */
interface Serializer
{
    /**
     * Open a new file/memory and prepare the serializer for writing. This method should be called before any other
     * method. If the serializer is used for writing to memory, the path will be ignored.
     *
     * @param string $path The path to the file to write to
     */
    public function open(string $path): static;

    /**
     * Write the data to the file/memory and return the result. If the serializer is used for writing to memory, the
     * result will be returned as a string. If the serializer is used for writing to a file, the result will be the path
     * to the file.
     *
     * @return string The path to the file or the stringified data
     */
    public function write(): string;

    /**
     * Start a new group of data. Groups are used to structure the data into logical units.
     *
     * @param string $name The name of the group
     */
    public function startGroup(string $name): void;

    /**
     * End the current group of data. This method should be called after the last data has been written to the group. If
     * the group name does not match the name of the group that was started, an exception will be thrown.
     *
     * @param string $name The name of the group
     *
     * @throws \LogicException If the group name does not match the name of the group that was started
     */
    public function endGroup(string $name): void;

    /**
     * Start a new group of data, execute the callback and end the group.
     *
     * @param string $name The name of the group
     * @param callable(): void $callback Callback that will be executed when the group is started
     */
    public function group(string $name, callable $callback): void;

    /**
     * Append a data item to the current group.
     *
     * @param string $name The name of the item
     * @param array $data The data to append
     */
    public function append(string $name, array $data): void;
}
