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

use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Unzip;
use ILIAS\Filesystem\Util\Archive\ZipDirectoryHandling;

/**
 * Just a wrapper class to create Unit Test for other classes.
 * Can be remove when the static method calls have been removed
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUtilHelper
{
    private readonly \ILIAS\Filesystem\Util\Convert\LegacyImages $image_converter;
    private readonly \ILIAS\Filesystem\Util\Archive\Archives $archives;
    private readonly \ILIAS\FileDelivery\Services $delivery;

    public function __construct()
    {
        global $DIC;
        $this->image_converter = $DIC->fileConverters()->legacyImages();
        $this->archives = $DIC->archives();
        $this->delivery = $DIC->fileDelivery();
    }

    public function deliverData(string $data, string $fileName, string $mimeType): void
    {
        ilUtil::deliverData(
            $data,
            $fileName,
            $mimeType
        );
    }

    public function prepareFormOutput(string $string): string
    {
        return ilLegacyFormElementsUtil::prepareFormOutput($string);
    }

    public function convertImage(
        string $from,
        string $to,
        string $geometry = ''
    ): void {
        $this->image_converter->convertToFormat(
            $from,
            $to,
            ImageOutputOptions::FORMAT_JPG,
            $geometry === '' ? null : (int) $geometry,
            $geometry === '' ? null : (int) $geometry,
        );
    }

    public function stripSlashes(string $string): string
    {
        return ilUtil::stripSlashes($string);
    }

    /**
     * @param list<Streams> $streams
     */
    public function zipAndDeliver(array $streams, string $download_filename): void
    {
        $this->delivery->delivery()->attached(
            $this->archives->zip($streams)->get(),
            $download_filename
        );
    }

    public function getDir(string $copyDirectory): array
    {
        return ilFileUtils::getDir($copyDirectory);
    }

    public function unzip(string $file, string $zip_output_path, bool $overwrite): Unzip
    {
        return $this->archives->unzip(
            Streams::ofResource(fopen($file, 'rb')),
            $this->archives->unzipOptions()
                           ->withOverwrite($overwrite)
                           ->withZipOutputPath($zip_output_path)
                           ->withDirectoryHandling(ZipDirectoryHandling::KEEP_STRUCTURE)
        );
    }

    public function delDir(string $path): void
    {
        ilFileUtils::delDir($path);
    }

    /**
     * @throws ilException
     */
    public function moveUploadedFile(
        string $file,
        string $name,
        string $target,
        bool $raise_errors = true,
        string $mode = 'move_uploaded'
    ): bool {
        return ilFileUtils::moveUploadedFile(
            $file,
            $name,
            $target,
            $raise_errors,
            $mode
        );
    }

    public function getImagePath(
        string $img,
        string $module_path = "",
        string $mode = "output",
        bool $offline = false
    ): string {
        return ilUtil::getImagePath(
            $img,
            $module_path,
            $mode,
            $offline
        );
    }
}
