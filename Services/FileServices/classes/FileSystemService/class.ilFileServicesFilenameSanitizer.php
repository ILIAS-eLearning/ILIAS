<?php

use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizerImpl;

/**
 * Class ilFileServicesFilenameSanitizer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileServicesFilenameSanitizer extends FilenameSanitizerImpl
{
    public function __construct(ilFileServicesSettings $settings)
    {
        parent::__construct($settings->getWhiteListedSuffixes());
    }
}
