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

namespace ILIAS\Container\Sorting\Settings;

use ILIAS\Container\InternalRepoService;
use ILIAS\Container\InternalDomainService;
use ILIAS\Container\InternalDataService;
use ilContainer;
use ilObject;

class Manager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    /**
     * Takes into account inherited settings.
     */
    public function getEffectiveSettingsForObject(int $obj_id): Settings
    {
        $direct_settings = $this->getSettingsForObject($obj_id);
        if ($direct_settings->getSortMode() !== ilContainer::SORT_INHERIT) {
            return $direct_settings;
        }

        $inherited_settings = $this->getInheritedSettings($obj_id);
        if (
            $inherited_settings === null ||
            $inherited_settings->getSortMode() === ilContainer::SORT_INHERIT
        ) {
            return $this->data->sorting()->settings(
                $obj_id,
                ilContainer::SORT_TITLE,
                $direct_settings->getSortDirection(),
                $direct_settings->getSortNewItemsPosition(),
                $direct_settings->getSortNewItemsOrder()
            );
        } else {
            return $this->data->sorting()->settings(
                $obj_id,
                $inherited_settings->getSortMode(),
                $direct_settings->getSortDirection(),
                $inherited_settings->getSortNewItemsPosition(),
                $inherited_settings->getSortNewItemsOrder()
            );
        }
    }

    /**
     * Returns null if there is nothing to inherit.
     */
    protected function getInheritedSettings(int $obj_id): ?Settings
    {
        $ref_ids = ilObject::_getAllReferences($obj_id);
        $ref_id = current($ref_ids);

        if ($cont_ref_id = $this->domain->repositoryTree()->checkForParentType($ref_id, 'grp', true)) {
            $parent_obj_id = ilObject::_lookupObjId($cont_ref_id);
            $parent_settings = $this->getSettingsForObject($parent_obj_id);

            if ($parent_settings->getSortMode() === ilContainer::SORT_INHERIT) {
                return $this->getInheritedSettings($parent_obj_id);
            }
            return $parent_settings;
        }
        if ($cont_ref_id = $this->domain->repositoryTree()->checkForParentType($ref_id, 'crs', true)) {
            $parent_obj_id = ilObject::_lookupObjId($cont_ref_id);
            return $this->getSettingsForObject($parent_obj_id);
        }
        return null;
    }

    public function lookupSortModeForObject(int $obj_id): int
    {
        $sort_mode = $this->repo->sorting()->settings()->getSortModeForObject($obj_id);
        if ($sort_mode !== ilContainer::SORT_INHERIT) {
            return $sort_mode;
        }
        return $this->getInheritedSettings($obj_id)?->getSortMode() ?? $sort_mode;
    }

    public function cloneSettings(
        int $old_id,
        int $new_id
    ): void {
        $old_settings = $this->repo->sorting()->settings()->getSettings($old_id);
        if ($old_settings !== null) {
            $this->repo->sorting()->settings()->save(
                $new_id,
                $old_settings->getSortMode(),
                $old_settings->getSortDirection(),
                $old_settings->getSortNewItemsPosition(),
                $old_settings->getSortNewItemsOrder()
            );
        }
    }

    public function saveSettingsForObject(
        int $obj_id,
        int $sort_mode,
        int $sort_direction,
        int $new_items_position,
        int $new_items_order
    ): void {
        $this->repo->sorting()->settings()->save(
            $obj_id,
            $sort_mode,
            $sort_direction,
            $new_items_position,
            $new_items_order
        );
    }

    public function deleteSettingsForObject(int $obj_id): void
    {
        $this->repo->sorting()->settings()->delete($obj_id);
    }

    public function getSettingsForObject(int $obj_id): Settings
    {
        return $this->repo->sorting()->settings()->getSettings($obj_id) ??
            $this->defaultSettings($obj_id);
    }

    protected function defaultSettings(int $obj_id): Settings
    {
        return $this->data->sorting()->settings(
            $obj_id,
            ilContainer::SORT_TITLE,
            ilContainer::SORT_DIRECTION_ASC,
            ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM,
            ilContainer::SORT_NEW_ITEMS_ORDER_TITLE
        );
    }
}
