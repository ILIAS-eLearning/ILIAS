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
    protected \ilObjectDefinition $obj_definition;

    public function __construct()
    {
        global $DIC;

        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $this->obj_definition = $DIC["objDefinition"];
    }

    protected function getDefaultMediaCollector(): \Closure
    {
        return fn(int $id) => \ilMediaItem::_getMapAreasIntLinks($id);
    }

    public function getInternalLinks(
        \DOMDocument $dom,
        ?\Closure $media_collector = null
    ): array {
        if (is_null($media_collector)) {
            $media_collector = $this->getDefaultMediaCollector();
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

                    $med_links = $media_collector((int) $id);
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

                $med_links = $media_collector((int) $id);
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

            if ($a_link_map === null) {
                $new_target = \ilInternalLink::_getIdForImportId($type, $target);
                //$this->log->debug("no map, type: " . $type . ", target: " . $target . ", new target: " . $new_target);
            } else {
                $nt = explode("_", $a_link_map[$target] ?? "");
                $new_target = false;
                if (($nt[1] ?? "") == IL_INST_ID) {
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

    protected function getDefaultLMTypeLookuper(): \Closure
    {
        return fn(int $id) => \ilLMObject::_lookupType($id);
    }

    /**
     * Move internal links from one destination to another. This is used
     * for pages and structure links. Just use IDs in "from" and "to".
     */
    public function moveIntLinks(
        \DOMDocument $dom,
        array $a_from_to,
        ?\Closure $lm_type_lookup = null
    ): bool {
        if (is_null($lm_type_lookup)) {
            $lm_type_lookup = $this->getDefaultLMTypeLookuper();
        }

        $changed = false;

        // resolve normal internal links
        $path = "//IntLink";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $target = $node->getAttribute("Target");
            $type = $node->getAttribute("Type");
            $obj_id = \ilInternalLink::_extractObjIdOfTarget($target);
            if (($a_from_to[$obj_id] ?? 0) > 0 && is_int(strpos($target, "__"))) {
                if ($type == "PageObject" && $lm_type_lookup($a_from_to[$obj_id]) == "pg") {
                    $node->setAttribute("Target", "il__pg_" . $a_from_to[$obj_id]);
                    $changed = true;
                }
                if ($type == "StructureObject" && $lm_type_lookup($a_from_to[$obj_id]) == "st") {
                    $node->setAttribute("Target", "il__st_" . $a_from_to[$obj_id]);
                    $changed = true;
                }
                if ($type == "PortfolioPage") {
                    $node->setAttribute("Target", "il__ppage_" . $a_from_to[$obj_id]);
                    $changed = true;
                }
                if ($type == "WikiPage") {
                    $node->setAttribute("Target", "il__wpage_" . $a_from_to[$obj_id]);
                    $changed = true;
                }
            }
        }

        // map areas
        $path = "//MediaAlias";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $media_object_node = $node->parentNode;
            $page_content_node = $media_object_node->parentNode;
            $c_hier_id = $page_content_node->getAttribute("HierId");

            // first check, wheter we got instance map areas -> take these
            $std_alias_item = new \ilMediaAliasItem(
                $dom,
                $c_hier_id,
                "Standard"
            );
            $areas = $std_alias_item->getMapAreas();
            $correction_needed = false;
            if (count($areas) > 0) {
                // check if correction needed
                foreach ($areas as $area) {
                    if ($area["Type"] == "PageObject" ||
                        $area["Type"] == "StructureObject") {
                        $t = $area["Target"];
                        $tid = \ilInternalLink::_extractObjIdOfTarget($t);
                        if ($a_from_to[$tid] > 0) {
                            $correction_needed = true;
                        }
                    }
                }
            } else {
                $areas = array();

                // get object map areas and check whether at least one must
                // be corrected
                $oid = $node->getAttribute("OriginId");
                if (substr($oid, 0, 4) == "il__") {
                    $id_arr = explode("_", $oid);
                    $id = $id_arr[count($id_arr) - 1];

                    $mob = new \ilObjMediaObject($id);
                    $med_item = $mob->getMediaItem("Standard");
                    $med_areas = $med_item->getMapAreas();

                    foreach ($med_areas as $area) {
                        $link_type = ($area->getLinkType() == "int")
                            ? "IntLink"
                            : "ExtLink";

                        $areas[] = array(
                            "Nr" => $area->getNr(),
                            "Shape" => $area->getShape(),
                            "Coords" => $area->getCoords(),
                            "Link" => array(
                                "LinkType" => $link_type,
                                "Href" => $area->getHref(),
                                "Title" => $area->getTitle(),
                                "Target" => $area->getTarget(),
                                "Type" => $area->getType(),
                                "TargetFrame" => $area->getTargetFrame()
                            )
                        );

                        if ($area->getType() == "PageObject" ||
                            $area->getType() == "StructureObject") {
                            $t = $area->getTarget();
                            $tid = \ilInternalLink::_extractObjIdOfTarget($t);
                            if ($a_from_to[$tid] > 0) {
                                $correction_needed = true;
                            }
                            //var_dump($a_from_to);
                        }
                    }
                }
            }

            // correct map area links
            if ($correction_needed) {
                $changed = true;
                $std_alias_item->deleteAllMapAreas();
                foreach ($areas as $area) {
                    if ($area["Link"]["LinkType"] == "IntLink") {
                        $target = $area["Link"]["Target"];
                        $type = $area["Link"]["Type"];
                        $obj_id = \ilInternalLink::_extractObjIdOfTarget($target);
                        if ($a_from_to[$obj_id] > 0) {
                            if ($type == "PageObject" && $lm_type_lookup($a_from_to[$obj_id]) == "pg") {
                                $area["Link"]["Target"] = "il__pg_" . $a_from_to[$obj_id];
                            }
                            if ($type == "StructureObject" && $lm_type_lookup($a_from_to[$obj_id]) == "st") {
                                $area["Link"]["Target"] = "il__st_" . $a_from_to[$obj_id];
                            }
                        }
                    }

                    $std_alias_item->addMapArea(
                        $area["Shape"],
                        $area["Coords"],
                        $area["Link"]["Title"],
                        array("Type" => $area["Link"]["Type"],
                              "TargetFrame" => $area["Link"]["TargetFrame"],
                              "Target" => $area["Link"]["Target"],
                              "Href" => $area["Link"]["Href"],
                              "LinkType" => $area["Link"]["LinkType"],
                        )
                    );
                }
            }
        }
        return $changed;
    }

    /**
     * Handle repository links on copy process
     */
    public function handleRepositoryLinksOnCopy(
        \DOMDocument $dom,
        array $a_mapping,
        int $a_source_ref_id,
        \ilTree $tree
    ): void {
        $type = "";
        $objDefinition = $this->obj_definition;

        // resolve normal internal links
        $path = "//IntLink";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $target = $node->getAttribute("Target");
            $type = $node->getAttribute("Type");
            //$this->log->debug("Target: " . $target);
            $t = explode("_", $target);
            if ($type == "RepositoryItem" && ((int) $t[1] == 0 || (int) $t[1] == IL_INST_ID)) {
                if (isset($a_mapping[$t[3]])) {
                    // we have a mapping -> replace the ID
                    //$this->log->debug("... replace " . $t[3] . " with " . $a_mapping[$t[3]] . ".");
                    $node->setAttribute(
                        "Target",
                        "il__obj_" . $a_mapping[$t[3]]
                    );
                } elseif ($tree->isGrandChild($a_source_ref_id, $t[3])) {
                    // we have no mapping, but the linked object is child of the original node -> remove link
                    //$this->log->debug("... remove links.");
                    if ($node->parentNode->nodeName === "MapArea") {    // simply remove map areas
                        $parent = $node->parentNode;
                        $parent->parentNode->removeChild($parent);
                    } else {    // replace link by content of the link for other internal links
                        $source_node = $node;
                        $new_node = $source_node->cloneNode(true);
                        //$new_node->parentNode->removeChild($new_node);
                        $childs = $new_node->child_nodes();
                        foreach ($new_node->childNodes as $child) {
                            //$this->log->debug("... move node $j " . $child->node_name() . " before " . $source_node->node_name());
                            $source_node->parentNode->insertBefore($child, $source_node);
                        }
                        $source_node->parentNode->removeChild($source_node);
                    }
                }
            }
        }

        // resolve normal external links
        $ilias_url = parse_url(ILIAS_HTTP_PATH);
        $path = "//ExtLink";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $href = $node->getAttribute("Href");
            //$this->log->debug("Href: " . $href);

            $url = parse_url($href);

            // only handle links on same host
            //$this->log->debug("Host: " . ($url["host"] ?? ""));
            if (($url["host"] ?? "") !== "" && $url["host"] !== $ilias_url["host"]) {
                continue;
            }

            // get parameters
            $par = [];
            if (substr($href, strlen($href) - 5) === ".html") {
                $parts = explode(
                    "_",
                    basename(
                        substr($url["path"], 0, strlen($url["path"]) - 5)
                    )
                );
                if (array_shift($parts) !== "goto") {
                    continue;
                }
                $par["client_id"] = array_shift($parts);
                $par["target"] = implode("_", $parts);
            } else {
                foreach (explode("&", ($url["query"] ?? "")) as $p) {
                    $p = explode("=", $p);
                    if (isset($p[0]) && isset($p[1])) {
                        $par[$p[0]] = $p[1];
                    }
                }
            }

            $target_client_id = $par["client_id"] ?? "";
            if ($target_client_id != "" && $target_client_id != CLIENT_ID) {
                continue;
            }

            // get ref id
            $ref_id = 0;
            if (is_int(strpos($href, "ilias.php"))) {
                $ref_id = (int) ($par["ref_id"] ?? 0);
            } elseif (isset($par["target"]) && $par["target"] !== "") {
                $t = explode("_", $par["target"]);
                if ($objDefinition->isRBACObject($t[0] ?? "")) {
                    $ref_id = (int) ($t[1] ?? 0);
                    $type = $t[0] ?? "";
                }
            }
            if ($ref_id > 0) {
                if (isset($a_mapping[$ref_id])) {
                    $new_ref_id = $a_mapping[$ref_id];
                    // we have a mapping -> replace the ID
                    if (is_int(strpos($href, "ilias.php"))) {
                        $new_href = str_replace("ref_id=" . ($par["ref_id"] ?? ""), "ref_id=" . $new_ref_id, $href);
                    } else {
                        $nt = str_replace($type . "_" . $ref_id, $type . "_" . $new_ref_id, $par["target"]);
                        $new_href = str_replace($par["target"], $nt, $href);
                    }
                    if ($new_href != "") {
                        //$this->log->debug("... ext link replace " . $href . " with " . $new_href . ".");
                        $node->set_attribute("Href", $new_href);
                    }
                } elseif ($tree->isGrandChild($a_source_ref_id, $ref_id)) {
                    // we have no mapping, but the linked object is child of the original node -> remove link
                    //$this->log->debug("... remove ext links.");
                    if ($node->parentNode->nodeName == "MapArea") {    // simply remove map areas
                        $parent = $node->parentNode;
                        $parent->parentNode->removeChild($parent);
                    } else {    // replace link by content of the link for other internal links
                        $source_node = $node;
                        $new_node = $source_node->cloneNode(true);
                        //$new_node->unlink_node($new_node);
                        foreach ($new_node->childNodes as $child) {
                            //$this->log->debug("... move node $j " . $childs[$j]->node_name() . " before " . $source_node->node_name());
                            $source_node->parentNode->insertBefore($child, $source_node);
                        }
                        $source_node->parentNode->removeChild($source_node);
                    }
                }
            }
        }
    }

    /**
     * Delete internal links
     */
    public function deleteInternalLinks(
        \ilPageObject $page
    ): void {
        \ilInternalLink::_deleteAllLinksOfSource(
            $page->getParentType() . ":pg",
            $page->getId(),
            $page->getLanguage()
        );
    }


    /**
     * save internal links of page
     */
    public function saveInternalLinks(
        \ilPageObject $page,
        \DOMDocument $a_domdoc,
    ): void {
        $this->deleteInternalLinks(
            $page
        );
        $t_type = "";
        // query IntLink elements
        $xpath = new \DOMXPath($a_domdoc);
        $nodes = $xpath->query('//IntLink');
        foreach ($nodes as $node) {
            $link_type = $node->getAttribute("Type");

            switch ($link_type) {
                case "StructureObject":
                    $t_type = "st";
                    break;

                case "PageObject":
                    $t_type = "pg";
                    break;

                case "GlossaryItem":
                    $t_type = "git";
                    break;

                case "MediaObject":
                    $t_type = "mob";
                    break;

                case "RepositoryItem":
                    $t_type = "obj";
                    break;

                case "File":
                    $t_type = "file";
                    break;

                case "WikiPage":
                    $t_type = "wpage";
                    break;

                case "PortfolioPage":
                    $t_type = "ppage";
                    break;

                case "User":
                    $t_type = "user";
                    break;
            }

            $target = $node->getAttribute("Target");
            $target_arr = explode("_", $target);
            $t_id = (int) $target_arr[count($target_arr) - 1];

            // link to other internal object
            if (is_int(strpos($target, "__"))) {
                $t_inst = 0;
            } else {    // link to unresolved object in other installation
                $t_inst = (int) ($target_arr[1] ?? 0);
            }

            if ($t_id > 0) {
                \ilInternalLink::_saveLink(
                    $page->getParentType() . ":pg",
                    $page->getId(),
                    $t_type,
                    $t_id,
                    $t_inst,
                    $page->getLanguage()
                );
            }
        }
    }
}
