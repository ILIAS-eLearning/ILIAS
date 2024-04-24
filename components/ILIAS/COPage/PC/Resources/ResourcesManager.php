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

namespace ILIAS\COPage\PC\Resources;

class ResourcesManager
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
    }

    public function getResourceIds(\DOMDocument $dom): array
    {
        $r_ids = [];
        $path = "//Resources";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $id = "";
            $c = $node->childNodes->item(0);
            if (is_object($c) && $c->nodeName === "ResourceList") {
                $id = $c->getAttribute("Type");
            }
            if (is_object($c) && $c->nodeName === "ItemGroup") {
                $id = $c->getAttribute("RefId");
            }
            if ($id !== "" && !in_array($id, $r_ids)) {
                $r_ids[] = $id;
            }
        }
        return $r_ids;
    }

}
