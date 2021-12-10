<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Participants;

use ILIAS\Survey\InternalDataService;

/**
 * Participation repos
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

    public function invitations() : InvitationsDBRepository
    {
        return new InvitationsDBRepository(
            $this->data,
            $this->db
        );
    }
}
