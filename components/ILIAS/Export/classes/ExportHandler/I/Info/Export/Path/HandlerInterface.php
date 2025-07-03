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

namespace ILIAS\Export\ExportHandler\I\Info\Export\Path;

interface HandlerInterface
{
    public function withPathToComponentExpDirInContainer(
        string $path_to_component_exp_dir
    ): HandlerInterface;

    public function withPathToComponentDirInContainer(
        string $path_to_component_dir
    ): HandlerInterface;

    public function withSetNumber(
        int $set_number
    ): HandlerInterface;

    public function withIsContainerExport(
        bool $is_contianer_export
    ): HandlerInterface;

    public function getPathToComponentExpDirInContainer(): string;

    public function getPathToComponentDirInContainer(): string;

    public function getSetNumber(): int;

    public function isContainerExport(): bool;
}
