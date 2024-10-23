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
//use ILIAS\Repository\Clipboard\ClipboardManager;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Portfolio\Settings\SettingsManager;

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
        $this->initDomainServices($DIC);
        $this->dic = $DIC;
    }

    public function portfolioSettings(): SettingsManager
    {
        return self::$instance["settings"] ??= new SettingsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function notes(): \ILIAS\Notes\DomainService
    {
        return $this->dic->notes()->domain();
    }
}
