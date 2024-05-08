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

namespace ILIAS\Filesystem\Security\Sanitizing;

use ilFileUtils;
use ILIAS\Filesystem\Util;

/**
 * Class FilenameSanitizerImpl
 *
 * Standard implementation of the filename sanitizing interface.
 *
 * @package ILIAS\Filesystem\Security\Sanitizising
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.1.0
 * @since 5.3.4
 */
class FilenameSanitizerImpl implements FilenameSanitizer
{
    /**
     * Contains the whitelisted file suffixes.
     *
     * @var string[] $whitelist
     */
    private array $whitelist;


    /**
     * FilenameSanitizerImpl constructor.
     */
    public function __construct(array $whitelist)
    {
        $this->whitelist = $whitelist;

        // the secure file ending must be valid, therefore add it if it got removed from the white list.
        if (!in_array(FilenameSanitizer::CLEAN_FILE_SUFFIX, $this->whitelist, true)) {
            $this->whitelist[] = FilenameSanitizer::CLEAN_FILE_SUFFIX;
        }
    }


    public function isClean(string $filename): bool
    {
        $suffix = $this->extractFileSuffix($filename);
        if (preg_match('/^ph(p[3457]?|t|tml|ar)$/i', $suffix)) {
            return false;
        }

        return in_array($suffix, $this->whitelist, true);
    }


    /**
     * @inheritDoc
     */
    public function sanitize(string $filename): string
    {
        $filename = Util::sanitizeFileName($filename);

        if ($this->isClean($filename)) {
            return $filename;
        }

        $pathInfo = pathinfo($filename);
        $basename = $pathInfo['basename'];
        $parentPath = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'];


        $filename = str_replace('.', '', $basename);
        $filename .= "." . FilenameSanitizer::CLEAN_FILE_SUFFIX;

        // there is no parent
        if ($parentPath === '') {
            return $filename;
        }

        return "$parentPath/$filename";
    }


    /**
     * Extracts the suffix from the given filename.
     * If no suffix was found an empty string will be returned.
     *
     * @param string $filename The filename which should be used to extract the file suffix.
     * @return string The file name suffix in lowercase.
     */
    private function extractFileSuffix(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
