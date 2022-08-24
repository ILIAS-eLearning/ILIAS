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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsTreeCommandQueueHandler implements ilECSCommandQueueHandler
{
    private ilLogger $log;

    private ?\ilECSSetting $server = null;
    private int $mid = 0;

    public function __construct(ilECSSetting $server)
    {
        global $DIC;
        $this->log = $DIC->logger()->wsrv();

        $this->server = $server;
        $this->init();
    }

    /**
     * Get server
     */
    public function getServer(): ilECSSetting
    {
        return $this->server;
    }


    /**
     * @inheritdoc
     */
    public function handleCreate(ilECSSetting $server, $a_content_id): bool
    {
        $this->log->debug('ECS cms tree create');


        try {
            $dir_reader = new ilECSDirectoryTreeConnector($this->getServer());
            $res = $dir_reader->getDirectoryTree($a_content_id);
            $nodes = $res->getResult();

            if ($this->log->isHandling(ilLogLevel::DEBUG)) {
                $this->log->dump($nodes, ilLogLevel::DEBUG);
            }
        } catch (ilECSConnectorException $e) {
            $this->log->error('Tree creation failed  with mesage ' . $e->getMessage());
            return false;
        }

        $cms_tree = $nodes;
        $data = new ilECSCmsData();
        $data->setServerId($server->getServerId());
        $data->setMid($this->mid);
        $data->setCmsId($cms_tree->rootID);
        $data->setTreeId($a_content_id);
        $data->setTitle($cms_tree->directoryTreeTitle);
        $data->setTerm($cms_tree->term);
        $data->save();

        $tree = new ilECSCmsTree($a_content_id);
        $tree->insertRootNode($a_content_id, $data->getObjId());
        $tree->setRootId($data->getObjId());


        foreach ((array) $cms_tree->nodes as $node) {
            // Add data entry
            $data = new ilECSCmsData();
            $data->setServerId($this->getServer()->getServerId());
            $data->setMid($this->mid);
            $data->setCmsId($node->id);
            $data->setTreeId($a_content_id);
            $data->setTitle($node->title);
            $data->setTerm($node->term);
            $data->save();

            // add to tree
            if ($node->parent->id) {
                $parent_id = ilECSCmsData::lookupObjId(
                    $this->getServer()->getServerId(),
                    $this->mid,
                    $a_content_id,
                    $node->parent->id
                );
                $tree->insertNode($data->getObjId(), $parent_id);
            }
        }
        return true;
    }

    /**
     * Handle delete
     */
    public function handleDelete(ilECSSetting $server, int $a_content_id): bool
    {
        $this->log->debug('ECS cms tree delete');

        $data = new ilECSCmsData();
        $data->setServerId($this->getServer()->getServerId());
        $data->setMid($this->mid);
        $data->setTreeId($a_content_id);
        $data->deleteTree();

        $tree = new ilECSCmsTree($a_content_id);
        $tree->deleteTree($tree->getNodeData(ilECSCmsTree::lookupRootId($a_content_id)));

        ilECSNodeMappingAssignments::deleteMappings(
            $this->getServer()->getServerId(),
            $this->mid,
            $a_content_id
        );
        return true;
    }

    /**
     * Handle update
     */
    public function handleUpdate(ilECSSetting $server, $a_content_id): bool
    {
        $this->log->debug('ECS cms tree update');


        // 1)  Mark all nodes as deleted
        // 2a) Delete cms tree
        // 2)  Add cms tree table entries
        // 2)  Replace the cms data table entries
        // 3)  Insert deleted tree nodes in tree
        // 4)  Sync tree

        try {
            $dir_reader = new ilECSDirectoryTreeConnector($this->getServer());
            $res = $dir_reader->getDirectoryTree($a_content_id);
            $nodes = $res->getResult();
            if ($this->log->isHandling(ilLogLevel::DEBUG)) {
                $this->log->dump($nodes, ilLogLevel::DEBUG);
            }
        } catch (ilECSConnectorException $e) {
            $this->log->error('Tree creation failed  with message ' . $e->getMessage());
            return false;
        }

        // read old tree structure
        $tree = new ilECSCmsTree($a_content_id);

        $root_node = $tree->getNodeData(ilECSCmsTree::lookupRootId($a_content_id));

        $old_nodes = array();
        if ($root_node['child']) {
            $old_nodes = $tree->getSubTree($root_node, true);
        }

        if ($this->log->isHandling(ilLogLevel::DEBUG)) {
            $this->log->debug('Old tree data... ');
            $this->log->dump($old_nodes, ilLogLevel::DEBUG);
        }

        // Delete old cms tree
        ilECSCmsTree::deleteByTreeId($a_content_id);

        // Mark all nodes in cms data as deleted
        ilECSCmsData::writeAllDeleted(
            $this->getServer()->getServerId(),
            $this->mid,
            $a_content_id,
            true
        );

        // Check for update or new entry
        $cms_tree = $nodes;

        $data_obj_id = ilECSCmsData::lookupObjId(
            $this->getServer()->getServerId(),
            $this->mid,
            $a_content_id,
            $cms_tree->rootID
        );

        $data = new ilECSCmsData($data_obj_id);
        $data->setServerId($server->getServerId());
        $data->setMid($this->mid);
        $data->setCmsId($cms_tree->rootID);
        $data->setTreeId($a_content_id);
        $data->setTitle($cms_tree->directoryTreeTitle);
        $data->setTerm($cms_tree->term);

        if ($data_obj_id) {
            $data->setDeleted(false);
            $data->update();
        } else {
            $data->save();
        }

        $tree->insertRootNode($a_content_id, $data->getObjId());
        $tree->setRootId($data->getObjId());


        foreach ((array) $cms_tree->nodes as $node) {
            $data_obj_id = ilECSCmsData::lookupObjId(
                $this->getServer()->getServerId(),
                $this->mid,
                $a_content_id,
                $node->id
            );

            // update data entry
            $data = new ilECSCmsData($data_obj_id);
            $data->setTitle($node->title);
            $data->setTerm($node->term);
            $data->setDeleted(false);

            if ($data_obj_id) {
                $data->update();
            } else {
                $data->setCmsId($node->id);
                $data->setMid($this->mid);
                $data->setServerId($this->getServer()->getServerId());
                $data->setTreeId($a_content_id);
                $data->setDeleted(false);
                $data->save();

                $data_obj_id = $data->getObjId();
            }

            // add to tree
            $parent_id = ilECSCmsData::lookupObjId(
                $this->getServer()->getServerId(),
                $this->mid,
                $a_content_id,
                $node->parent->id
            );
            $tree->insertNode($data->getObjId(), $parent_id);
        }

        // Insert deleted nodes in tree
        $deleted = ilECSCmsData::findDeletedNodes(
            $this->getServer()->getServerId(),
            $this->mid,
            $a_content_id
        );

        foreach ($deleted as $obj_id) {
            $parent = 0;
            foreach ($old_nodes as $node) {
                if ($node['child'] === $obj_id) {
                    $parent = $node['parent'];
                    break;
                }
            }

            if ($parent && $tree->isInTree($parent)) {
                $tree->insertNode($obj_id, $parent);
            }
        }

        // Sync tree
        $sync = new ilECSCmsTreeSynchronizer(
            $this->getServer(),
            $this->mid,
            $a_content_id
        );
        $sync->sync();

        return true;
    }

    /**
     * init handler
     */
    private function init(): void
    {
        $this->mid = ilECSParticipantSettings::getInstanceByServerId($this->getServer()->getServerId())->lookupCmsMid();
    }
}
