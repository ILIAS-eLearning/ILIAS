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

namespace ILIAS\COPage\PC\MediaObject;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InteractiveImageManager
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
    }

    /**
     * Resolve iim media aliases
     * (in ilContObjParse)
     */
    public function resolveIIMMediaAliases(
        \DOMDocument $dom,
        array $a_mapping
    ): bool {
        // resolve normal internal links
        $path = "//InteractiveImage/MediaAlias";
        $changed = false;
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $old_id = $node->getAttribute("OriginId");
            if ($a_mapping[$old_id] > 0) {
                $node->setAttribute("OriginId", "il__mob_" . $a_mapping[$old_id]);
                $changed = true;
            }
        }
        return $changed;
    }
}
