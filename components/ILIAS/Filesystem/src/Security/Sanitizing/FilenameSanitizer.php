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

namespace ILIAS\Filesystem\Security\Sanitizing;

/**
 * The filename sanitizer verifies and fixes file name endings.
 * It will not check the file for invalid character or other potential
 * problems.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
interface FilenameSanitizer
{
    /**
     * This file suffix will be used to sanitize not whitelisted file names.
     */
    public const CLEAN_FILE_SUFFIX = 'sec';

    /**
     * Checks if the filename is prefixed with a valid whitelisted ending.
     *
     * @param string $filename The filename which should be checked for a whitelisted ending.
     *
     * @return bool True if the filename ending is whitelisted otherwise false.
     *
     */
    public function isClean(string $filename): bool;

    /**
     * Validates the file ending, with the filesystem whitelist provided by ILIAS.
     * If the filename is suffixed with an not listed file ending
     *
     * @param string $filename The filename which should be sanitized.
     *
     * @return string The filename with a valid ending.
     */
    public function sanitize(string $filename): string;
}
