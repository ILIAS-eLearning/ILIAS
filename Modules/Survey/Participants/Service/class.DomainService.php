<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Participants;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;

/**
 * Participants domain service
 * @author killing@leifos.de
 */
class DomainService
{
    protected InternalRepoService $repo_service;
    protected InternalDomainService $domain_service;
    protected InvitationsManager $invitations_manager;

    /**
     * @var array<string, array<int, array<int, StatusManager>>>
     */
    protected static array $managers = [];

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
