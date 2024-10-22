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
     * @var string|list<string>
     */
    private $path;

    /**
     * @param string|list<string> $path
     * @param array<string, string> $query_params
     */
    public function __construct($path, private readonly string $command = '', private readonly array $query_params = [])
    {
        $this->path = $path;
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

    /**
     * @return array<string, string>
     */
    public function queryParams(): array
    {
        return $this->query_params;
    }
}
