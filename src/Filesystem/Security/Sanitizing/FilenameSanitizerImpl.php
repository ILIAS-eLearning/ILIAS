<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Security\Sanitizing;

use ilFileUtils;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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


    /**
     * @inheritDoc
     */
    public function isClean(string $filename) : bool
    {
        return in_array($this->extractFileSuffix($filename), $this->whitelist, true);
    }


    /**
     * @inheritDoc
     */
    public function sanitize(string $filename) : string
    {
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
    private function extractFileSuffix(string $filename) : string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
