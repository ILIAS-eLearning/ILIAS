<?php

declare(strict_types=1);

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

namespace ILIAS\Repository\Resources;

use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Export\ImportStatus\Exception\ilException;

class ZipAdapter
{
    protected Archives $archives;

    public function __construct(
        Archives $archives
    ) {
        $this->archives = $archives;
    }

    public function unzipFile(string $filepath): void
    {
        $unzip = $this->archives->unzip(
            Streams::ofResource(fopen($filepath, 'rb')),
            (new UnzipOptions())
                ->withZipOutputPath(dirname($filepath))
                ->withOverwrite(false)
                ->withFlat(false)
                ->withEnsureTopDirectoy(false)
        );
        if (!$unzip->extract()) {
            throw new ilException("Unzip failed.");
        }
    }
}
