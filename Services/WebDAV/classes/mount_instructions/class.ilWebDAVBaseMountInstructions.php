<?php declare(strict_types = 1);

use ILIAS\FileUpload\Collection\Exception\ElementAlreadyExistsException;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
