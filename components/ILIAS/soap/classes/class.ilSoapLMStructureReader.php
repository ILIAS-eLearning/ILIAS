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
 * class for reading a learning module as structure object
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @version $Id: class.ilSoapStructureReader.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
 * @package ilias
 */
class ilSoapLMStructureReader extends ilSoapStructureReader
{
    public function _parseStructure(): void
    {
        /** @var ilObjLearningModule $obect */
        $obect = $this->object;

        $ctree = $obect->getLMTree();

        $nodes = $ctree->getSubTree($ctree->getNodeData($ctree->getRootId()));

        $currentParentStructureObject = $this->structureObject;
        $currentParent = 1;

        $parents = [];
        $parents[$currentParent] = $currentParentStructureObject;

        $lastStructureObject = null;
        $lastNode = null;
        foreach ($nodes as $node) {
            // only pages and chapters
            if ($node["type"] === "st" || $node["type"] === "pg") {
                // parent has changed, to build a tree
                if ((int) $currentParent !== (int) $node["parent"]) {
                    // did we passed this parent before?

                    if (array_key_exists($node["parent"], $parents)) {
                        $currentParentStructureObject = $parents[$node["parent"]];
                    } elseif ($lastNode["type"] !== "pg") {
                        // no, we did not, so use the last inserted structure as new parent
                        $parents[$lastNode["child"]] = $lastStructureObject;
                        $currentParentStructureObject = $lastStructureObject;
                    }
                    $currentParent = $lastNode["child"];
                }

                $lastNode = $node;

                $lastStructureObject = ilSoapStructureObjectFactory::getInstance(
                    $node["obj_id"],
                    $node["type"],
                    $node["title"],
                    $node["description"],
                    $this->getObject()->getRefId()
                );

                $currentParentStructureObject->addStructureObject($lastStructureObject);
            }
        }
    }
}
