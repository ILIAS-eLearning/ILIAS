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
*
* @ingroup ServicesTree
*/
class ilRBACTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	 * RBAC FA tests 
	 * @param
	 * @return
	 */
	public function testRbacFA()
	{
		global $rbacreview,$rbacadmin;
		
		// Non empty
		$non_empty = $rbacreview->filterEmptyRoleFolders(array(8));
		$this->assertEquals($non_empty,array(8));

		// Empty
		$empty = $rbacreview->filterEmptyRoleFolders(array(1));
		$this->assertEquals($empty,array());
		
		// Protected
		$rbacadmin->setProtected(1,4,'y');
		$prot = $rbacreview->isProtected(8,4);
		$this->assertEquals($prot,true);
		$rbacadmin->setProtected(1,4,'n');
		$prot = $rbacreview->isProtected(8,4);
		$this->assertEquals($prot,false);
		
		$rbacreview->getRoleListByObject(8);
		$rbacreview->getAssignableRoles();
		
		// Child roles
		$child1 = $rbacreview->getAssignableRolesInSubtree(8);
		$child2 = $rbacreview->getAssignableChildRoles(8);
		$this->assertEquals($child1,$child2);
		
		$ass = $rbacreview->isAssignable(4,8);
		$this->assertEquals($ass,true);
		
		$roles = $rbacreview->getRolesOfRoleFolder(8);
		$rbacreview->__getAllRoleFolderIds();
		
		$rbacreview->getLinkedRolesOfRoleFolder(8);
		
		$obj = $rbacreview->getObjectOfRole(4);
		$this->assertEquals(9,$obj);
		
		$rbacreview->getRolesForIDs(array(4),false);
	}
	
	/**
	 * test rbac_ua 
	 */
	public function testRbacUA()
	{
		global $rbacreview,$rbacadmin;
		
		$obj = ilUtil::_getObjectsByOperations('crs','join');
		
		$rbacreview->assignedUsers(4);
		$rbacreview->assignedRoles(6);
	}
	
	/**
	 * rbac ta test 
	 * @param
	 * @return
	 */
	public function testRbacTA()
	{
		global $rbacreview,$rbacadmin;
		
		$sess_ops = $rbacreview->getOperationsOnTypeString('sess');
		
		$rbacadmin->assignOperationToObject($rbacreview->getTypeId('sess'),'7');
		//$new_sess_ops = $rbacreview->getOperationsOnTypeString('sess');
		//$this->assertEquals(array_merge($sess_ops,array(7)),$new_sess_ops);
		
		$rbacadmin->deassignOperationFromObject($rbacreview->getTypeId('sess'),'7');
		$new_sess_ops = $rbacreview->getOperationsOnTypeString('sess');
		$this->assertEquals($sess_ops,$new_sess_ops);
	}

	/**
	 * test rbac_pa 
	 */
	public function testRbacPA()
	{
		global $rbacreview,$rbacadmin;
		
		$sess_ops = $rbacreview->getOperationsOnTypeString('cat');
		
		$rbacadmin->revokePermission(1,4);
		$rbacadmin->grantPermission(4,array(2,3),1);

	}

	/**
	 * test preconditions
	 * @param
	 * @return
	 */
	public function testConditions()
	{
		include_once './Services/AccessControl/classes/class.ilConditionHandler.php';
		
		ilConditionHandler::_getDistinctTargetRefIds();
		ilConditionHandler::_deleteTargetConditionsByRefId(1);
		
		$handler = new ilConditionHandler();
		$handler->setTargetRefId(99999);
		$handler->setTargetObjId(99998);
		$handler->setTargetType('xxx');
		$handler->setTriggerRefId(99997);
		$handler->setTriggerObjId(99996);
		$handler->setTriggerType('yyy');
		$handler->setReferenceHandlingType(0);
		$handler->enableAutomaticValidation(false);
		$suc = $handler->storeCondition();
		$this->assertEquals($suc,true);
		
		$suc = $handler->checkExists();
		$this->assertEquals($suc,false);
		
		$suc = $handler->delete(99999);
		$this->assertEquals($suc,true);
		
		// syntax check
		$handler->deleteByObjId(-1);
		$handler->deleteCondition(-1);
		ilConditionHandler::_getConditionsOfTrigger(-1,-1);
		ilConditionHandler::_getConditionsOfTarget(-1,-1);
		ilConditionHandler::_getCondition(-1);
	}
	
	public function testCache()
	{
		include_once './Services/AccessControl/classes/class.ilAccessHandler.php';
		
		$handler = new ilAccessHandler();
		$handler->setResults(array(1,2,3));
		$handler->storeCache();
		$handler->readCache();
		$res = $handler->getResults();
		
		$this->assertEquals(array(1,2,3),$res);	
	}
	
	
}
?>
