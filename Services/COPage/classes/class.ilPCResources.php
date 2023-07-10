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

/**
 * Resources content object (see ILIAS DTD). Inserts Repository Resources
 * of a Container Object,
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCResources extends ilPageContent
{
    public function init(): void
    {
        $this->setType("repobj");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "Resources");
    }

    public function setResourceListType(string $a_type): void
    {
        if (!empty($a_type)) {
            $this->dom_util->deleteAllChilds($this->getChildNode());
            $list_node = $this->dom_doc->createElement("ResourceList");
            $list_node = $this->getChildNode()->appendChild($list_node);
            $list_node->setAttribute("Type", $a_type);
        }
    }

    public function setItemGroupRefId(int $a_ref_id): void
    {
        if (!empty($a_ref_id)) {
            $this->dom_util->deleteAllChilds($this->getChildNode());
            $list_node = $this->dom_doc->createElement("ItemGroup");
            $list_node = $this->getChildNode()->appendChild($list_node);
            $list_node->setAttribute("RefId", $a_ref_id);
        }
    }

    /**
     * Get Resource List Type.
     */
    public function getResourceListType(): ?string
    {
        if (is_object($this->getChildNode())) {
            $c = $this->getChildNode()->childNodes->item(0);
            if (is_object($c) && $c->nodeName == "ResourceList") {
                return $c->getAttribute("Type");
            }
        }
        return null;
    }

    /**
     * Get item group ref id
     */
    public function getItemGroupRefId(): ?int
    {
        if (is_object($this->getChildNode())) {
            $c = $this->getChildNode()->childNodes->item(0);
            if (is_object($c) && $c->nodeName == "ItemGroup") {
                return (int) $c->getAttribute("RefId");
            }
        }
        return null;
    }

    public function getMainType(): ?string
    {
        if (is_object($this->getChildNode())) {
            $c = $this->getChildNode()->childNodes->item(0);
            if (is_object($c)) {
                return (string) $c->nodeName;
            }
        }
        return null;
    }

    public static function modifyItemGroupRefIdsByMapping(
        ilPageObject $a_page,
        array $mappings
    ): bool {
        $dom = $a_page->getDomDoc();
        $log = ilLoggerFactory::getLogger('copg');

        $changed = false;
        $xpath_temp = new DOMXPath($dom);
        $igs = $xpath_temp->query("//Resources/ItemGroup");

        foreach ($igs as $ig_node) {
            $ref_id = $ig_node->getAttribute("RefId");
            $log->debug(">>> Fix Item Group with import Ref Id:" . $ref_id);
            $log->debug("Ref Id Mapping:" . print_r($mappings, true));
            if (($mappings[$ref_id] ?? 0) > 0) {
                $ig_node->setAttribute("RefId", $mappings[$ref_id]);
                $changed = true;
            }
        }
        return $changed;
    }

    public static function getLangVars(): array
    {
        return array("pc_res");
    }

    public static function resolveResources(
        ilPageObject $page,
        array $ref_mappings
    ): bool {
        return self::modifyItemGroupRefIdsByMapping($page, $ref_mappings);
    }
}
