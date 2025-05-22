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

namespace ILIAS\Export\ImportHandler\SchemaFolder\Info;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\I\SchemaFolder\Info\HandlerInterface as SchemaInfoInterface;
use SplFileInfo;

class Handler implements SchemaInfoInterface
{
    protected Version $version;

    protected string $component;

    protected string $sub_type;

    protected SPlFileInfo $spl_file_info;

    public function withSplFileInfo(
        SplFileInfo $spl_file_info
    ): SchemaInfoInterface {
        $clone = clone $this;
        $clone->spl_file_info = $spl_file_info;
        return $clone;
    }

    public function withComponent(
        string $component
    ): SchemaInfoInterface {
        $clone = clone $this;
        $clone->component = $component;
        return $clone;
    }

    public function withSubtype(
        string $sub_type
    ): SchemaInfoInterface {
        $clone = clone $this;
        $clone->sub_type = $sub_type;
        return $clone;
    }

    public function withVersion(
        Version $version
    ): SchemaInfoInterface {
        $clone = clone $this;
        $clone->version = $version;
        return $clone;
    }

    public function getFile(): SplFileInfo
    {
        return $this->spl_file_info;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getSubtype(): string
    {
        return $this->sub_type;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }
}
