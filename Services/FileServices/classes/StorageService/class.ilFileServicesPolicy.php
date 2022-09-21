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
    protected ?bool $bypass = null;

    public function __construct(ilFileServicesSettings $settings)
    {
        parent::__construct($settings->getBlackListedSuffixes(), $settings->getWhiteListedSuffixes());
        $this->sanitizer = new ilFileServicesFilenameSanitizer($settings);
    }

    public function prepareFileNameForConsumer(string $filename_with_extension): string
    {
        return $this->sanitizer->sanitize(basename($filename_with_extension));
    }

    private function determineFileAdminRefId(): int
    {
        $objects_by_type = ilObject2::_getObjectsByType('facs');
        $id = (int) reset($objects_by_type)['obj_id'];
        $references = ilObject2::_getAllReferences($id);
        return (int) reset($references);
    }

    public function isBlockedExtension(string $extension): bool
    {
        if ($this->bypass !== null && $this->bypass === true) {
            return false;
        }
        global $DIC;
        if (($this->bypass = $DIC->rbac()->system()->checkAccess(
            'upload_blacklisted_files',
            $this->determineFileAdminRefId()
        )) === true) {
            return false;
        }
        return parent::isBlockedExtension($extension);
    }
}
