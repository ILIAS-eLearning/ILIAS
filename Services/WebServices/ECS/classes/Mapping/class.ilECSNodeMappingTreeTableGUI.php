<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Table GUI for ecs trees
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSNodeMappingTreeTableGUI extends ilTable2GUI
{
    private $server_id = 0;
    private $mid = 0;

    /**
     * Table gui constructor
     * @global <type> $lng
     * @global <type> $ilCtrl
     * @param <type> $a_parent_obj
     * @param <type> $a_parent_cmd
     */
    public function __construct($a_server_id, $a_mid, $a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->server_id = $a_server_id;
        $this->mid = $a_mid;

        // TODO: set id
        $this->setId('ecs_node_mapping_table');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt('ecs_cms_directory_trees_tbl'));
        $this->addColumn($this->lng->txt('title'), '', "80%");
        $this->addColumn($this->lng->txt('actions'), '', "20%");
        $this->setRowTemplate("tpl.ecs_node_mapping_tree_table_row.html", "Services/WebServices/ECS");

        $this->setEnableHeader(true);
    }

    /**
     * Get setting
     * @return ilECSSetting
     */
    public function getServer()
    {
        return ilECSSetting::getInstanceByServerId($this->server_id);
    }

    /**
     * Get mid
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Fill row
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];


        // show title if available
        if ($a_set['term']) {
            $this->tpl->setVariable('VAL_TITLE', $a_set['term']);
        } else {
            $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        }
        $this->tpl->setVariable('TXT_STATUS', $this->lng->txt('status'));
        $this->tpl->setVariable('VAL_STATUS', ilECSMappingUtils::mappingStatusToString($a_set['status']));

        // Actions
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('actl_' . $a_set['id']);
        $list->setListTitle($this->lng->txt('actions'));
        
        $ilCtrl->setParameter($this->getParentObject(), 'tid', $a_set['id']);
        $this->tpl->setVariable('EDIT_TITLE', $this->ctrl->getLinkTarget($this->getParentObject(), 'dInitEditTree'));
        
        $list->addItem($this->lng->txt('edit'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'dInitEditTree'));
        
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
        if ($a_set['status'] != ilECSMappingUtils::MAPPED_UNMAPPED &&
                ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isDirectoryMappingEnabled()) {
            $list->addItem(
                $this->lng->txt('ecs_cms_tree_synchronize'),
                '',
                $ilCtrl->getLinkTarget($this->getParentObject(), 'dSynchronizeTree')
            );
        }
        
        $list->addItem($this->lng->txt('delete'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'dConfirmDeleteTree'));
        $this->tpl->setVariable('ACTIONS', $list->getHTML());

        $ilCtrl->clearParameters($this->getParentObject());
    }

    /**
     * Parse campusconnect
     */
    public function parse()
    {
        include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';

        $data = array();
        $counter = 0;
        foreach (ilECSCmsData::lookupTreeIds($this->getServer()->getServerId(), $this->getMid()) as $tree_id) {
            $root = new ilECSCmsTree($tree_id);
            $node = new ilECSCmsData($root->getRootId());

            $data[$counter]['id'] = $tree_id;
            $data[$counter]['status'] = ilECSMappingUtils::lookupMappingStatus(
                $this->getServer()->getServerId(),
                $this->getMid(),
                $tree_id
            );
            $data[$counter]['title'] = $node->getTitle();
            $data[$counter]['term'] = ilECSCmsData::lookupTopTerm(
                $this->getServer()->getServerId(),
                $this->getMid(),
                $tree_id
            );
            $counter++;
        }
        $this->setData($data);
    }
}
