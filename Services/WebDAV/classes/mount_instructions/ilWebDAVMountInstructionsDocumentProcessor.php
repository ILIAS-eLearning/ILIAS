<?php

interface ilWebDAVMountInstructionsDocumentProcessor
{
    /**
     * @param string $a_raw_mount_instructions
     * @return string
     */
    public function processMountInstructions(string $a_raw_mount_instructions) : array ;
}
