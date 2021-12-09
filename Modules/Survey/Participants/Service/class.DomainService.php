<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Participants;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;
use ILIAS\Survey\Execution\SessionManager;

/**
 * Participants domain service
 * @author killing@leifos.de
 */
class DomainService
{
    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    /**
     * @var InvitationsManager
     */
    protected $invitations_manager;

    protected static $managers = [];

    public function __construct(
        InternalDomainService $domain_service,
        InternalRepoService $repo_service
    ) {
        $this->domain_service = $domain_service;
        $this->repo_service = $repo_service;
        $this->invitations_manager = new InvitationsManager(
            $this->repo_service
        );
    }

    public function invitations() : InvitationsManager
    {
        return $this->invitations_manager;
    }

    public function status(\ilObjSurvey $survey, int $user_id) : StatusManager
    {
        if (!isset(self::$managers[StatusManager::class][$survey->getId()][$user_id])) {
            self::$managers[StatusManager::class][$survey->getId()][$user_id] =
                new StatusManager(
                    $this->domain_service,
                    $this->repo_service,
                    $survey,
                    $user_id
                );
        }
        return self::$managers[StatusManager::class][$survey->getId()][$user_id];
    }
}
