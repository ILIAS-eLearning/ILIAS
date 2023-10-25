<?php

declare(strict_types=1);

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
 * Container page object
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerPage extends ilPageObject
{
    /**
     * Get parent type
     * @return string parent type
     */
    public function getParentType(): string
    {
        return "cont";
    }


    public function addMissingContainerBlocks(?\ILIAS\Container\Content\ItemPresentationManager $manager): void
    {
        if ($manager !== null) {
            foreach ($manager->getItemBlockSequence()->getBlocks() as $block) {
                if (!$block->getPageEmbedded()) {
                    $this->buildDom();
                    $this->addRepositoryBlockToPage($block->getId(), $block->getBlock());
                }
            }
            $this->removeUnsupportedBlockIds($manager);
        }
    }

    public function removeUnsupportedBlockIds(\ILIAS\Container\Content\ItemPresentationManager $manager): void
    {
        $ids = $manager->getPageEmbeddedBlockIds();
        $block_ids = [];
        foreach ($manager->getItemBlockSequence()->getBlocks() as $block) {
            $block_ids[] = $block->getId();
        }
        foreach ($ids as $id) {
            if (!in_array($id, $block_ids)) {
                $this->buildDom();
                $doc = $this->getDomDoc();
                $xpath_temp = new DOMXPath($doc);
                if (is_numeric($id)) {
                    $igs = $xpath_temp->query("//Resources/ItemGroup");
                    foreach ($igs as $ig_node) {
                        $ref_id = $ig_node->getAttribute("RefId");
                        if ((int) $ref_id === (int) $id) {
                            $pc_node = $ig_node->parentNode->parentNode;
                            $node = $pc_node->parentNode->removeChild($pc_node);
                        }
                    }
                } else {
                    $igs = $xpath_temp->query("//Resources/ResourceList");
                    foreach ($igs as $ig_node) {
                        $type = $ig_node->getAttribute("Type");
                        if ($type == $id) {
                            $pc_node = $ig_node->parentNode->parentNode;
                            $node = $pc_node->parentNode->removeChild($pc_node);
                        }
                    }
                }
            }
        }
        $this->update();
    }

    protected function addRepositoryBlockToPage(string $id, \ILIAS\Container\Content\Block $block): void
    {
        $pc_resources = new ilPCResources($this);
        $this->addHierIDs();
        $doc = $this->getDomDoc();
        $xpath = new DOMXPath($doc);
        $parent_hier_id = "pg";
        $parent_pc_id = "";
        $nodes = $xpath->query("//PageObject/PageContent[last()]");
        foreach ($nodes as $node) {
            $parent_hier_id = $node->getAttribute("HierId");
            $parent_pc_id = $node->getAttribute("PCID");
        }

        $pc_resources->create($this, $parent_hier_id, $parent_pc_id);

        if ($block instanceof \ILIAS\Container\Content\TypeBlock) {
            $pc_resources->setResourceListType($id);
        } elseif ($block instanceof \ILIAS\Container\Content\ItemGroupBlock) {
            $pc_resources->setItemGroupRefId((int) $id);
        } elseif ($block instanceof \ILIAS\Container\Content\OtherBlock) {
            $pc_resources->setResourceListType("_other");
        } elseif ($block instanceof \ILIAS\Container\Content\SessionBlock) {
            $pc_resources->setResourceListType("sess");
        } elseif ($block instanceof \ILIAS\Container\Content\ObjectivesBlock) {
            $pc_resources->setResourceListType("_lobj");
        } else {
            throw new ilException("unknown type " . get_class($block));
        }
        $this->update();
    }
}
