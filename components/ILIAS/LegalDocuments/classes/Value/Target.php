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

namespace ILIAS\LegalDocuments\Value;

class Target
{
    /**
     * @param string|list<string> $path
     */
    public function __construct(private $path, private readonly string $command = '')
    {
    }

    /**
     * @return string|list<string>
     */
    public function guiPath()
    {
        return $this->path;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function guiName(): string
    {
        $path = $this->guiPath();
        return is_array($path) ? $path[count($path) - 1] : $path;
    }
}
