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

namespace ILIAS\Dataset;

use ILIAS\ResourceStorage\Resource\StorableResource;

class IRSSContainerExportConfig
{
    public function __construct(
        protected StorableResource $source_container,
        protected string $source_path,
        protected string $target_path
    ) {
    }

    public function getSourceContainer(): StorableResource
    {
        return $this->source_container;
    }

    public function getSourcePath(): string
    {
        return $this->source_path;
    }

    public function getTargetPath(): string
    {
        return $this->target_path;
    }
}
