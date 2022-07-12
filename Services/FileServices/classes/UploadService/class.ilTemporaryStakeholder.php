<?php

use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\ResourceStorage\Policy\WhiteAndBlacklistedFileNamePolicy;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class Temporary
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilTemporaryStakeholder extends AbstractResourceStakeholder
{
    protected int $owner_id;
    
    public function __construct()
    {
        global $DIC;
        
        $this->owner_id = $DIC->user()->getId();
    }
    
    public function getId() : string
    {
        return 'irss_temp';
    }
    
    public function getOwnerOfNewResources() : int
    {
        return $this->owner_id;
    }
}
