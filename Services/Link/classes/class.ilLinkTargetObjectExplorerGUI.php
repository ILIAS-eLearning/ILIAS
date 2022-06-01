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
 * Internal Link: Repository Item Selector Explorer
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLinkTargetObjectExplorerGUI extends ilRepositorySelectorExplorerGUI
{
    protected string $clickable_type = "";
    protected string $link_type = "";

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_link_type
    ) {
        $this->link_type = $a_link_type;
        parent::__construct($a_parent_obj, $a_parent_cmd, null, "", "");
    }

    /**
     * Set clickable type
     * @param string $a_val clickable type
     */
    public function setClickableType(string $a_val) : void
    {
        $this->clickable_type = $a_val;
    }
    
    /**
     * Get clickable type
     *
     * @return string clickable type
     */
    public function getClickableType() : string
    {
        return $this->clickable_type;
    }

    /**
     * @param mixed $a_node node object/array
     */
    public function getNodeOnClick($a_node) : string
    {
        return "il.IntLink.selectLinkTargetObject('" . $a_node["type"] . "','" . $a_node["child"] . "','" . $this->link_type . "'); return(false);";
    }

    /**
     * @param mixed $a_node node object/array
     */
    public function getNodeHref($a_node) : string
    {
        return "#";
    }

    /**
     * @param mixed $a_node node object/array
     */
    public function isNodeClickable($a_node) : bool
    {
        if ($a_node["type"] === $this->getClickableType()) {
            return true;
        }
        return false;
    }
}
