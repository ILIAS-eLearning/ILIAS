<?php

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

declare(strict_types=1);

namespace ILIAS\Category;

use ILIAS\DI;
use ILIAS\Repository;
use ILIAS\Catgory\AssignedRolesManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use Repository\GlobalDICDomainServices;

    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        DI\Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
    }

    public function assignedRoledRetrieval(
        int $ref_id,
        int $managed_user_id,
        int $managing_user_id
    ): AssignedRolesRetrieval {
        return new AssignedRolesRetrieval(
            $this,
            $ref_id,
            $managed_user_id,
            $managing_user_id
        );
    }

    public function assignedRolesManager(
        int $ref_id,
        int $managed_user_id,
        int $managing_user_id
    ): AssignedRolesManager {
        return new AssignedRolesManager(
            $this,
            $ref_id,
            $managed_user_id,
            $managing_user_id
        );
    }

}
