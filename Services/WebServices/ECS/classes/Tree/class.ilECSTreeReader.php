<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Reads and store cms tree in database
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSTreeReader
{
    private $server_id;
    private $mid;

    /**
     * Constructor
     * @param <type> $server_id
     * @param <type> $mid
     */
    public function __construct($server_id, $mid)
    {
        $this->server_id = $server_id;
        $this->mid = $mid;
    }



    /**
     * Read trees from ecs
     *
     * @throws ilECSConnectorException
     */
    public function read()
    {
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Begin read');
        try {
            include_once './Services/WebServices/ECS/classes/Tree/class.ilECSDirectoryTreeConnector.php';
            $dir_reader = new ilECSDirectoryTreeConnector(
                ilECSSetting::getInstanceByServerId($this->server_id)
            );
            $trees = $dir_reader->getDirectoryTrees();
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ' ' . print_r($trees, true));
            if ($trees instanceof ilECSUriList) {
                foreach ((array) $trees->getLinkIds() as $tree_id) {
                    include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
                    include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';

                    if (!ilECSCmsData::treeExists($this->server_id, $this->mid, $tree_id)) {
                        $result = $dir_reader->getDirectoryTree($tree_id);
                        $this->storeTree($tree_id, $result->getResult());
                    }
                }
            }
        } catch (ilECSConnectorException $e) {
            throw $e;
        }
    }

    protected function storeTree($tree_id, $a_nodes)
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';

        $tree = new ilECSCmsTree($tree_id);
        
        
        $cms_tree = $a_nodes;

        $data = new ilECSCmsData();
        $data->setServerId($this->server_id);
        $data->setMid($this->mid);
        $data->setCmsId($cms_tree->rootID);
        $data->setTreeId($tree_id);
        $data->setTitle($node->directoryTitle);
        $data->setTerm($node->term);
        $data->save();

        $tree->insertRootNode($tree_id, $data->getObjId());
        $tree->setRootId($data->getObjId());
        
        
        foreach ((array) $cms_tree->nodes as $node) {
            // Add data entry
            $data = new ilECSCmsData();
            $data->setServerId($this->server_id);
            $data->setMid($this->mid);
            $data->setCmsId($node->id);
            $data->setTreeId($tree_id);
            $data->setTitle($node->title);
            $data->setTerm($node->term);
            $data->save();

            // add to tree
            if ($node->parent->id) {
                $parent_id = ilECSCmsData::lookupObjId(
                    $this->server_id,
                    $this->mid,
                    $tree_id,
                    (int) $node->parent->id
                );
                $tree->insertNode($data->getObjId(), $parent_id);
            }
        }
    }
}
