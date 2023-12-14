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

namespace ILIAS\Calendar\FileHandler;

use ILIAS\BookingManager\getObjectSettingsCommand;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilFileProperty
{
    private ?string $absolute_path;
    private ?string $file_name;

    private string $file_rid;

    public function getAbsolutePath(): ?string
    {
        return $this->absolute_path;
    }

    public function setAbsolutePath(string $absolute_path): void
    {
        $this->absolute_path = $absolute_path;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): void
    {
        $this->file_name = $file_name;
    }

    public function setFileRId(string $file_rid): void
    {
        $this->file_rid = $file_rid;
    }

    public function getFileRId(): string
    {
        return $this->file_rid;
    }
}
