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

namespace ILIAS\Repository\Resources;

use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Export\ImportStatus\Exception\ilException;
use ILIAS\Filesystem\Util\Archive\LegacyArchives;
use ILIAS\Filesystem\Util\Archive\ZipDirectoryHandling;

class ZipAdapter
{
    protected Archives $archives;
    protected LegacyArchives $legacy_archives;

    public function __construct(
        Archives $archives,
        LegacyArchives $legacy_archives
    ) {
        $this->archives = $archives;
        $this->legacy_archives = $legacy_archives;
    }

    public function unzipFile(string $filepath): void
    {
        $unzip = $this->archives->unzip(
            Streams::ofResource(fopen($filepath, 'rb')),
            $this->archives->unzipOptions()
                ->withZipOutputPath(dirname($filepath))
                ->withOverwrite(false)
                ->withDirectoryHandling(ZipDirectoryHandling::KEEP_STRUCTURE)
        );
        if (!$unzip->extract()) {
            throw new ilException("Unzip failed.");
        }
    }

    public function zipDirectoryToFile(string $directory, string $zip_file): void
    {
        $this->legacy_archives->zip(
            $directory,
            $zip_file,
            true
        );
    }
}
