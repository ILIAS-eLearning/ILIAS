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

namespace ILIAS\COPage\Page;

use ILIAS\Repository\Profile\ProfileAdapter;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageManager implements PageManagerInterface
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;
    protected PageDBRepository $repo;
    protected ProfileAdapter $profile;

    public function __construct(
        PageDBRepository $repo,
        \ILIAS\COPage\Dom\DomUtil $dom_util,
        ProfileAdapter $profile
    ) {
        $this->repo = $repo;
        $this->dom_util = $dom_util;
        $this->profile = $profile;
    }

    public function get(
        string $parent_type,
        int $id = 0,
        int $old_nr = 0,
        string $lang = "-"
    ): \ilPageObject {
        return \ilPageObjectFactory::getInstance(
            $parent_type,
            $id,
            $old_nr,
            $lang
        );
    }

    public function lookupParentId(int $a_id, string $a_type): int
    {
        return $this->repo->lookupParentId($a_id, $a_type);
    }

    public function writeParentId(string $a_parent_type, int $a_pg_id, int $a_par_id): void
    {
        $this->repo->writeParentId($a_parent_type, $a_pg_id, $a_par_id);
    }

    public function writeActive(int $page_id, string $parent_type, bool $active): void
    {
        $this->repo->writeActive($page_id, $parent_type, $active);
    }

    public function getParentObjectContributors(
        string $parent_type,
        int $parent_id,
        string $lang = "-"
    ): array {
        $contributors = [];
        $contributor_data = $this->repo->getParentObjectContributorData(
            $parent_type,
            $parent_id,
            $lang
        );

        foreach ($contributor_data["pages"] as $page) {
            if ($lang === "") {
                $contributors[$page["last_change_user"]][$page["page_id"]][$page["lang"]] = 1;
            } else {
                $contributors[$page["last_change_user"]][$page["page_id"]] = 1;
            }
        }

        foreach ($contributor_data["history"] as $page) {
            if ($lang === "") {
                $contributors[$page["user_id"]][$page["page_id"]][$page["lang"]] =
                    ($contributors[$page["user_id"]][$page["page_id"]][$page["lang"]] ?? 0) + $page["cnt"];
            } else {
                $contributors[$page["user_id"]][$page["page_id"]] =
                    ($contributors[$page["user_id"]][$page["page_id"]] ?? 0) + $page["cnt"];
            }
        }

        $result = [];
        foreach ($contributors as $user_id => $pages) {
            if (!$this->profile->exists((int) $user_id)) {
                continue;
            }
            $name = \ilObjUser::_lookupName((int) $user_id);
            $result[] = [
                "user_id" => $user_id,
                "pages" => $pages,
                "lastname" => $name["lastname"],
                "firstname" => $name["firstname"]
            ];
        }

        return $result;
    }

    public function getPageContributors(
        string $parent_type,
        int $page_id,
        string $lang = "-"
    ): array {
        $contributors = [];
        $contributor_data = $this->repo->getPageContributorData(
            $parent_type,
            $page_id,
            $lang
        );

        foreach ($contributor_data["pages"] as $page) {
            if ($lang === "") {
                $contributors[$page["last_change_user"]][$page["lang"]] = 1;
            } else {
                $contributors[$page["last_change_user"]] = 1;
            }
        }

        foreach ($contributor_data["history"] as $page) {
            if ($lang === "") {
                $contributors[$page["user_id"]][$page["lang"]] =
                    ($contributors[$page["user_id"]][$page["lang"]] ?? 0) + $page["cnt"];
            } else {
                $contributors[$page["user_id"]] =
                    ($contributors[$page["user_id"]] ?? 0) + $page["cnt"];
            }
        }

        $result = [];
        foreach ($contributors as $user_id => $pages) {
            $name = \ilObjUser::_lookupName((int) $user_id);
            $result[] = [
                "user_id" => $user_id,
                "pages" => $pages,
                "lastname" => $name["lastname"],
                "firstname" => $name["firstname"]
            ];
        }

        return $result;
    }

    public function content(\DOMDocument $dom): PageContentManager
    {
        return new PageContentManager($dom);
    }

    public function contentFromXml($xml): PageContentManager
    {
        $error = "";
        $dom = $this->dom_util->docFromString($xml, $error);
        return new PageContentManager($dom);
    }
}
