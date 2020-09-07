<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

/**
* Unit tests for tree table
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @group needsInstalledILIAS
* @ingroup ServicesTree
*/
class ilTreeTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }
    
    public function testGetChild()
    {
        $tree = new ilTree(ROOT_FOLDER_ID);
        $childs = $tree->getChilds(14); // Chat settings (contains public chat)
        
        $this->assertEquals(count($childs), 1);
    }
    
    /**
     * get childs by type
     * @group IL_Init
     * @static
     */
    public function testGetChildsByType()
    {
        $tree = new ilTree(ROOT_FOLDER_ID);
        $childs = $tree->getChildsByType(9, 'cals'); // only calendar settings
        
        $this->assertEquals(count($childs), 1);
    }
    
    /**
     * get childs by type filter
     * @group IL_Init
     * @static
     */
    public function testGetChildsByTypeFilter()
    {
        $tree = new ilTree(ROOT_FOLDER_ID);
        $childs = $tree->getChildsByTypeFilter(9, array('cals','rolf')); // only calendar settings and role folder
        
        $this->assertEquals(count($childs), 2);
    }

    /**
     * get childs by type filter
     * @group IL_Init
     * @static
     */
    public function testGetSubTree()
    {
        $tree = new ilTree(ROOT_FOLDER_ID);
        
        $root = $tree->getNodeData(1);
        $childs = $tree->getSubTree($root, false, 'cals'); // only calendar settings
        
        $this->assertEquals(count($childs), 1);
    }
     
    /**
    * get path ids using nested set
    * @group IL_Init
    * @param
    * @return
    */
    public function testGetPathIdsUsingNestedSet()
    {
        // This test leads to a fatal error, as getPathIdsUsingNestedSets is
        // not defined on ilTree.
        $this->assertTrue(false, "Testcase leads to fatal error.");
        
        $tree = new ilTree(ROOT_FOLDER_ID);
        $ids = $tree->getPathIdsUsingNestedSets(24, 9); // Administration -> Public Chat => should return 9,14,24 (chat server settings)
        
        $this->assertEquals($ids, array(9,14,24));

        $tree = new ilTree(ROOT_FOLDER_ID);
        $ids = $tree->getPathIdsUsingNestedSets(24); // Administration -> Public Chat => should return 9,14,24 (chat server settings)
        
        $this->assertEquals($ids, array(1,9,14,24));
    }
    
    /**
     * get path ids (adjacenca and nested set)
     * @group IL_Init
     * @param
     * @return
     */
    public function testGetPathIds()
    {
        // This test leads to a fatal error, as getPathIdsUsingNestedSets and
        // getPathIdsUsingAdjacencyMap are not defined on ilTree.
        $this->assertTrue(false, "Testcase leads to fatal error.");
        
        $tree = new ilTree(ROOT_FOLDER_ID);
        $ids_ns = $tree->getPathIdsUsingNestedSets(24);
        $ids_al = $tree->getPathIdsUsingAdjacencyMap(24);

        $this->assertEquals($ids_ns, $ids_al);
    }
    
    /**
     * @group IL_Init
     * @param
     * @return
     */
    public function testGetNodePath()
    {
        $tree = new ilTree(ROOT_FOLDER_ID);
        $res = $tree->getNodePath(1, 29);
    }
            
    /**
     * get path ids (adjacenca and nested set)
     * @group IL_Init
     * @param
     * @return
     */
    public function testMaxDepth()
    {
        $tree = new ilTree(ROOT_FOLDER_ID);
        $tree->getMaximumDepth();
    }
     
    /**
     * get path ids (adjacenca and nested set)
     * @group IL_Init
     * @param
     * @return
     */
    public function testAllOthers()
    {
        $tree = new ilTree(ROOT_FOLDER_ID);
        $d = $tree->getDepth(24);
        
        $this->assertEquals($d, 4);
        
        $node = $tree->getNodeData(24);
        $this->assertEquals($node['title'], 'Public chat');
        
        $bool = $tree->isInTree(24);
        $this->assertEquals($bool, true);
        
        $bool = $tree->isInTree(24242424);
        $this->assertEquals($bool, false);
        
        /* ref_id 14 => obj_id 98 does not exist
        $node = $tree->getParentNodeData(24);
        $this->assertEquals($node['title'],'Chat-Server');
        */
        
        $bool = $tree->isGrandChild(9, 24);
        $this->assertEquals($bool, 1);
        
        /* see above
        $node = $tree->getNodeDataByType('chac');
        $this->assertEquals($node[0]['title'],'Chat-Server');
        */
        
        $bool = $tree->isDeleted(24);
        $this->assertEquals($bool, false);
        
        $id = $tree->getParentId(24);
        $this->assertEquals($id, 14);
        
        $lft = $tree->getLeftValue(24);
        $this->assertEquals($lft, 14);
        
        $seq = $tree->getChildSequenceNumber($tree->getNodeData(24));
        $this->assertEquals($seq, 1);
        
        $tree->getNodePath(9, 1);
        
        $max_depth = $tree->getMaximumDepth();
        
        // Round trip
        $tree = new ilTree(ROOT_FOLDER_ID);
        $suc = $tree->fetchSuccessorNode(16); // cals
        $cals = $tree->fetchPredecessorNode($suc['child']);
        $this->assertEquals($cals['child'], 16);
    }
}
