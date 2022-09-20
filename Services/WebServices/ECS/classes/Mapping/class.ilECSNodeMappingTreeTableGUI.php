<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Table GUI for ecs trees
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSNodeMappingTreeTableGUI extends ilTable2GUI
{
    private int $server_id;
    private int $mid;

    /**
     * Table gui constructor
     */
    public function __construct(int $a_server_id, int $a_mid, ?object $a_parent_obj, string $a_parent_cmd)
    {
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
     */
    public function getServer(): \ilECSSetting
    {
        return ilECSSetting::getInstanceByServerId($this->server_id);
    }

    /**
     * Get mid
     */
    public function getMid(): int
    {
        return $this->mid;
    }

    /**
     * Fill row
     * @param array $a_set
     */
    protected function fillRow(array $a_set): void
    {
        // show title if available
        if ($a_set['term']) {
            $this->tpl->setVariable('VAL_TITLE', $a_set['term']);
        } else {
            $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        }
        $this->tpl->setVariable('TXT_STATUS', $this->lng->txt('status'));
        $this->tpl->setVariable('VAL_STATUS', ilECSMappingUtils::mappingStatusToString($a_set['status']));

        // Actions
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('actl_' . $a_set['id']);
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->getParentObject(), 'tid', $a_set['id']);
        $this->tpl->setVariable('EDIT_TITLE', $this->ctrl->getLinkTarget($this->getParentObject(), 'dInitEditTree'));

        $list->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'dInitEditTree'));

        if ($a_set['status'] !== ilECSMappingUtils::MAPPED_UNMAPPED &&
                ilECSNodeMappingSettings::getInstanceByServerMid($this->getServer()->getServerId(), $this->getMid())->isDirectoryMappingEnabled()) {
            $list->addItem(
                $this->lng->txt('ecs_cms_tree_synchronize'),
                '',
                $this->ctrl->getLinkTarget($this->getParentObject(), 'dSynchronizeTree')
            );
        }

        $list->addItem($this->lng->txt('delete'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'dConfirmDeleteTree'));
        $this->tpl->setVariable('ACTIONS', $list->getHTML());

        $this->ctrl->clearParameters($this->getParentObject());
    }

    /**
     * Parse campusconnect
     */
    public function parse(): void
    {
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
