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

namespace ILIAS\Container\Content;

use ILIAS\Container\InternalRepoService;
use ILIAS\Container\InternalDataService;
use ILIAS\Container\InternalDomainService;
use ILIAS\Container\Content\Filter\FilterManager;
use ILIAS\Container\Content\ItemBlock\ItemBlockSequenceGenerator;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected \ILIAS\Repository\Clipboard\ClipboardManager $repo_clipboard;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    protected ItemSessionRepository $item_repo;
    protected ModeSessionRepository $mode_repo;
    /**
     * @var array<int, ModeManager>
     */
    protected static array $mode_managers = [];

    /**
     * @var array<int, ItemSetManager>
     */
    protected static array $flat_item_set_managers = [];

    /**
     * @var array<int, ItemSetManager>
     */
    protected static array $tree_item_set_managers = [];

    public function __construct(
        InternalRepoService $repo_service,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        global $DIC;

        $this->repo_clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard();

        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->item_repo = $this->repo_service->content()->item();
        $this->mode_repo = $this->repo_service->content()->mode();
    }

    /**
     * Controls item state (e.g. expanded)
     */
    public function items(\ilContainer $container): ItemManager
    {
        // @todo get rid of $DIC/request call here -> move to gui
        global $DIC;

        return new ItemManager(
            $container,
            $this->item_repo,
            $this->mode($container),
            $DIC->container()->internal()->gui()->standardRequest()
        );
    }

    /**
     * Manages item retrieval, filtering, grouping and sorting
     */
    public function itemPresentation(
        \ilContainer $container,
        ?\ilContainerUserFilter $container_user_filter,
        bool $include_empty_blocks = true
    ): ItemPresentationManager {
        return new ItemPresentationManager(
            $this->domain_service,
            $container,
            $container_user_filter,
            $this->repo_clipboard,
            $include_empty_blocks
        );
    }

    /**
     * Manages set of conatiner items (flat version)
     */
    public function itemSetFlat(
        int $ref_id,
        ?\ilContainerUserFilter $user_filter
    ): ItemSetManager {
        if (!isset(self::$flat_item_set_managers[$ref_id])) {
            self::$flat_item_set_managers[$ref_id] = new ItemSetManager(
                $this->domain_service,
                ItemSetManager::FLAT,
                $ref_id,
                $user_filter
            );
        }
        return self::$flat_item_set_managers[$ref_id];
    }

    /**
     * Manages set of conatiner items (flat version)
     */
    public function itemSetTree(
        int $ref_id,
        ?\ilContainerUserFilter $user_filter
    ): ItemSetManager {
        if (!isset(self::$tree_item_set_managers[$ref_id])) {
            self::$tree_item_set_managers[$ref_id] = new ItemSetManager(
                $this->domain_service,
                ItemSetManager::TREE,
                $ref_id,
                $user_filter
            );
        }
        return self::$tree_item_set_managers[$ref_id];
    }

    /**
     * Manages set of conatiner items (single item version)
     */
    public function itemSetSingle(int $ref_id, int $single_ref_id): ItemSetManager
    {
        return new ItemSetManager(
            $this->domain_service,
            ItemSetManager::SINGLE,
            $ref_id,
            null,
            $single_ref_id
        );
    }

    public function view(\ilContainer $container): ViewManager
    {
        $view_mode = $container->getViewMode();
        if ($container->filteredSubtree()) {
            $view_mode = \ilContainer::VIEW_SIMPLE;
        }
        switch ($view_mode) {
            case \ilContainer::VIEW_SIMPLE:
                $container_view = new SimpleViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;

            case \ilContainer::VIEW_OBJECTIVE:
                $container_view = new ObjectiveViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;

                // all items in one block
            case \ilContainer::VIEW_SESSIONS:
            case \ilCourseConstants::IL_CRS_VIEW_TIMING: // not nice this workaround
                $container_view = new SessionsViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;

                // all items in one block
            case \ilContainer::VIEW_BY_TYPE:
            default:
                $container_view = new ByTypeViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;
        }

        return $container_view;
    }

    /**
     * Controls admin/content view state
     * Note: The node manager currently holds "state". E.g. the ilContainerGUI
     * class sets e.g. the ordering mode early in the request.
     * Thus internal manager array is not only caching for performance but also
     * serves a singleton approach. This may be refactored in the future.
     */
    public function mode(\ilContainer $container): ModeManager
    {
        if (!isset(self::$mode_managers[$container->getId()])) {
            self::$mode_managers[$container->getId()] = new ModeManager(
                $container,
                $this->mode_repo,
                $this->repo_clipboard
            );
        }
        return self::$mode_managers[$container->getId()];
    }

    /**
     * @param array[] $objects each array must contain the keys "obj_id" and "type"
     */
    public function filter(
        array $objects,
        ?\ilContainerUserFilter $container_user_filter,
        bool $results_on_filter_only = false
    ): FilterManager {
        return new FilterManager(
            $this,
            $this->repo_service->content(),
            $objects,
            $container_user_filter,
            $results_on_filter_only
        );
    }

    public function itemBlockSequenceGenerator(
        \ilContainer $container,
        BlockSequence $block_sequence,
        ItemSetManager $item_set_manager,
        bool $include_empty_blocks = true
    ): ItemBlockSequenceGenerator {
        return new ItemBlockSequenceGenerator(
            $this->data_service->content(),
            $this->domain_service,
            $container,
            $block_sequence,
            $item_set_manager,
            $include_empty_blocks
        );
    }
}
