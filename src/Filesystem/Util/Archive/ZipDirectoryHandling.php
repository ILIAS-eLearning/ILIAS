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

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
enum ZipDirectoryHandling: int
{
    /**
     * @description Will keep the top directory of the ZIP file if there is one (simple unzip).
     * This is the most common case and per default.
     */
    case KEEP_STRUCTURE = 1;

    /**
     * @description When unzipping, a directory is always created first, which has the name of
     * the zip to be unzipped. the rest of the structure is created within this
     * directory. If the ZIP only contains exactly one directory on the first level, which already has
     * the same name as the ZIP, no additional directory is created.
     */
    case ENSURE_SINGLE_TOP_DIR = 2;
    /**
     * @description Will strip the top directory if there is only one inside the ZIP.
     */
    // case STRIP_IF_ONLY_ONE = 4;
    /**
     * @description Will strip all directories and and will result in a flat structure with files only.
     */
    case FLAT_STRUCTURE = 8;
    /**
     * @description Will strip all directories and and will result in a flat structure with files only but inside
     * a directory with the zips name.
     */
    // case FLAT_STRUCTURE_WITH_SINGLE_TOP_DIR = 16;
}
