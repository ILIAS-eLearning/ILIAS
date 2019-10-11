<?php


class ilWebDAVObjectlessMountInstructions
{
    public function __construct(ilWebDAVMountInstructionsRepository $a_document_repository)
    {

    }


    public function getMountInstructionsAsArray() : array
    {
        $mount_instructions = array();

        $document = $this->document_repository->getMountInstructionsByLanguage('de');
        $processed = '{"WINDOWS": "Hello World", "MAC": "Test"}';//$document->getProcessedInstructions();
        $mount_instructions = json_decode($processed, true);

        return $mount_instructions;
    }

    protected function getMountInstructionsWithFilledPlaceholders(array $a_mount_instructions)
    {
        return $a_mount_instructions;
    }
}