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

namespace ILIAS\Filesystem\Definitions;

/**
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class SuffixDefinitions
{
    public const SEC = ".sec";
    protected array $white_list = [];

    /**
     * @param mixed[] $black_list
     */
    public function __construct(array $white_list, protected array $black_list)
    {
        $this->white_list[] = '';
        $this->white_list = array_unique($white_list);
    }

    /**
     * @return mixed[]
     */
    public function getWhiteList(): array
    {
        return $this->white_list;
    }

    /**
     * @return mixed[]
     */
    public function getBlackList(): array
    {
        return $this->black_list;
    }

    /**
     * @deprecated Use ILIAS ResourceStorage to store files, there is no need to check valid filenames
     */
    public function getValidFileName(string $filename): string
    {
        if ($this->hasValidFileName($filename)) {
            return $filename;
        }
        $pi = pathinfo($filename);
        // if extension is not in white list, remove all "." and add ".sec" extension
        $basename = str_replace(".", "", $pi["basename"]);
        if (trim($basename) == "") {
            throw new \RuntimeException("Invalid upload filename.");
        }
        $basename .= self::SEC;
        if ($pi["dirname"] != "" && ($pi["dirname"] != "." || substr($filename, 0, 2) == "./")) {
            return $pi["dirname"] . "/" . $basename;
        }
        return $basename;
    }

    /**
     * @deprecated Use ILIAS ResourceStorage to store files, there is no need to check valid filenames
     */
    public function hasValidFileName(string $filename): bool
    {
        $pi = pathinfo($filename);

        return in_array(strtolower($pi["extension"]), $this->white_list)
            && !in_array(strtolower($pi["extension"]), $this->black_list);
    }
}
