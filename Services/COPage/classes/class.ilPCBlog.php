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
 * Class ilPCBlog
 * Blog content object (see ILIAS DTD)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCBlog extends ilPageContent
{
    protected php4DOMElement $blog_node;
    protected ilObjUser $user;

    public function init() : void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("blog");
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->blog_node = $a_node->first_child();		// this is the blog node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->blog_node = $this->dom->create_element("Blog");
        $this->blog_node = $this->node->append_child($this->blog_node);
    }

    /**
     * Set blog settings
     */
    public function setData(
        int $a_blog_id,
        array $a_posting_ids = null
    ) : void {
        $ilUser = $this->user;
        
        $this->blog_node->set_attribute("Id", $a_blog_id);
        $this->blog_node->set_attribute("User", $ilUser->getId());

        // remove all children first
        $children = $this->blog_node->child_nodes();
        if ($children) {
            foreach ($children as $child) {
                $this->blog_node->remove_child($child);
            }
        }

        if (count($a_posting_ids)) {
            foreach ($a_posting_ids as $posting_id) {
                $post_node = $this->dom->create_element("BlogPosting");
                $post_node = $this->blog_node->append_child($post_node);
                $post_node->set_attribute("Id", $posting_id);
            }
        }
    }

    public function getBlogId() : int
    {
        if (is_object($this->blog_node)) {
            return (int) $this->blog_node->get_attribute("Id");
        }
        return 0;
    }

    /**
     * Get blog postings
     */
    public function getPostings() : array
    {
        $res = array();
        if (is_object($this->blog_node)) {
            $children = $this->blog_node->child_nodes();
            if ($children) {
                foreach ($children as $child) {
                    $res[] = (int) $child->get_attribute("Id");
                }
            }
        }
        return $res;
    }
}
