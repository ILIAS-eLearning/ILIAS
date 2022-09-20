<?php

use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\ResourceStorage\Policy\WhiteAndBlacklistedFileNamePolicy;

/**
 * Class ilFileServicesPolicy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileServicesPolicy extends WhiteAndBlacklistedFileNamePolicy
{
    protected ilFileServicesSettings $settings;
    protected ilFileServicesFilenameSanitizer $sanitizer;

    public function __construct(ilFileServicesSettings $settings)
    {
        parent::__construct($settings->getBlackListedSuffixes(), $settings->getWhiteListedSuffixes());
        $this->sanitizer = new ilFileServicesFilenameSanitizer($settings);
    }

    public function prepareFileNameForConsumer(string $filename_with_extension): string
    {
        return $this->sanitizer->sanitize(basename($filename_with_extension));
    }
}
