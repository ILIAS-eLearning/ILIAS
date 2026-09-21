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

namespace ILIAS\MediaPool;

use ILIAS\DI\Container;
use ILIAS\AdvancedMetaData\Services\ServicesInterface;
use ILIAS\MediaObjects\Thumbs\ThumbsGUI;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\MediaPool\Clipboard\GUIService;
use ILIAS\MediaPool\PermanentLink\PermanentLinkManager;

class InternalGUIService
{
    use GlobalDICGUIServices;
    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalDataService $data_service,
        protected InternalDomainService $domain_service,
        protected MediaPoolRepository $media_pool_repository,
        protected ServicesInterface $advanced_metadata,
        protected \ilObjUser $user,
        protected ThumbsGUI $thumbs_gui
    ) {
        $this->initGUIServices($DIC);
    }

    public function standardRequest(): StandardGUIRequest
    {
        return self::$instance["request"] ??= new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function clipboard(): GUIService
    {
        return self::$instance["clipboard"] ??= new GUIService(
            $this->domain_service,
            $this,
            $this->user,
            $this->thumbs_gui
        );
    }

    public function mediaPoolTableBuilder(
        \ilObjMediaPool $media_pool,
        string $folder_par,
        string $mode,
        bool $all_objects,
        ?string $filter_command,
        ?string $reset_command,
        string $insert_command,
        object $parent_gui,
        string $parent_cmd,
        ?string $filter_title = null
    ): MediaPoolTableBuilder {
        return new MediaPoolTableBuilder(
            $this->domain_service,
            $this,
            $this->thumbs_gui,
            $this->media_pool_repository,
            $this->advanced_metadata,
            $media_pool,
            $folder_par,
            $mode,
            $all_objects,
            $filter_command,
            $reset_command,
            $insert_command,
            $parent_gui,
            $parent_cmd,
            $filter_title
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

    public function permanentLink(
        int $ref_id = 0
    ): PermanentLinkManager {
        return new PermanentLinkManager(
            $this->domain_service->staticUrl(),
            $this,
            $ref_id
        );
    }

}
