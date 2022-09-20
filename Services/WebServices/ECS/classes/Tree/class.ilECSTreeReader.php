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
 * Reads and store cms tree in database
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSTreeReader
{
    private ilLogger $logger;

    private int $server_id;
    private int $mid;

    /**
     * Constructor
     */
    public function __construct(int $server_id, int $mid)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();

        $this->server_id = $server_id;
        $this->mid = $mid;
    }



    /**
     * Read trees from ecs
     *
     * @throws ilECSConnectorException
     */
    public function read(): void
    {
        $this->logger->debug('Begin read');
        $dir_reader = new ilECSDirectoryTreeConnector(
            ilECSSetting::getInstanceByServerId($this->server_id)
        );
        $trees = $dir_reader->getDirectoryTrees();
        $this->logger->debug(print_r($trees, true));
        if ($trees instanceof ilECSUriList) {
            foreach ($trees->getLinkIds() as $tree_id) {
                if (!ilECSCmsData::treeExists($this->server_id, $this->mid, $tree_id)) {
                    $result = $dir_reader->getDirectoryTree($tree_id);
                    $this->storeTree($tree_id, $result->getResult());
                }
            }
        }
    }

    protected function storeTree($tree_id, $a_nodes): void
    {
        $tree = new ilECSCmsTree($tree_id);


        $cms_tree = $a_nodes;

        $data = new ilECSCmsData();
        $data->setServerId($this->server_id);
        $data->setMid($this->mid);
        $data->setCmsId($cms_tree->rootID);
        $data->setTreeId($tree_id);
        $data->setTitle($cms_tree->directoryTitle);
        $data->setTerm($cms_tree->term);
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
                    $node->parent->id
                );
                $tree->insertNode($data->getObjId(), $parent_id);
            }
        }
    }
}
