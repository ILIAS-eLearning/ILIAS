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

namespace ILIAS\MediaObjects;

use ILIAS\DI\Container;
use ILIAS\MediaObjects\ImageMap\ImageMapManager;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\MediaObjects\MediaType\MediaTypeManager;
use ILIAS\MediaObjects\Tracking\TrackingManager;
use ILIAS\MediaObjects\Metadata\MetadataManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalRepoService $repo_service,
        protected InternalDataService $data_service
    ) {
        $this->initDomainServices($DIC);
    }

    public function mediaObject(): MediaObjectManager
    {
        return self::$instance["mob"] ??= new MediaObjectManager(
            $this->data_service,
            $this->repo_service,
            $this,
            new \ilMobStakeholder()
        );
    }

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

    public function metadata(): MetadataManager
    {
        return new MetadataManager($this->learningObjectMetadata());
    }
}
