<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Execution;

/**
 * Survey Runs
 *
 * @author killing@leifos.de
 */
class RunManager
{
    /**
     * @var RunDBRepository
     */
    protected $repo;

    /**
     * Constructor
     */
    public function __construct(RunDBRepository $repo = null)
    {
        $this->repo = (is_null($repo))
            ? new RunDBRepository()
            : $repo;
    }
}