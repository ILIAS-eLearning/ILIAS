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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageManager implements PageManagerInterface
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;
    protected PageDBRepository $repo;

    public function __construct(PageDBRepository $repo, \ILIAS\COPage\Dom\DomUtil $dom_util)
    {
        $this->repo = $repo;
        $this->dom_util = $dom_util;
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
