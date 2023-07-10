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

namespace ILIAS\COPage\PC\FileList;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FileListManager
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
    }

    public function getAllFileObjIds(\DOMDocument $dom): array
    {
        $file_obj_ids = [];
        $path = "//FileItem/Identifier";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $file_obj_ids[] = $node->getAttribute("Entry");
        }
        return $file_obj_ids;
    }

    public function addFileSizes(\DOMDocument $dom): void
    {
        $path = "//FileItem";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $cnode = $node;
            $size_node = $dom->createElement("Size");
            $size_node = $cnode->appendChild($size_node);

            $size = "";
            foreach ($cnode->childNodes as $child) {
                if ($child->nodeName == "Identifier") {
                    if ($child->hasAttribute("Entry")) {
                        $entry = $child->getAttribute("Entry");
                        $entry_arr = explode("_", $entry);
                        $id = $entry_arr[count($entry_arr) - 1];
                        $size = \ilObjFileAccess::_lookupFileSize((int) $id, false);
                    }
                }
            }
            $this->dom_util->setContent($size_node, (string) $size);
        }
    }

    /**
     * Resolve file items
     * (after import)
     */
    public function resolveFileItems(
        \DOMDocument $dom,
        array $a_mapping
    ): bool {
        $path = "//FileItem/Identifier";
        $changed = false;
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $old_id = $node->getAttribute("Entry");
            $old_id = explode("_", $old_id);
            $old_id = $old_id[count($old_id) - 1];
            if ($a_mapping[$old_id] > 0) {
                $node->setAttribute("Entry", "il__file_" . $a_mapping[$old_id]);
                $changed = true;
            }
        }

        return $changed;
    }
}
