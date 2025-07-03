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

namespace ILIAS\Export\ExportHandler\Info\Export\Path;

use ILIAS\Export\ExportHandler\I\Info\Export\Path\HandlerInterface as ExportPathInfoInterface;

class Handler implements ExportPathInfoInterface
{
    protected string $path_to_component_exp_dir;
    protected string $path_to_component_dir;
    protected int $set_number;
    protected bool $is_container_export;

    public function __construct()
    {
        $this->path_to_component_exp_dir = '';
        $this->path_to_component_dir = '';
        $this->set_number = 0;
        $this->is_container_export = false;
    }

    public function withPathToComponentExpDirInContainer(
        string $path_to_component_exp_dir
    ): ExportPathInfoInterface {
        $clone = clone $this;
        $clone->path_to_component_exp_dir = $path_to_component_exp_dir;
        return $clone;
    }

    public function withPathToComponentDirInContainer(
        string $path_to_component_dir
    ): ExportPathInfoInterface {
        $clone = clone $this;
        $clone->path_to_component_dir = $path_to_component_dir;
        return $clone;
    }

    public function withSetNumber(
        int $set_number
    ): ExportPathInfoInterface {
        $clone = clone $this;
        $clone->set_number = $set_number;
        return $clone;
    }

    public function withIsContainerExport(
        bool $is_contianer_export
    ): ExportPathInfoInterface {
        $clone = clone $this;
        $clone->is_container_export = $is_contianer_export;
        return $clone;
    }

    public function getPathToComponentExpDirInContainer(): string
    {
        return $this->path_to_component_exp_dir;
    }

    public function getPathToComponentDirInContainer(): string
    {
        return $this->path_to_component_dir;
    }

    public function getSetNumber(): int
    {
        return $this->set_number;
    }

    public function isContainerExport(): bool
    {
        return $this->is_container_export;
    }
}
