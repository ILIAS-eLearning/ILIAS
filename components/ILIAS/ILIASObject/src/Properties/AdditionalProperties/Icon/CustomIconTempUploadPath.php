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

namespace ILIAS\ILIASObject\Properties\AdditionalProperties\Icon;

/**
 * Resolves a user-supplied temp file identifier to an absolute path that is
 * guaranteed to refer to a regular file inside ILIAS data/temp.
 */
class CustomIconTempUploadPath
{
    private string $absolute_path;

    /**
     * @throws InvalidArgumentException if the path is not a safe temp upload file reference
     */
    public function __construct(
        string $temp_file_name,
        string $ilias_data_dir
    ) {
        $this->absolute_path = $this->buildAndCheckSource(
            $this->buildAndCheckBaseName($temp_file_name),
            $ilias_data_dir
        );
    }

    public function getAbsolutePath(): string
    {
        return $this->absolute_path;
    }

    private function buildAndCheckBaseName(
        string $temp_file_name
    ): string {
        $base_name = basename(
            str_replace('\\', '/', $temp_file_name)
        );
        if ($base_name === '' || $base_name === '.' || $base_name === '..') {
            throw new \InvalidArgumentException(
                'Invalid temporary upload file name.'
            );
        }

        return $base_name;
    }

    private function buildAndCheckSource(
        string $base_name,
        string $ilias_data_dir
    ): string {
        $data_dir = rtrim($ilias_data_dir, '/\\');
        $temp_dir = "{$data_dir}/temp";
        $real_temp = $this->getRealPath($temp_dir);
        if ($real_temp === false) {
            throw new \InvalidArgumentException(
                'Temporary directory is not accessible.'
            );
        }

        $real_source = $this->getRealPath("{$temp_dir}/{$base_name}");
        if ($real_source === false || !is_file($real_source)) {
            throw new \InvalidArgumentException(
                'Temporary upload file not found.'
            );
        }

        if (!str_starts_with($real_source, "{$real_temp}/")) {
            throw new \InvalidArgumentException(
                'Temporary upload file is outside the temp directory.'
            );
        }

        return $real_source;
    }

    protected function getRealPath(
        string $path
    ): string|false {
        return realpath($path);
    }
}
