<?php
/*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */

include_once "./webservice/soap/classes/class.ilSoapStructureReader.php";
include_once "./webservice/soap/classes/class.ilSoapStructureObjectFactory.php";

/**
 * class for reading a learning module as structure object
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @version $Id: class.ilSoapStructureReader.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
 * @package ilias
 */
class ilSoapLMStructureReader extends ilSoapStructureReader
{
    public function _parseStructure() : void
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
