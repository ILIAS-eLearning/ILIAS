<?php

use ILIAS\FileUpload\Collection\Exception\ElementAlreadyExistsException;

abstract class ilWebDAVBaseMountInstructions
{
    /** @var ilWebDAVMountInstructionsRepository */
    protected $repo;

    /** @var ilWebDAVUriBuilder */
    protected $uri_builder;

    /** @var ilSetting */
    protected $settings;

    /** @var string */
    protected $language;

    public function __construct(ilWebDAVMountInstructionsRepository $a_repo,
        ilWebDAVUriBuilder $a_uri_builder,
        ilSetting $a_settings)
    {
        $this->repo = $a_repo;
        $this->uri_builder = $a_uri_builder;
        $this->settings = $a_settings;
    }

    public function getMountInstructionsAsArray() : array
    {
        $document = $this->repo->getMountInstructionsByLanguage($this->language);
        $processed = $document->getProcessedInstructions();
        $mount_instructions = json_decode($processed, true);
        $mount_instructions = $this->fillPlaceholdersForMountInstructions($mount_instructions);
        
        return $mount_instructions;
    }

    abstract protected function fillPlaceholdersForMountInstructions(array $mount_instructions) : array ;
}