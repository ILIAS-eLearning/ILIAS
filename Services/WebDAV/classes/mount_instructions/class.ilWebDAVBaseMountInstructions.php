<?php declare(strict_types = 1);

use ILIAS\FileUpload\Collection\Exception\ElementAlreadyExistsException;

abstract class ilWebDAVBaseMountInstructions
{
    protected ilWebDAVMountInstructionsRepository $repo;
    protected ilWebDAVUriBuilder $uri_builder;
    protected ilSetting $settings;
    protected string $language;

    public function __construct(
        ilWebDAVMountInstructionsRepository $repo,
        ilWebDAVUriBuilder $uri_builder,
        ilSetting $settings,
        string $language
    ) {
        $this->repo = $repo;
        $this->uri_builder = $uri_builder;
        $this->settings = $settings;
        $this->language = $language;
    }

    public function getMountInstructionsAsArray(array $mount_instructions = []) : array
    {
        if (count($mount_instructions) == 0) {
            $document = $this->repo->getMountInstructionsByLanguage($this->language);
            $processed = $document->getProcessedInstructions();
            $mount_instructions = json_decode($processed, true);
        }
        
        return $this->fillPlaceholdersForMountInstructions($mount_instructions);
    }

    abstract protected function fillPlaceholdersForMountInstructions(array $mount_instructions) : array ;
}
