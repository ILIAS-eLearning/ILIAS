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
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilIntLinkRepItemExplorerGUI extends ilRepositorySelectorExplorerGUI
{
    protected string $link_target_script;

    /**
     * @param object|array $a_parent_obj parent gui class or class array
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, null, "", "");
        
        // #14587 - ilRepositorySelectorExplorerGUI::__construct() does NOT include side blocks!
        $list = $this->getTypeWhiteList();
        $list[] = "poll";
        $this->setTypeWhiteList($list);
    }

    /**
     * Set "set link target" script
     */
    public function setSetLinkTargetScript(string $a_script) : void
    {
        $this->link_target_script = $a_script;
    }

    /**
     * Get "set link target" script
     */
    public function getSetLinkTargetScript() : string
    {
        return $this->link_target_script;
    }

    /**
     * @param array|object $a_node
     */
    public function getNodeHref($a_node) : string
    {
        if ($this->getSetLinkTargetScript() === "") {
            return "#";
        }

        $link = ilUtil::appendUrlParameterString(
            $this->getSetLinkTargetScript(),
            "linktype=RepositoryItem&linktarget=il__" . $a_node["type"] . "_" . $a_node["child"]
        );

        return $link;
    }

    /**
     * get onclick event handling
     * @param array|object $a_node
     */
    public function getNodeOnClick($a_node) : string
    {
        if ($this->getSetLinkTargetScript() === "") {
            return "return il.IntLink.addInternalLink('[iln " . $a_node['type'] . "=&quot;" . $a_node['child'] . "&quot;]','[/iln]', event);";
        }

        return "";
    }
}
