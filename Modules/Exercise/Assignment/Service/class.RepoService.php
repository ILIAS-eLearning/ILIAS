<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalDataService;

/**
 * Assignment repos
 * @author Alexander Killing <killing@leifos.de>
 */
class RepoService
{
    protected \ilDBInterface $db;
    protected InternalDataService $data;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    public function randomAssignments() : Mandatory\RandomAssignmentsDBRepository
    {
        return new Mandatory\RandomAssignmentsDBRepository(
            $this->data,
            $this->db
        );
    }
}
