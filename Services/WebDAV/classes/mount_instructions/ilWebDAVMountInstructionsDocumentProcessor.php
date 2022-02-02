<?php declare(strict_types = 1);

interface ilWebDAVMountInstructionsDocumentProcessor
{
    public function processMountInstructions(string $a_raw_mount_instructions) : array ;
}
