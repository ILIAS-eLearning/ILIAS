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

    public function __construct()
    {
        $this->path_to_component_exp_dir = '';
        $this->path_to_component_dir = '';
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

    public function getPathToComponentExpDirInContainer(): string
    {
        return $this->path_to_component_exp_dir;
    }

    public function getPathToComponentDirInContainer(): string
    {
        return $this->path_to_component_dir;
    }
}
