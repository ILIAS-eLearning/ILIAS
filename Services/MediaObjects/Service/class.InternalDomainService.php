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

namespace ILIAS\MediaObjects;

use ILIAS\DI\Container;
use ILIAS\MediaObjects\ImageMap\ImageMapManager;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\MediaObjects\MediaType\MediaTypeManager;
use ILIAS\MediaObjects\Tracking\TrackingManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
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

    public function imageMap(): ImageMapManager
    {
        return new ImageMapManager(
            $this->repo_service->imageMap()
        );
    }

    public function mediaType(): MediaTypeManager
    {
        return new MediaTypeManager();
    }

    public function tracking(): TrackingManager
    {
        return new TrackingManager(
            $this
        );
    }
}
