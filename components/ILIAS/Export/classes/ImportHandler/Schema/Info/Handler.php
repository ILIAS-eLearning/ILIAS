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

namespace ILIAS\Export\ImportHandler\Schema\Info;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\I\Schema\Info\HandlerInterface as SchemaInfoInterface;
use SplFileInfo;

class Handler implements SchemaInfoInterface
{
    private Version $version;

    private string $component;

    private string $sub_type;

    private SplFileInfo $file;

    public function withSplFileInfo(
        SplFileInfo $spl_file_info
    ): SchemaInfoInterface {
        $clone = clone $this;
        $clone->file = $spl_file_info;
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
        return $this->file;
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
