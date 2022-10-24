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
    public php4DOMElement $res_node;

    public function init(): void
    {
        $this->setType("repobj");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->res_node = $a_node->first_child();		// this is the Resources node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->res_node = $this->dom->create_element("Resources");
        $this->res_node = $this->node->append_child($this->res_node);
    }

    public function setResourceListType(string $a_type): void
    {
        if (!empty($a_type)) {
            $children = $this->res_node->child_nodes();
            for ($i = 0; $i < count($children); $i++) {
                $this->res_node->remove_child($children[$i]);
            }
            $list_node = $this->dom->create_element("ResourceList");
            $list_node = $this->res_node->append_child($list_node);
            $list_node->set_attribute("Type", $a_type);
        }
    }

    public function setItemGroupRefId(int $a_ref_id): void
    {
        if (!empty($a_ref_id)) {
            $children = $this->res_node->child_nodes();
            for ($i = 0; $i < count($children); $i++) {
                $this->res_node->remove_child($children[$i]);
            }
            $list_node = $this->dom->create_element("ItemGroup");
            $list_node = $this->res_node->append_child($list_node);
            $list_node->set_attribute("RefId", $a_ref_id);
        }
    }

    /**
     * Get Resource List Type.
     */
    public function getResourceListType(): ?string
    {
        if (is_object($this->res_node)) {
            $children = $this->res_node->child_nodes();
            if (is_object($children[0]) && $children[0]->node_name() == "ResourceList") {
                return $children[0]->get_attribute("Type");
            }
        }
        return null;
    }

    /**
     * Get item group ref id
     */
    public function getItemGroupRefId(): ?int
    {
        if (is_object($this->res_node)) {
            $children = $this->res_node->child_nodes();
            if (is_object($children[0]) && $children[0]->node_name() == "ItemGroup") {
                return (int) $children[0]->get_attribute("RefId");
            }
        }
        return null;
    }

    public function getMainType(): ?string
    {
        if (is_object($this->res_node)) {
            $children = $this->res_node->child_nodes();
            if (is_object($children[0])) {
                return (string) $children[0]->node_name();
            }
        }
        return null;
    }

    public static function modifyItemGroupRefIdsByMapping(
        ilPageObject $a_page,
        array $mappings
    ): void {
        $dom = $a_page->getDom();

        if ($dom instanceof php4DOMDocument) {
            $dom = $dom->myDOMDocument;
        }

        $xpath_temp = new DOMXPath($dom);
        $igs = $xpath_temp->query("//Resources/ItemGroup");

        foreach ($igs as $ig_node) {
            $ref_id = $ig_node->getAttribute("RefId");
            if ($mappings[$ref_id] > 0) {
                $ig_node->setAttribute("RefId", $mappings[$ref_id]);
            }
        }
    }

    public static function getLangVars(): array
    {
        return array("pc_res");
    }

    public static function resolveResources(
        ilPageObject $page,
        array $ref_mappings
    ): void {
        self::modifyItemGroupRefIdsByMapping($page, $ref_mappings);
    }
}
