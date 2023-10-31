<?php

declare(strict_types=1);

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

namespace ILIAS\Container\Content;

use ILIAS\Container\InternalDomainService;
use ILIAS\Container\Content\ItemBlock\ItemBlockSequence;
use ILIAS\Repository\Clipboard\ClipboardManager;

/**
 * High level business logic class. Orchestrates item set,
 * view and block sequence generator.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemPresentationManager
{
    protected bool $include_empty_blocks;
    protected ModeManager $mode_manager;
    protected ?bool $can_order = null;
    protected ClipboardManager $repo_clipboard;
    protected ?bool $can_manage = null;
    protected ItemBlock\ItemBlockSequenceGenerator $sequence_generator;
    protected ?\ilContainerUserFilter $container_user_filter = null;
    protected ?array $type_grps = null;
    protected ?ItemSetManager $item_set = null;
    protected \ilContainer $container;
    protected ItemSessionRepository $item_repo;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        \ilContainer $container,
        ?\ilContainerUserFilter $container_user_filter,
        ClipboardManager $repo_clipboard,
        bool $include_empty_blocks = true
    ) {
        $this->container = $container;
        $this->domain = $domain;
        $this->container_user_filter = $container_user_filter;
        $this->repo_clipboard = $repo_clipboard;
        $this->mode_manager = $domain->content()->mode($container);
        $this->include_empty_blocks = $include_empty_blocks;

        // sequence from view manager
    }

    public function canManageItems(): bool
    {
        if (!is_null($this->can_manage)) {
            return $this->can_manage;
        }
        $user = $this->domain->user();
        $rbacsystem = $this->domain->rbac()->system();
        if ($user->getId() === ANONYMOUS_USER_ID || !is_object($this->container)) {
            return false;
        }

        if ($rbacsystem->checkAccess("write", $this->container->getRefId()) ||
            $this->container->getHiddenFilesFound() ||
            $this->repo_clipboard->hasEntries()) {
            $this->can_manage = true;
            return true;
        }
        $this->init();
        $this->can_manage = false;
        foreach ($this->item_set->getAllRefIds() as $ref_id) {
            if ($this->can_manage === true) {
                break;
            }
            if ($rbacsystem->checkAccess("delete", $ref_id)) {
                $this->can_manage = true;
            }
        }
        return $this->can_manage;
    }

    /**
     * Controls the ordering subtab
     */
    public function canOrderItems(): bool
    {
        $user = $this->domain->user();
        $rbacsystem = $this->domain->rbac()->system();

        if (is_null($this->can_order)) {
            $this->can_order = false;
            if ($user->getId() !== ANONYMOUS_USER_ID &&
                is_object($this->container) &&
                $rbacsystem->checkAccess("write", $this->container->getRefId())) {
                $this->can_order = true;
            }
        }
        return $this->can_order;
    }

    /**
     * Are we currently in ordering view and the items can be ordered?
     */
    public function isActiveItemOrdering(): bool
    {
        if ($this->mode_manager->isActiveItemOrdering()) {
            return true;
        }
        return false;
    }


    /**
     * @todo from ilContainer, should be removed there
     * @todo make proper service in classification component
     */
    protected function isClassificationFilterActive(): bool
    {
        $ref_id = $this->container->getRefId();
        // apply container classification filters
        $classification = $this->domain->classification($ref_id);
        foreach (\ilClassificationProvider::getValidProviders(
            $this->container->getRefId(),
            $this->container->getId(),
            $this->container->getType()
        ) as $class_provider) {
            $id = get_class($class_provider);
            $current = $classification->getSelectionOfProvider($id);
            if ($current) {
                return true;
            }
        }
        return false;
    }

    /**
     * @todo from ilContainer, should be removed there
     */
    public function filteredSubtree(): bool
    {
        return $this->isClassificationFilterActive() && in_array(
            $this->container->getType(),
            ["grp", "crs"]
        );
    }

    protected function init(): void
    {
        // already initialised?
        if (!is_null($this->item_set)) {
            return;
        }

        // get item set
        $ref_id = $this->container->getRefId();
        if ($this->filteredSubtree()) {
            $this->item_set = $this->domain->content()->itemSetTree($ref_id, $this->container_user_filter);
        } else {
            $this->item_set = $this->domain->content()->itemSetFlat($ref_id, $this->container_user_filter);
        }

        // get view
        $view = $this->domain->content()->view($this->container);
        // get item block sequence generator
        $this->sequence_generator = $this->domain->content()->itemBlockSequenceGenerator(
            $this->container,
            $view->getBlockSequence(),
            $this->item_set,
            $this->include_empty_blocks
        );
    }

    public function hasItems(): bool
    {
        $this->init();
        return $this->item_set->hasItems();
    }

    public function getItemBlockSequence(): ItemBlockSequence
    {
        $this->init();
        return $this->sequence_generator->getSequence();
    }

    public function getPageEmbeddedBlockIds(): array
    {
        $this->init();
        return $this->sequence_generator->getPageEmbeddedBlockIds();
    }

    public function getRawDataByRefId(int $ref_id): ?array
    {
        $this->init();
        return $this->item_set->getRawDataByRefId($ref_id);
    }

    public function getRefIdsOfType(string $type): array
    {
        $this->init();
        return $this->item_set->getRefIdsOfType($type);
    }
}
