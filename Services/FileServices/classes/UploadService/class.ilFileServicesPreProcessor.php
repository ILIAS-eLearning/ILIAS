<?php

use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\ResourceStorage\Policy\WhiteAndBlacklistedFileNamePolicy;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class ilFileServicesPolicy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileServicesPreProcessor extends BlacklistExtensionPreProcessor
{
    protected ilRbacSystem $rbac;
    
    private int $fileadmin_ref_id;
    
    public function __construct(
        ilRbacSystem $rbac,
        ilFileServicesSettings $settings,
        string $reason = 'Extension is blacklisted.',
        ?int $fileadmin_ref_id = null
    ) {
        parent::__construct($settings->getBlackListedSuffixes(), $reason);
        $this->rbac = $rbac;
        $this->fileadmin_ref_id = $fileadmin_ref_id ?? $this->determineFileAdminRefId();
    }
    
    public function process(FileStream $stream, Metadata $metadata) : ProcessingStatus
    {
        if ($this->rbac->checkAccess('upload_blacklisted_files', $this->fileadmin_ref_id)) {
            return new ProcessingStatus(ProcessingStatus::OK, 'Blacklist override by RBAC');
        }
        return parent::process($stream, $metadata);
    }
    
    private function determineFileAdminRefId() : int
    {
        $objects_by_type = ilObject2::_getObjectsByType('facs');
        $id = (int) reset($objects_by_type)['obj_id'];
        $references = ilObject2::_getAllReferences($id);
        return (int) reset($references);
    }
}
