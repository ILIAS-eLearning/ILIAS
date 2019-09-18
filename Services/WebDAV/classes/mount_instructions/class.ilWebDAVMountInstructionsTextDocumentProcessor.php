<?php


class ilWebDAVMountInstructionsTextDocumentProcessor extends ilWebDAVMountInstructionsDocumentProcessorBase
{

    public function processMountInstructions(string $raw_mount_instructions) : array
    {
        $stripped_instructions = htmlspecialchars($raw_mount_instructions);
        $stripped_instructions = nl2br($stripped_instructions);

        $processed_instructions = $this->parseInstructionsToAssocArray($stripped_instructions);

        return $processed_instructions;
    }
}