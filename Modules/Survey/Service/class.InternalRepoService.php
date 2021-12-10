<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

use ILIAS\Survey\Execution;

/**
 * Survey internal data service
 * @author killing@leifos.de
 */
class InternalRepoService
{
    /**
     * @var InternalDataService
     */
    protected $data;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
    }

    public function execution() : Execution\RepoService
    {
        return new Execution\RepoService(
            $this->data,
            $this->db
        );
    }

    public function participants() : Participants\RepoService
    {
        return new Participants\RepoService(
            $this->data,
            $this->db
        );
    }

    public function code() : Code\CodeDBRepo
    {
        return new Code\CodeDBRepo(
            $this->data,
            $this->db
        );
    }

    public function settings() : Settings\SettingsDBRepository
    {
        return new Settings\SettingsDBRepository(
            $this->data,
            $this->db
        );
    }
}
