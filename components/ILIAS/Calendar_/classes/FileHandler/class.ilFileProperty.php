<?php

declare(strict_types=1);

namespace ILIAS\Calendar\FileHandler;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilFileProperty
{
    private ?string $absolute_path;
    private ?string $file_name;

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
}
