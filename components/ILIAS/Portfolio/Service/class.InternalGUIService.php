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

namespace ILIAS\Portfolio;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\Portfolio\Administration\PortfolioRoleAssignmentTableBuilder;
use ILIAS\Portfolio\Page\PortfolioPageTableBuilder;
use ILIAS\Repository\Permission\CmdPermissionInterface;
use ILIAS\Portfolio\Permission\PortfolioCmdPermission;

class InternalGUIService
{
    use GlobalDICGUIServices;

    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalDataService $data_service,
        protected InternalDomainService $domain_service
    ) {
        $this->initGUIServices($DIC);
    }

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function cmdPerm(
        ?\ilPortfolioAccessHandler $portfolio_access,
        ?\ilAccessHandler $repo_access,
        int $node_id,
        ?StandardGUIRequest $request = null
    ): Permission\PortfolioCmdPermission {
        return new Permission\PortfolioCmdPermission(
            $this->domain_service->lng(),
            $portfolio_access,
            $repo_access,
            $this->mainTemplate(),
            $this->ctrl(),
            $request ?? $this->standardRequest(),
            $node_id
        );
    }


    public function settings(
    ): Settings\GUIService {
        return self::$instance["settings"] ??= new Settings\GUIService(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

    public function portfolioPageTableBuilder(
        int $portfolio_id,
        object $parent_gui,
        string $parent_cmd,
        PortfolioCmdPermission $cmd_permission
    ): PortfolioPageTableBuilder {
        return new PortfolioPageTableBuilder(
            $this->domain_service,
            $this,
            $portfolio_id,
            $parent_gui,
            $parent_cmd,
            $cmd_permission
        );
    }

    public function portfolioRoleAssignmentTableBuilder(
        bool $has_write_permission,
        object $parent_gui,
        string $parent_cmd
    ): PortfolioRoleAssignmentTableBuilder {
        return new PortfolioRoleAssignmentTableBuilder(
            $this->domain_service,
            $this,
            $has_write_permission,
            $parent_gui,
            $parent_cmd
        );
    }

}
