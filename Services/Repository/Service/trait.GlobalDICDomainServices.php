<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

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
    protected \ilTree $repo_tree;
    protected \ilAccessHandler $access;
    protected \ilLanguage $lng;
    protected \ilObjUser $user;
    protected Refinery\Factory $refinery;
    protected RBACServices $rbac;
    protected \ilAppEventHandler $event;
    protected Filesystems $filesystem;
    protected ResourceStorage\Services $resource_storage;
    protected LoggingServices $logger;
    protected \ilSetting $settings;
    protected \ilObjectDefinition $object_definition;

    protected function initDomainServices(\ILIAS\DI\Container $DIC)
    {
        $this->repo_tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->rbac = $DIC->rbac();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->logger = $DIC->logger();
        $this->refinery = $DIC->refinery();
        $this->filesystem = $DIC->filesystem();
        $this->resource_storage = $DIC->resourceStorage();
        $this->event = $DIC->event();
        $this->settings = $DIC->settings();
        $this->object_definition = $DIC["objDefinition"];
    }

    public function repositoryTree() : \ilTree
    {
        return $this->repo_tree;
    }

    public function access() : \ilAccessHandler
    {
        return $this->access;
    }

    public function rbac() : RBACServices
    {
        return $this->rbac;
    }

    public function lng() : \ilLanguage
    {
        return $this->lng;
    }

    public function user() : \ilObjUser
    {
        return $this->user;
    }

    public function logger() : LoggingServices
    {
        return $this->logger;
    }

    public function refinery() : Refinery\Factory
    {
        return $this->refinery;
    }

    public function filesystem() : Filesystems
    {
        return $this->filesystem;
    }

    public function resourceStorage() : ResourceStorage\Services
    {
        return $this->resource_storage;
    }

    public function event() : \ilAppEventHandler
    {
        return $this->event;
    }

    public function settings() : \ilSetting
    {
        return $this->settings;
    }

    public function objectDefinition() : \ilObjectDefinition
    {
        return $this->object_definition;
    }
}
