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

namespace ILIAS\COPage\Link;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LinkManager
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;

        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
    }

    protected function getDefaultMediaResolver(): \Closure
    {
        return fn (int $id) => \ilMediaItem::_getMapAreasIntLinks($id);
    }

    public function getInternalLinks(
        \DOMDocument $dom,
        ?\Closure $media_resolver = null
    ): array {
        if (is_null($media_resolver)) {
            $media_resolver = $this->getDefaultMediaResolver();
        }
        // get all internal links of the page
        $path = "//IntLink";
        $links = array();
        $cnt_multiple = 1;
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $target = $node->getAttribute("Target");
            $type = $node->getAttribute("Type");
            $targetframe = $node->getAttribute("TargetFrame");
            $anchor = $node->getAttribute("Anchor");
            $links[$target . ":" . $type . ":" . $targetframe . ":" . $anchor] =
                array("Target" => $target,
                      "Type" => $type,
                      "TargetFrame" => $targetframe,
                      "Anchor" => $anchor
                );

            // get links (image map areas) for inline media objects
            if ($type == "MediaObject" && $targetframe == "") {
                if (substr($target, 0, 4) == "il__") {
                    $id_arr = explode("_", $target);
                    $id = $id_arr[count($id_arr) - 1];

                    $med_links = $media_resolver((int) $id);
                    //    \ilMediaItem::_getMapAreasIntLinks($id);
                    foreach ($med_links as $key => $med_link) {
                        $links[$key] = $med_link;
                    }
                }
            }
        }

        // get all media aliases
        $path = "//MediaAlias";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $oid = $node->getAttribute("OriginId");
            if (substr($oid, 0, 4) == "il__") {
                $id_arr = explode("_", $oid);
                $id = $id_arr[count($id_arr) - 1];

                $med_links = $media_resolver($id);
                foreach ($med_links as $key => $med_link) {
                    $links[$key] = $med_link;
                }
            }
        }

        return $links;
    }


    public function containsFileLinkId(\DOMDocument $dom, string $file_link_id): bool
    {
        $int_links = $this->getInternalLinks(
            $dom,
            function (int $id): array {
                return [];
            }
        );
        foreach ($int_links as $il) {
            if ($il["Target"] == str_replace("_file_", "_dfile_", $file_link_id)) {
                return true;
            }
        }
        return false;
    }

    public function extractFileFromLinkId(string $file_link_id): int
    {
        $file = explode("_", $file_link_id);
        return (int) $file[count($file) - 1];
    }

    /**
     * Resolves all internal link targets of the page, if targets are available
     * (after import)
     */
    public function resolveIntLinks(
        \DOMDocument $dom,
        array $a_link_map = null
    ): bool {
        $changed = false;

        // resolve normal internal links
        $path = "//IntLink";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $target = $node->getAttribute("Target");
            $type = $node->getAttribute("Type");

            if ($a_link_map == null) {
                $new_target = \ilInternalLink::_getIdForImportId($type, $target);
            //$this->log->debug("no map, type: " . $type . ", target: " . $target . ", new target: " . $new_target);
            } else {
                $nt = explode("_", $a_link_map[$target]);
                $new_target = false;
                if ($nt[1] == IL_INST_ID) {
                    $new_target = "il__" . $nt[2] . "_" . $nt[3];
                }
                //$this->log->debug("map, type: " . $type . ", target: " . $target . ", new target: " . $new_target);
            }
            if ($new_target !== false) {
                $node->setAttribute("Target", $new_target);
                $changed = true;
            } else {        // check wether link target is same installation
                if (\ilInternalLink::_extractInstOfTarget($target) == IL_INST_ID &&
                    IL_INST_ID > 0 && $type != "RepositoryItem") {
                    $new_target = \ilInternalLink::_removeInstFromTarget($target);
                    if (\ilInternalLink::_exists($type, $new_target)) {
                        $node->setAttribute("Target", $new_target);
                        $changed = true;
                    }
                }
            }
        }

        // resolve internal links in map areas
        $path = "//MediaAlias";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $orig_id = $node->getAttribute("OriginId");
            $id_arr = explode("_", $orig_id);
            $mob_id = $id_arr[count($id_arr) - 1];
            \ilMediaItem::_resolveMapAreaLinks($mob_id);
        }
        return $changed;
    }
}
