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

namespace ILIAS\MediaCast;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\MediaCast\LearningProgress\LearningProgressManager;
use ILIAS\MediaCast\Settings\SettingsManager;

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

    public function notes(): \ILIAS\Notes\DomainService
    {
        return $this->dic->notes()->domain();
    }

    public function mediaCast(\ilObjMediaCast $media_cast): MediaCastManager
    {
        return self::$instance["cast"][$media_cast->getId()] ??= new MediaCastManager($media_cast);
    }

    public function learningProgress(\ilObjMediaCast $cast): LearningProgressManager
    {
        return self::$instance["lp"][$cast->getId()] ??= new LearningProgressManager($cast);
    }

    public function mediacastSettings(): SettingsManager
    {
        return self::$instance["settings"] ??= new SettingsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

}
