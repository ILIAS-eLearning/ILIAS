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

namespace ILIAS\Filesystem\Util\Archive;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @deprecated This class is deprecated and will be removed with ILIAS 10. Please use the
 * Archives implementation instead.
 * @description LegacyArchives can be used to zip individual files or directories and extract a zip
 * file to a specified location. We should do without these possibilities as soon as possible,
 * but for the moment very many places in ILIAS use such functionalities.
 */
final class LegacyArchives
{
    use PathHelper;

    private string $base_temp_path;
    private Archives $archives;
    private ZipOptions $zip_options;
    private UnzipOptions $unzip_options;

    public function __construct()
    {
        $this->archives = new Archives();
        if (defined('ILIAS_DATA_DIR') && defined('CLIENT_ID')) {
            $this->base_temp_path = \ILIAS_DATA_DIR . '/' . \CLIENT_ID . '/temp';
        } else {
            $this->base_temp_path = sys_get_temp_dir();
        }

        $this->zip_options = new ZipOptions();
        $this->zip_options = $this->zip_options
            ->withZipOutputPath($this->base_temp_path);
        $this->unzip_options = new UnzipOptions();
    }


    /**
     * @deprecated Use \ILIAS\Filesystem\Util\Archive\Archives::zip() instead. Will be removed in ILIAS 10.
     */
    public function zip(string $directory_to_zip, string $path_to_output_zip): bool
    {
        $directory_to_zip = $this->normalizePath($directory_to_zip);
        $path_to_output_zip = $this->normalizePath($path_to_output_zip);

        $zip = $this->archives->zip(
            [],
            $this->zip_options
                ->withZipOutputPath(dirname($path_to_output_zip))
                ->withZipOutputName(basename($path_to_output_zip))
        );

        $zip->addDirectory($directory_to_zip);
        $zip_stream = $zip->get();

        return file_put_contents($path_to_output_zip, $zip_stream->getContents()) > 0;
    }

    /**
     * @deprecated Use \ILIAS\Filesystem\Util\Archive\Archives::unzip() instead. Will be removed in ILIAS 10.
     */
    public function unzip(
        string $path_to_zip,
        string $extract_to_path = null,
        bool $overwrite = false,
        bool $flat = false
    ): bool {
        $extract_to_path ??= dirname($path_to_zip);

        $unzip = $this->archives->unzip(
            Streams::ofResource(fopen($path_to_zip, 'rb')),
            $this->unzip_options
                ->withZipOutputPath($extract_to_path)
                ->withOverwrite($overwrite)
                ->withFlat($flat)
        );
        return $unzip->extract();
    }
}
