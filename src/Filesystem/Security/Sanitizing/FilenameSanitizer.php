<?php

namespace ILIAS\Filesystem\Security\Sanitizing;

/**
 * Interface FilenameSanitizer
 *
 * The filename sanitizer verifies and fixes file name endings.
 * It will not check the file for invalid character or other potential
 * problems.
 *
 * @package ILIAS\Filesystem\Security\Sanitizising
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0
 * @since 5.3.4
 */
interface FilenameSanitizer
{

    /**
     * This file suffix will be used to sanitize not whitelisted file names.
     */
    const CLEAN_FILE_SUFFIX = 'sec';

    /**
     * Checks if the filename is prefixed with a valid whitelisted ending.
     *
     * @param string $filename The filename which should be checked for a whitelisted ending.
     *
     * @return bool True if the filename ending is whitelisted otherwise false.
     *
     * @version 1.0
     * @since 5.3.4
     */
    public function isClean(string $filename) : bool;


    /**
     * Validates the file ending, with the filesystem whitelist provided by ILIAS.
     * If the filename is suffixed with an not listed file ending
     *
     * @param string $filename The filename which should be sanitized.
     *
     * @return string The filename with a valid ending.
     */
    public function sanitize(string $filename) : string;
}
