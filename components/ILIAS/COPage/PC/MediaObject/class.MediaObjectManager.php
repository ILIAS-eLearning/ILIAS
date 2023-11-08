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
class MediaObjectManager
{
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
    }

    /**
     * get all media objects, that are referenced and used within
     * the page
     */
    public function collectMediaObjects(
        \DOMDocument $dom,
        bool $a_inline_only = true
    ): array {
        // determine all media aliases of the page
        $path = "//MediaObject/MediaAlias";
        $mob_ids = array();
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $id_arr = explode("_", $node->getAttribute("OriginId"));
            $mob_id = $id_arr[count($id_arr) - 1];
            $mob_ids[$mob_id] = $mob_id;
        }

        // determine all media aliases of interactive images
        $path = "//InteractiveImage/MediaAlias";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $id_arr = explode("_", $node->getAttribute("OriginId"));
            $mob_id = $id_arr[count($id_arr) - 1];
            $mob_ids[$mob_id] = $mob_id;
        }

        // determine all inline internal media links
        $path = "//IntLink[@Type = 'MediaObject']";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            if (($node->getAttribute("TargetFrame") == "") ||
                (!$a_inline_only)) {
                $target = $node->getAttribute("Target");
                $id_arr = explode("_", $target);
                if (($id_arr[1] == IL_INST_ID) ||
                    (substr($target, 0, 4) == "il__")) {
                    $mob_id = $id_arr[count($id_arr) - 1];
                    if (\ilObject::_exists($mob_id)) {
                        $mob_ids[$mob_id] = $mob_id;
                    }
                }
            }
        }
        return $mob_ids;
    }

    /**
     * get a xml string that contains all media object elements, that
     * are referenced by any media alias in the page
     */
    public function getMultimediaXML(\DOMDocument $dom): string
    {
        $mob_ids = $this->collectMediaObjects($dom);

        // get xml of corresponding media objects
        $mobs_xml = "";
        foreach ($mob_ids as $mob_id => $dummy) {
            if (\ilObject::_lookupType($mob_id) == "mob") {
                $mob_obj = new \ilObjMediaObject($mob_id);
                $mobs_xml .= $mob_obj->getXML(IL_MODE_OUTPUT, $a_inst = 0, true);
            }
        }
        return $mobs_xml;
    }

    /**
     * get complete media object (alias) element
     */
    public function getMediaAliasElement(
        \DOMDocument $dom,
        int $a_mob_id,
        int $a_nr = 1
    ): string {
        $path = "//MediaObject/MediaAlias[@OriginId='il__mob_$a_mob_id']";
        $nodes = $this->dom_util->path($dom, $path);
        $mal_node = $nodes->item($a_nr - 1);
        $mob_node = $mal_node->parentNode;
        return $this->dom_util->dump($mob_node);
    }

    public function resolveMediaAliases(
        \ilPageObject $page,
        array $a_mapping,
        bool $a_reuse_existing_by_import = false
    ): bool {
        // resolve normal internal links
        $dom = $page->getDomDoc();
        $path = "//MediaAlias";
        $changed = false;
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            // get the ID of the import file from the xml
            $old_id = $node->getAttribute("OriginId");
            $old_id = explode("_", $old_id);
            $old_id = $old_id[count($old_id) - 1];
            $new_id = "";
            $import_id = "";
            // get the new id from the current mapping
            if (($a_mapping[$old_id] ?? 0) > 0) {
                $new_id = $a_mapping[$old_id];
                if ($a_reuse_existing_by_import) {
                    // this should work, if the lm has been imported in a translation installation and re-exported
                    $import_id = \ilObject::_lookupImportId($new_id);
                    $imp = explode("_", $import_id);
                    if ($imp[1] == IL_INST_ID && $imp[2] == "mob" && \ilObject::_lookupType((int) ($imp[3] ?? 0)) == "mob") {
                        $new_id = $imp[3];
                    }
                }
            }
            // now check, if the translation has been done just by changing text in the exported
            // translation file
            if ($import_id == "" && $a_reuse_existing_by_import) {
                // if the old_id is also referred by the page content of the default language
                // we assume that this media object is unchanged
                $med_of_def_lang = \ilObjMediaObject::_getMobsOfObject(
                    $page->getParentType() . ":pg",
                    $page->getId(),
                    0,
                    "-"
                );
                if (in_array($old_id, $med_of_def_lang)) {
                    $new_id = $old_id;
                }
            }
            if ($new_id != "") {
                $node->setAttribute("OriginId", "il__mob_" . $new_id);
                $changed = true;
            }
        }
        return $changed;
    }
}
