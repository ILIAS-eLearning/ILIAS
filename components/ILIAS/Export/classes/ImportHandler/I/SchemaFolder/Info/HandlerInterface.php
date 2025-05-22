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

namespace ILIAS\Export\ImportHandler\I\SchemaFolder\Info;

use ILIAS\Data\Version;
use SplFileInfo;

interface HandlerInterface
{
    public function withSplFileInfo(
        SplFileInfo $spl_file_info
    ): HandlerInterface;

    public function withComponent(
        string $component
    ): HandlerInterface;

    public function withSubtype(
        string $sub_type
    ): HandlerInterface;

    public function withVersion(
        Version $version
    ): HandlerInterface;

    public function getFile(): SplFileInfo;

    public function getComponent(): string;

    public function getSubtype(): string;

    public function getVersion(): Version;
}
