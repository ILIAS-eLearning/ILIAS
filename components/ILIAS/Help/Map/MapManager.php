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

namespace ILIAS\Help\Map;

use ILIAS\Help\InternalRepoService;
use ILIAS\Help\InternalDomainService;

class MapManager
{
    protected \ilObjUser $user;
    protected \ilRbacReview $rbacreview;
    protected \ilSetting $settings;
    protected \ilAccessHandler $access;
    protected InternalDomainService $domain;
    protected MapDBRepository $repo;

    public function __construct(
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->repo = $repo->map();
        $this->domain = $domain;

        $this->access = $domain->access();
        $this->settings = $domain->settings();
        $this->rbacreview = $domain->rbac()->review();
        $this->user = $domain->user();
    }

    public function saveScreenIdsForChapter(
        int $chap,
        array $ids
    ): void {
        $this->repo->saveScreenIdsForChapter($chap, $ids);
    }

    public function saveMappingEntry(
        int $chap,
        string $comp,
        string $screen_id,
        string $screen_sub_id,
        string $perm,
        int $module_id = 0
    ): void {
        $this->repo->saveMappingEntry(
            $chap,
            $comp,
            $screen_id,
            $screen_sub_id,
            $perm,
            $module_id
        );
    }

    public function removeScreenIdsOfChapter(
        int $chap,
        int $module_id = 0
    ): void {
        $this->repo->removeScreenIdsOfChapter(
            $chap,
            $module_id
        );
    }

    public function getScreenIdsOfChapter(
        int $chap,
        int $module_id = 0
    ): array {
        return $this->repo->getScreenIdsOfChapter(
            $chap,
            $module_id
        );
    }


    public function getHelpSectionsForId(
        string $a_screen_id,
        int $a_ref_id
    ): array {
        if ($this->domain->module()->isAuthoringMode()) {
            $module_ids = [0];
        } else {
            $module_ids = $this->domain->module()->getActiveModules();
        }
        $chaps = [];
        foreach ($this->repo->getChaptersForScreenId($a_screen_id, $module_ids) as $rec) {
            if ($rec["perm"] != "" && $rec["perm"] != "-") {
                // check special "create*" permission
                if ($rec["perm"] === "create*") {
                    $has_create_perm = false;

                    // check owner
                    if ($this->user->getId() === \ilObject::_lookupOwner(\ilObject::_lookupObjId($a_ref_id))) {
                        $has_create_perm = true;
                    } elseif ($this->rbacreview->isAssigned($this->user->getId(), SYSTEM_ROLE_ID)) { // check admin
                        $has_create_perm = true;
                    } elseif ($this->access->checkAccess("read", "", $a_ref_id)) {
                        $perm = $this->rbacreview->getUserPermissionsOnObject($this->user->getId(), $a_ref_id);
                        foreach ($perm as $p) {
                            if (strpos($p, "create_") === 0) {
                                $has_create_perm = true;
                            }
                        }
                    }
                    if ($has_create_perm) {
                        $chaps[] = $rec["chap"];
                    }
                } elseif ($this->access->checkAccess($rec["perm"], "", $a_ref_id)) {
                    $chaps[] = $rec["chap"];
                }
            } else {
                $chaps[] = $rec["chap"];
            }
        }
        return $chaps;
    }

    /**
     * Has given screen Id any sections?
     * Note: We removed the "ref_id" parameter here, since this method
     * should be fast. It is used to decide whether the help button should
     * appear or not. We assume that there is at least one section for
     * users with the "read" permission.
     */
    public function hasScreenIdSections(
        string $a_screen_id
    ): bool {

        if ($this->user->getLanguage() !== "de") {
            return false;
        }

        if ($this->settings->get("help_mode") === "2") {
            return false;
        }

        if ($this->domain->module()->isAuthoringMode()) {
            $module_ids = [0];
        } else {
            $module_ids = $this->domain->module()->getActiveModules();
        }

        foreach ($this->repo->getChaptersForScreenId($a_screen_id, $module_ids) as $rec) {
            return true;
        }
        return false;
    }

    public function deleteEntriesOfModule(
        int $id
    ): void {
        $this->repo->deleteEntriesOfModule($id);
    }
}
