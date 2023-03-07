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
 *
 * @description This class is used to create a zip archive from a list of file-streams.
 * In most cases this will be used inside other Services such as the Filesystem Service or the IRSS.
 */
final class Archives
{
    use PathHelper;
    private ZipOptions $zip_options;
    private UnzipOptions $unzip_options;

    public function __construct()
    {
        $this->zip_options = new ZipOptions();
        $this->unzip_options = new UnzipOptions();
    }


    public function zip(array $file_streams, ?ZipOptions $zip_options = null): Zip
    {
        return new Zip(
            $this->mergeZipOptions($zip_options),
            ...$file_streams
        );
    }

    public function unzip(FileStream $zip, ?UnzipOptions $unzip_options = null): Unzip
    {
        return new Unzip(
            $this->mergeUnzipOptions($unzip_options),
            $zip
        );
    }


    protected function mergeZipOptions(?ZipOptions $zip_options): ZipOptions
    {
        if (null === $zip_options) {
            return $this->zip_options;
        }
        return $this->zip_options
            ->withZipOutputName($zip_options->getZipOutputName())
            ->withZipOutputPath($zip_options->getZipOutputPath());
    }

    protected function mergeUnzipOptions(?UnzipOptions $unzip_options): UnzipOptions
    {
        if (null === $unzip_options) {
            return $this->unzip_options;
        }
        return $this->unzip_options
            ->withZipOutputPath($unzip_options->getZipOutputPath())
            ->withOverwrite($unzip_options->isOverwrite())
            ->withFlat($unzip_options->isFlat());
    }
}
