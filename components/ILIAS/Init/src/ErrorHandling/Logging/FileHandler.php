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

namespace ILIAS\Init\ErrorHandling\Logging;

use ilFileUtils;
use DateTimeImmutable;
use DateInterval;

class FileHandler
{
    private const string FILE_FORMAT = '.log';

    public function doesDirectoryExist(string $directory): bool
    {
        return is_dir($directory);
    }

    public function createFile(
        string $directory,
        string $file_name,
        string $content
    ): void {
        $this->createDirectoryIfNecessary($directory);

        $file_name = rtrim($directory, '/') . '/' . $file_name . self::FILE_FORMAT;
        $stream = fopen($file_name, 'wb+');
        fwrite($stream, $content);
        fclose($stream);
        chmod($file_name, 0755);
    }

    public function deleteFilesOlderThan(
        string $directory,
        int $cutoff_in_days
    ): int {
        $files = $this->readFilesInDirectory($directory);
        $delete_date = new DateTimeImmutable();
        $delete_date = $delete_date->sub(new DateInterval('P' . $cutoff_in_days . 'D'));

        $count = 0;
        foreach ($files as $file) {
            $file_path = rtrim($directory, '/') . '/' . $file;
            $file_date = date('Y-m-d', filemtime($file_path));

            if ($file_date <= $delete_date->format('Y-m-d')) {
                $this->deleteFile($file_path);
                $count++;
            }
        }
        return $count;
    }

    private function createDirectoryIfNecessary(string $directory): void
    {
        if (!$this->doesDirectoryExist($directory)) {
            ilFileUtils::makeDirParents($directory);
        }
    }

    private function deleteFile(string $file_path): void
    {
        unlink($file_path);
    }

    private function readFilesInDirectory(string $directory): array
    {
        $ret = [];

        $folder = dir($directory);
        while ($file_name = $folder->read()) {
            if (filetype(rtrim($directory, '/') . '/' . $file_name) != 'dir') {
                $ret[] = $file_name;
            }
        }
        $folder->close();

        return $ret;
    }
}
