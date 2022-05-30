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
 * Media pool explorer GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolExplorerGUI extends ilTreeExplorerGUI
{
    protected ilObjMediaPool $media_pool;
    protected \ILIAS\MediaPool\StandardGUIRequest $mep_request;
    protected ilObjUser $user;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjMediaPool $a_media_pool
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->media_pool = $a_media_pool;
        parent::__construct("mep_exp", $a_parent_obj, $a_parent_cmd, $a_media_pool->getTree());
        
        $this->setTypeWhiteList(array("dummy", "fold"));
        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setOrderField("title");

        $this->setNodeOpen($this->tree->readRootId());

        $this->mep_request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    /**
     * @param array $a_node
     */
    public function getNodeContent($a_node) : string
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->media_pool->getTitle();
        }
                
        return $a_node["title"];
    }

    /**
     * @param array $a_node
     */
    public function getNodeIcon($a_node) : string
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            $icon = ilUtil::getImagePath("icon_mep.svg");
        } else {
            $icon = ilUtil::getImagePath("icon_" . $a_node["type"] . ".svg");
        }
        
        return $icon;
    }

    /**
     * @param array $a_node
     */
    public function isNodeHighlighted($a_node) : bool
    {
        if ($a_node["child"] == $this->mep_request->getItemId() ||
            ($this->mep_request->getItemId() == 0 && $a_node["child"] == $this->getNodeId($this->getRootNode()))) {
            return true;
        }
        return false;
    }
    
    /**
     * @param array $a_node
     * @throws ilCtrlException
     */
    public function getNodeHref($a_node) : string
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass(
            "ilobjmediapoolgui",
            "ref_id",
            $this->mep_request->getRefId()
        );
        $ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", $a_node["child"]);
        $ret = $ilCtrl->getLinkTargetByClass("ilobjmediapoolgui", "listMedia", "", false, false);
        $ilCtrl->setParameterByClass(
            "ilobjmediapoolgui",
            "mepitem_id",
            $this->mep_request->getItemId()
        );
        return $ret;
    }

    /**
     * @param array $record
     */
    protected function getNodeStateToggleCmdClasses($record) : array
    {
        return [
            'ilRepositoryGUI',
            'ilObjMediaPoolGUI',
        ];
    }
}
