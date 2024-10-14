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
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\MediaPool\Tree\MediaPoolTree;
use ILIAS\MediaPool\Metadata\MetadataManager;
use ILIAS\MediaPool\Settings\SettingsManager;

class InternalDomainService
{
    use GlobalDICDomainServices;
    protected static array $instance = [];
    protected Container $dic;

    public function __construct(
        Container $DIC,
        protected InternalRepoService $repo_service,
        protected InternalDataService $data_service
    ) {
        $this->dic = $DIC;
        $this->initDomainServices($DIC);
    }

    public function clipboard(): Clipboard\ClipboardManager
    {
        return self::$instance["clipboard"] ??= new Clipboard\ClipboardManager(
            $this->repo_service->clipboard()
        );
    }

    public function mediapool(int $obj_id): MediaPoolManager
    {
        return self::$instance["mediapool"][$obj_id] ??= new MediaPoolManager(
            $this,
            $obj_id
        );
    }

    public function tree(int $mep_obj_id): MediaPoolTree
    {
        return self::$instance["tree"][$mep_obj_id] ??= new MediaPoolTree($mep_obj_id);
    }

    public function metadata(): MetadataManager
    {
        return self::$instance["metadata"] ??= new MetadataManager($this->learningObjectMetadata());
    }

    public function mediapoolSettings(): SettingsManager
    {
        return self::$instance["settings"] ??= new SettingsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

}
