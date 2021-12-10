<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;

/**
 * Execution repos
 * @author killing@leifos.de
 */
class DomainService
{
    /**
     * @var InternalRepoService
     */
    protected $repo_service;

    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    protected static $managers = [];

    public function __construct(
        InternalRepoService $repo_service,
        InternalDomainService $domain_service
    ) {
        $this->domain_service = $domain_service;
        $this->repo_service = $repo_service;
    }

    public function session(\ilObjSurvey $survey, int $user_id)
    {
        if (!isset(self::$managers[SessionManager::class][$survey->getId()][$user_id])) {
            self::$managers[SessionManager::class][$survey->getId()][$user_id] =
                new SessionManager(
                    $this->repo_service->execution()->anonymousSession(),
                    $survey,
                    $user_id,
                    $this->domain_service
                );
        }
        return self::$managers[SessionManager::class][$survey->getId()][$user_id];
    }

    public function run(\ilObjSurvey $survey, int $user_id) : RunManager
    {
        if (!isset(self::$managers[RunManager::class][$survey->getId()][$user_id])) {
            self::$managers[RunManager::class][$survey->getId()][$user_id] =
                new RunManager(
                    $this->repo_service,
                    $this->domain_service,
                    $survey,
                    $user_id
                );
        }
        return self::$managers[RunManager::class][$survey->getId()][$user_id];
    }
}
