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

/**
 * Class ilPCBlog
 * Blog content object (see ILIAS DTD)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCBlog extends ilPageContent
{
    protected ilObjUser $user;

    public function init(): void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("blog");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "Blog");
    }

    /**
     * Set blog settings
     */
    public function setData(
        int $a_blog_id,
        array $a_posting_ids = null
    ): void {
        $ilUser = $this->user;

        $this->getChildNode()->setAttribute("Id", (string) $a_blog_id);
        $this->getChildNode()->setAttribute("User", (string) $ilUser->getId());

        // remove all children first
        $children = $this->getChildNode()->childNodes;
        if ($children) {
            foreach ($children as $child) {
                $this->getChildNode()->removeChild($child);
            }
        }

        if (count($a_posting_ids)) {
            foreach ($a_posting_ids as $posting_id) {
                $post_node = $this->dom_doc->createElement("BlogPosting");
                $post_node = $this->getChildNode()->appendChild($post_node);
                $post_node->setAttribute("Id", (string) $posting_id);
            }
        }
    }

    public function getBlogId(): int
    {
        if (is_object($this->getChildNode())) {
            return (int) $this->getChildNode()->getAttribute("Id");
        }
        return 0;
    }

    /**
     * Get blog postings
     */
    public function getPostings(): array
    {
        $res = array();
        if (is_object($this->getChildNode())) {
            $children = $this->getChildNode()->childNodes;
            if ($children) {
                foreach ($children as $child) {
                    $res[] = (int) $child->getAttribute("Id");
                }
            }
        }
        return $res;
    }
}
