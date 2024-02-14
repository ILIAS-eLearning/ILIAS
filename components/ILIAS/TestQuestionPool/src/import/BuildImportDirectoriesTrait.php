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

namespace ILIAS\TestQuestionPool\Import;

trait BuildImportDirectoriesTrait
{
    private string $import_temp_directory = CLIENT_DATA_DIR . DIRECTORY_SEPARATOR . 'temp';

    protected function buildImportDirectoriesFromImportFile(string $file_to_import): array
    {
        $subdir = basename($file_to_import, '.zip');
        return [
            $subdir,
            $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir,
            $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR . $subdir . '.xml',
            $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR . str_replace(
                ['qpl', 'tst'],
                'qti',
                $subdir
            ) . '.xml'
        ];
    }

    protected function getImportTempDirectory(): string
    {
        return $this->import_temp_directory;
    }

    protected function buildImportDirectoryFromImportFile(string $file_to_import): string
    {
        $subdir = basename($file_to_import, '.zip');
        return $this->import_temp_directory . DIRECTORY_SEPARATOR . $subdir;
    }
}
