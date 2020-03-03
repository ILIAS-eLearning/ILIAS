<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCProfile
*
* Personal data content object (see ILIAS DTD)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPCListItem.php 22210 2009-10-26 09:46:06Z akill $
*
* @ingroup ServicesCOPage
*/
class ilPCProfile extends ilPageContent
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
        $this->setType("prof");
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->prof_node = $a_node->first_child();		// this is the profile node
    }

    /**
    * Create profile node in xml.
    *
    * @param	object	$a_pg_obj		Page Object
    * @param	string	$a_hier_id		Hierarchical ID
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->prof_node = $this->dom->create_element("Profile");
        $this->prof_node = $this->node->append_child($this->prof_node);
    }

    /**
     * Set profile settings
     *
     * @param string $a_mode
     * @param array $a_fields
     */
    public function setFields($a_mode, array $a_fields = null)
    {
        $ilUser = $this->user;
        
        $this->prof_node->set_attribute("Mode", $a_mode);
        $this->prof_node->set_attribute("User", $ilUser->getId());

        // remove all children first
        $children = $this->prof_node->child_nodes();
        if ($children) {
            foreach ($children as $child) {
                $this->prof_node->remove_child($child);
            }
        }

        if ($a_mode == "manual") {
            foreach ($a_fields as $field) {
                $field_node = $this->dom->create_element("ProfileField");
                $field_node = $this->prof_node->append_child($field_node);
                $field_node->set_attribute("Name", $field);
            }
        }
    }

    /**
     * Get profile mode
     *
     * @return string
     */
    public function getMode()
    {
        if (is_object($this->prof_node)) {
            return $this->prof_node->get_attribute("Mode");
        }
    }

    /**
    * Get profile settings
    *
    * @return array
    */
    public function getFields()
    {
        $res = array();
        if (is_object($this->prof_node)) {
            $children = $this->prof_node->child_nodes();
            if ($children) {
                foreach ($children as $child) {
                    $res[] = $child->get_attribute("Name");
                }
            }
        }
        return $res;
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("pc_prof", "ed_insert_profile");
    }
}
