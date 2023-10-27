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

namespace Schema;

use ILIAS\Data\Version;
use SplFileInfo as SplFileInfo;

class ilXmlSchemaInfo
{
    private Version $version;

    private string $component;

    private string $sub_type;

    private SplFileInfo $file;

    public function __construct(SplFileInfo $file, string $component, string $sub_type, Version $version)
    {
        $this->file = $file;
        $this->component = $component;
        $this->sub_type = $sub_type;
        $this->version = $version;
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
