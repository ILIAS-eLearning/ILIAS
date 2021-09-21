<?php declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise;

/**
 * Exercise domain service (business logic)
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    protected InternalDataService $data;
    protected InternalRepoService $repo;
    protected Assignment\DomainService $assignment_service;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->assignment_service = new Assignment\DomainService(
            $this,
            $repo
        );
    }

    public function assignment() : Assignment\DomainService
    {
        return $this->assignment_service;
    }
}
