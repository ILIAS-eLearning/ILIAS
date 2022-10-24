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

namespace ILIAS\Repository;

use ILIAS\DI\RBACServices;
use ILIAS\DI\LoggingServices;
use ILIAS\Filesystem\Filesystems;
use ILIAS\ResourceStorage;
use ILIAS\Refinery;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
trait GlobalDICDomainServices
{
    private \ILIAS\DI\Container $DIC;

    protected function initDomainServices(\ILIAS\DI\Container $DIC): void
    {
        $this->DIC = $DIC;
    }

    public function repositoryTree(): \ilTree
    {
        return $this->DIC->repositoryTree();
    }

    public function access(): \ilAccessHandler
    {
        return $this->DIC->access();
    }

    public function rbac(): RBACServices
    {
        return $this->DIC->rbac();
    }

    public function lng(): \ilLanguage
    {
        return $this->DIC->language();
    }

    public function user(): \ilObjUser
    {
        return $this->DIC->user();
    }

    public function logger(): LoggingServices
    {
        return $this->DIC->logger();
    }

    public function refinery(): Refinery\Factory
    {
        return $this->DIC->refinery();
    }

    public function filesystem(): Filesystems
    {
        return $this->DIC->filesystem();
    }

    public function resourceStorage(): ResourceStorage\Services
    {
        return $this->DIC->resourceStorage();
    }

    public function event(): \ilAppEventHandler
    {
        return $this->DIC->event();
    }

    public function settings(): \ilSetting
    {
        return $this->DIC->settings();
    }

    public function objectDefinition(): \ilObjectDefinition
    {
        return $this->DIC["objDefinition"];
    }
}
