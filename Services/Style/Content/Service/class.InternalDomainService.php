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

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access\StyleAccessManager;
use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Style\Content\Container\ContainerManager;
use ILIAS\Style\Content\Object\ObjectManager;
use ilRbacSystem;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected Container $dic;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;
    protected ilRbacSystem $rbacsystem;

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->rbacsystem = $DIC->rbac()->system();
        $this->repo_service = $repo_service;
        $this->initDomainServices($DIC);
        $this->dic = $DIC;
    }

    // access manager
    public function access(
        int $ref_id = 0,
        int $user_id = 0
    ) : StyleAccessManager {
        return new StyleAccessManager(
            $this->rbacsystem,
            $ref_id,
            $user_id
        );
    }

    public function characteristic(
        int $style_id,
        StyleAccessManager $access_manager
    ) : CharacteristicManager {
        return new CharacteristicManager(
            $style_id,
            $access_manager,
            $this->repo_service->characteristic(),
            $this->repo_service->characteristicCopyPaste(),
            $this->repo_service->color(),
            $this->user()
        );
    }

    public function color(
        int $style_id,
        StyleAccessManager $access_manager
    ) : ColorManager {
        return new ColorManager(
            $style_id,
            $access_manager,
            $this->repo_service->characteristic(),
            $this->repo_service->color()
        );
    }

    public function image(
        int $style_id,
        StyleAccessManager $access_manager
    ) : ImageManager {
        return new ImageManager(
            $style_id,
            $access_manager,
            $this->repo_service->image()
        );
    }

    public function repositoryContainer(int $ref_id) : ContainerManager
    {
        return new ContainerManager(
            $this->repo_service,
            $ref_id
        );
    }

    /**
     * Objects without ref id (e.g. portfolios) can use
     * the manager with a ref_id of 0, e.g. to get selectable styles
     */
    public function object(int $ref_id, int $obj_id = 0) : ObjectManager
    {
        return new ObjectManager(
            $this->repo_service,
            $this,
            $ref_id,
            $obj_id
        );
    }
}
