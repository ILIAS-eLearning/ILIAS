<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPCMyCourses
 *
 * My courses content object (see ILIAS DTD)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCMyCourses extends ilPageContent
{
    /**
     * @var ilObjUser
     */
    protected $user;

    public $dom;

    /**
    * Init page content component.
    */
    public function init()
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->setType("mcrs");
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("ed_insert_my_courses", "pc_mcrs");
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->mcrs_node = &$a_node->first_child();		// this is the courses node
    }

    /**
    * Create courses node in xml.
    *
    * @param	object	$a_pg_obj		Page Object
    * @param	string	$a_hier_id		Hierarchical ID
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->mcrs_node = $this->dom->create_element("MyCourses");
        $this->mcrs_node = $this->node->append_child($this->mcrs_node);
    }

    /**
     * Set courses settings
     */
    public function setData($a_sort)
    {
        $ilUser = $this->user;
        
        $this->mcrs_node->set_attribute("User", $ilUser->getId());
        $this->mcrs_node->set_attribute("Sort", $a_sort);
        
        /* remove all children first
        $children = $this->cach_node->child_nodes();
        if($children)
        {
            foreach($children as $child)
            {
                $this->cach_node->remove_child($child);
            }
        }
        */
    }
    
    public function getSorting()
    {
        if (is_object($this->mcrs_node)) {
            return $this->mcrs_node->get_attribute("Sort");
        }
    }
}
