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
 * Writes data in a specific format. Groups structure the written data into logical units, items are appended to the
 * currently open group.
 */
interface Serializer
{
    public function write(): string;

    public function startGroup(string $name): void;

    public function endGroup(string $name): void;

    /**
     * Starts a group, executes the callback and ends the group.
     *
     * @param callable(): void $callback
     */
    public function group(string $name, callable $callback): void;

    /**
     * @param array<array-key, mixed> $data
     */
    public function append(string $name, array $data): void;
}
