<?php

class ilWebDAVMountInstructionsTableDataProvider
{
    public function __construct(ilWebDAVMountInstructionsRepository $a_mount_instructions_repository)
    {
        $this->mount_instructions_repository = $a_mount_instructions_repository;
    }

    public function getList()
    {
        $items = $this->mount_instructions_repository->getAllMountInstructions();
        return array('items' => $items,
                    'cnt' => count($items)
            );
    }
}
