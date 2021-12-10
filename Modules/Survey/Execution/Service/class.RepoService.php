<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalDataService;/**
 * Execution repos
 * @author killing@leifos.de
 */

class RepoService
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var InternalDataService
     */
    protected $data;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    public function anonymousSession() : AnonymousSessionRepo
    {
        return new AnonymousSessionRepo();
    }

    public function run() : RunDBRepository
    {
        return new RunDBRepository(
            $this->data,
            $this->db
        );
    }
}
