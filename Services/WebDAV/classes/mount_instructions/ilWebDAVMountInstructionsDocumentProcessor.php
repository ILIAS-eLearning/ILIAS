<?php

interface ilWebDAVMountInstructionsDocumentProcessor
{
    /**
     * @param string $raw_mount_instructions
     * @return string
     */
    public function processMountInstructions(string $raw_mount_instructions) : string ;
}