<?php

declare(strict_types=1);

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

namespace ILIAS\Awareness;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected Container $dic;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;
    /** @var array<int,WidgetManager> */
    protected static array $managers = array();
    /** @var array<int,User\Collector>  */
    protected static array $collectors = array();

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
        $this->dic = $DIC;
    }

    /*
    public function access(int $ref_id, int $user_id) : Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->access,
            $ref_id,
            $user_id
        );
    }*/

    public function widget(int $user_id, int $ref_id = 0): WidgetManager
    {
        if (!isset(self::$managers[$user_id])) {
            self::$managers[$user_id] = new WidgetManager(
                $user_id,
                $ref_id,
                $this->data_service,
                $this->repo_service,
                $this
            );
        }
        return self::$managers[$user_id];
    }

    public function admin(int $ref_id): AdminManager
    {
        return new AdminManager(
            $ref_id,
            $this->data_service,
            $this
        );
    }

    public function awarenessSettings(): \ilSetting
    {
        return new \ilSetting("awrn");
    }

    public function userProvider(): User\ProviderFactory
    {
        return new User\ProviderFactory($this->dic);
    }

    public function userCollector(int $user_id, int $ref_id = 0): User\Collector
    {
        if (!isset(self::$collectors[$user_id])) {
            self::$collectors[$user_id] = new User\Collector(
                $user_id,
                $ref_id,
                $this->data_service,
                $this->repo_service,
                $this
            );
        }
        return self::$collectors[$user_id];
    }
}
