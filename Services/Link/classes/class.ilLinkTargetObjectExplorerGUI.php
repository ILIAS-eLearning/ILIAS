<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
 * Internal Link: Repository Item Selector Explorer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesIntLink
 */
class ilLinkTargetObjectExplorerGUI extends ilRepositorySelectorExplorerGUI
{
    /**
     * @var string
     */
    protected $link_type;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_link_type)
    {
        $this->link_type = $a_link_type;
        parent::__construct($a_parent_obj, $a_parent_cmd, null, "", "");
    }

    /**
     * Set clickable type
     *
     * @param string $a_val clickable type
     */
    public function setClickableType($a_val)
    {
        $this->clickable_type = $a_val;
    }
    
    /**
     * Get clickable type
     *
     * @return string clickable type
     */
    public function getClickableType()
    {
        return $this->clickable_type;
    }

    /**
     * Get onclick attribute
     */
    public function getNodeOnClick($a_node)
    {
        return "il.IntLink.selectLinkTargetObject('" . $a_node["type"] . "','" . $a_node["child"] . "','" . $this->link_type . "'); return(false);";
    }

    /**
     * Get href for node
     *
     * @param mixed $a_node node object/array
     * @return string href attribute
     */
    public function getNodeHref($a_node)
    {
        return "#";
    }

    /**
     * Is node clickable?
     *
     * @param array $a_node node data
     * @return boolean node clickable true/false
     */
    public function isNodeClickable($a_node)
    {
        if ($a_node["type"] == $this->getClickableType()) {
            return true;
        }
        return false;
    }
}
