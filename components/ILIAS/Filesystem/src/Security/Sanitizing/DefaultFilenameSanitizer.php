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

use ILIAS\Filesystem\Util;
use ILIAS\Filesystem\Configuration\FilesystemConfig;

/**
 * Standard implementation of the filename sanitizing interface.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
class DefaultFilenameSanitizer implements FilenameSanitizer
{
    private ?array $whitelist = null;

    public function __construct(
        private FilesystemConfig $settings
    ) {

    }

    private function getWhitelistedSuffixes(): array
    {
        if ($this->whitelist !== null) {
            return $this->whitelist;
        }

        $this->whitelist = array_diff($this->settings->getWhiteListedSuffixes(), $this->settings->getBlackListedSuffixes());

        // the secure file ending must be valid, therefore add it if it got removed from the white list.
        if (!in_array(FilenameSanitizer::CLEAN_FILE_SUFFIX, $this->whitelist, true)) {
            $this->whitelist[] = FilenameSanitizer::CLEAN_FILE_SUFFIX;
        }
        return $this->whitelist;
    }


    public function isClean(string $filename): bool
    {
        $suffix = $this->extractFileSuffix($filename);
        if (preg_match('/^ph(p[3457]?|t|tml|ar)$/i', $suffix)) {
            return false;
        }

        return in_array($suffix, $this->getWhitelistedSuffixes() ?? [], true);
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
