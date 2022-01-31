<?php declare(strict_types = 1);

class ilWebDAVMountInstructionsTableDataProvider
{
    public function __construct(ilWebDAVMountInstructionsRepository $a_mount_instructions_repository)
    {
        $this->mount_instructions_repository = $a_mount_instructions_repository;
    }

    public function getList() : array
    {
        $items = $this->mount_instructions_repository->getAllMountInstructions();
        return array('items' => $items,
                    'cnt' => count($items)
            );
    }
}
