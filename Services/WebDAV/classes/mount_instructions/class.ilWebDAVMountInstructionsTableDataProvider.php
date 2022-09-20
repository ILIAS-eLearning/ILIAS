<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilWebDAVMountInstructionsTableDataProvider
{
    protected ilWebDAVMountInstructionsRepository $mount_instructions_repository;

    public function __construct(ilWebDAVMountInstructionsRepository $a_mount_instructions_repository)
    {
        $this->mount_instructions_repository = $a_mount_instructions_repository;
    }

    public function getList(): array
    {
        $items = $this->mount_instructions_repository->getAllMountInstructions();
        return array('items' => $items,
                    'cnt' => count($items)
            );
    }
}
