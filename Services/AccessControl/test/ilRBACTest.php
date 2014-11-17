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
	 * @group IL_Init
	 * @param
	 * @return
	 */
	public function testRbacFA()
	{
		global $rbacreview,$rbacadmin;
		
		// Protected
		#$rbacadmin->setProtected(1,4,'y');
		#$prot = $rbacreview->isProtected(8,4);
		#$this->assertEquals($prot,true);
		#$rbacadmin->setProtected(1,4,'n');
		#$prot = $rbacreview->isProtected(8,4);
		#$this->assertEquals($prot,false);
		
		$rbacreview->getRoleListByObject(8);
		$rbacreview->getAssignableRoles();
		
		
		$ass = $rbacreview->isAssignable(4,8);
		$this->assertEquals($ass,true);

		$roles = $rbacreview->getRolesOfObject(8);

		$obj = $rbacreview->getObjectOfRole(4);
		$this->assertEquals(8,$obj);
	}
	
	/**
	 * test rbac_ua
	 * @group IL_Init
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
	 * @group IL_Init
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
	 * @group IL_Init
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
	 * @group IL_Init
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

	/**
	 * @group IL_Init
	 */
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

	/**
	 * Test Assign User Method
	 * DB: rbac_ua
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 */
	public function testAssignUser()
	{
		global $rbacreview, $rbacadmin;
		//assign User 15 to role 10
		$rbacadmin->assignUser(10,15);

		$this->assertTrue($rbacreview->isAssigned(15,10));

		//Test double assign
		$rbacadmin->assignUser(10,15);
	}

	/**
	 * Test deassign user Method
	 * DB: rbac_ua
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testAssignUser
	 */
	public function testDeassignUser()
	{
		global $rbacreview, $rbacadmin;
		//deassign User 15 from role 10
		$rbacadmin->deassignUser(10,15);

		$this->assertFalse($rbacreview->isAssigned(15,10));
	}

	/**
	 * Test grant Permission Method
	 * DB: rbac_pa
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 */
	public function testGrantPermission()
	{
		global $rbacreview, $rbacadmin;
		//grant permissions 10,20 and 30 for role 10 on object 60
		$rbacadmin->grantPermission(10,array(10,20,30),60);

		$this->assertEquals($rbacreview->getActiveOperationsOfRole(60,10), array(10,20,30));
	}

	/**
	 * Test revoke Permission Method
	 * DB: rbac_pa
	 *
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testGrantPermission
	 */
	public function testRevokePermission()
	{
		global $rbacreview, $rbacadmin, $ilDB;

		$req = $ilDB->query("SELECT ref.ref_id FROM object_reference AS ref LEFT JOIN object_data AS data ON data.obj_id = ref.obj_id WHERE data.type='seas';");

		$ref_id = 0;

		while($row = $ilDB->fetchAssoc($req))
		{
			$ref_id = $row["ref_id"];
		}

		$req = $ilDB->query("SELECT obj_id FROM object_data WHERE type='role';");
		$ilDB->fetchAssoc($req);//First role is protected. Dont use it!
		$role1 = $ilDB->fetchAssoc($req)["obj_id"];
		$role2 = $ilDB->fetchAssoc($req)["obj_id"];
		$role3 = $ilDB->fetchAssoc($req)["obj_id"];

		//save normal operations
		$opt1 = $rbacreview->getActiveOperationsOfRole($ref_id,$role1);
		$opt2 = $rbacreview->getActiveOperationsOfRole($ref_id,$role2);
		$opt3 = $rbacreview->getActiveOperationsOfRole($ref_id,$role3);

		$rbacadmin->grantPermission($role1, array(1,2,3,4,5), $ref_id);
		$rbacadmin->grantPermission($role2, array(1,2,3,4,5), $ref_id);
		//$this->assertEquals($rbacreview->getActiveOperationsOfRole($ref_id,$role1), array(1,2,3,4,5));
		//$this->assertEquals($rbacreview->getActiveOperationsOfRole($ref_id,$role2), array(1,2,3,4,5));
		$rbacadmin->revokePermission($ref_id);
		$this->assertEmpty($rbacreview->getActiveOperationsOfRole($ref_id,$role1));
		$this->assertEmpty($rbacreview->getActiveOperationsOfRole($ref_id,$role2));


		$rbacadmin->grantPermission($role1, array(1,2,3,4,5), $ref_id);
		//$this->assertEquals($rbacreview->getActiveOperationsOfRole($ref_id,$role1), array(1,2,3,4,5));
		$rbacadmin->revokePermission($ref_id, $role1);
		$this->assertEmpty($rbacreview->getActiveOperationsOfRole($ref_id,$role1));


		$rbacadmin->grantPermission($role2, array(1,2,3,4,5), $ref_id);
		$rbacadmin->grantPermission($role3, array(1,2,3,4,5), $ref_id);
		//$this->assertEquals($rbacreview->getActiveOperationsOfRole($ref_id,$role2), array(1,2,3,4,5));
		//$this->assertEquals($rbacreview->getActiveOperationsOfRole($ref_id,$role3), array(1,2,3,4,5));
		$rbacadmin->revokePermission($ref_id,0,false);
		$this->assertEmpty($rbacreview->getActiveOperationsOfRole($ref_id,$role2));
		$this->assertEmpty($rbacreview->getActiveOperationsOfRole($ref_id,$role3));

		$rbacadmin->grantPermission($role3, array(1,2,3,4,5), $ref_id);
		//$this->assertEquals($rbacreview->getActiveOperationsOfRole($ref_id,$role3), array(1,2,3,4,5));
		$rbacadmin->revokePermission($ref_id, $role3, false);
		$this->assertEmpty($rbacreview->getActiveOperationsOfRole($ref_id,$role3));

		//set normal operations
		$rbacadmin->grantPermission($role1, $opt1, $ref_id);
		$rbacadmin->grantPermission($role2, $opt2, $ref_id);
		$rbacadmin->grantPermission($role3, $opt3, $ref_id);
	}

	/**
	 * Test revokeSubtreePermissions Method
	 * DB: rbac_pa
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testGrantPermission
	 */
	public function testRevokeSubtreePermissions()
	{
		global $rbacreview, $rbacadmin, $tree, $ilDB;
		$req = $ilDB->query("SELECT ref.ref_id FROM object_reference AS ref LEFT JOIN object_data AS data ON data.obj_id = ref.obj_id WHERE data.type='adm';");

		$ref_id = 0;

		while($row = $ilDB->fetchAssoc($req))
		{
			$ref_id = $row["ref_id"];
		}

		$childs = $tree->getChildIds($ref_id);

		$req = $ilDB->query("SELECT obj_id FROM object_data WHERE type='role';");
		$ilDB->fetchAssoc($req);//First role is protected. Dont use it!
		$role = $ilDB->fetchAssoc($req)["obj_id"];

		$ops = array();

		foreach($childs as $id)
		{
			$ops[$id] = $rbacreview->getActiveOperationsOfRole($id,$role);//save normal operations
			$rbacadmin->grantPermission($role, array(1,2,3,4,5),$id);
			//$this->assertEquals($rbacreview->getActiveOperationsOfRole($id,$role), array(1,2,3,4,5));
		}

		$rbacadmin->revokeSubtreePermissions($ref_id,$role);

		foreach($childs as $id)
		{
			$this->assertEmpty($rbacreview->getActiveOperationsOfRole($id,$role));
			$rbacadmin->grantPermission($role, $ops[$id],$id);//set normal operations
		}
	}

	/**
	 * Test revokePermissionList Method
	 * DB: rbac_pa
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testGrantPermission
	 */
	public function testRevokePermissionList()
	{
		global $rbacreview, $rbacadmin;
		$list = array(1001, 1003, 1005, 1007);

		foreach($list as $id)
		{
			$rbacadmin->grantPermission(123, array(1,2,3,4,5),$id);
		}

		$rbacadmin->revokePermissionList($list, 123);

		foreach($list as $id)
		{
			$this->assertEmpty($rbacreview->getActiveOperationsOfRole($id,123));
		}
	}

	/**
	 * Test Set Role Permission Method
	 * DB: rbac_template
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 */
	public function testSetRolePermission()
	{
		global $rbacreview, $rbacadmin;
		$rbacadmin->deleteTemplate(1010);

		$rbacadmin->setRolePermission(1010,"a",array(10,11,13,15),1100);
		$rbacadmin->setRolePermission(1010,"b",array(20,22,23,25),1100);

		$assert = array("a" => array(10,11,13,15),"b" => array(20,22,23,25));
		$dest = $rbacreview->getAllOperationsOfRole(1010,1100);

		sort($dest["a"]);
		sort($dest["b"]);

		$this->assertEquals($assert, $dest);

		$rbacadmin->deleteTemplate(1010);
	}

	/**
	 * Test Delete Role Permission Method
	 * DB: rbac_template
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testSetRolePermission
	 */
	public function testDeleteRolePermission()
	{
		global $rbacreview, $rbacadmin;
		$rbacadmin->deleteTemplate(1010);

		$rbacadmin->setRolePermission(1010,"a",array(10,11,13,15),1100);
		$rbacadmin->setRolePermission(1010,"b",array(20,22,23,25),1100);

		$rbacadmin->deleteRolePermission(1010,1100);

		$this->assertEmpty($rbacreview->getAllOperationsOfRole(1010,1100));

		$rbacadmin->setRolePermission(1010,"a",array(10,11,13,15),1100);
		$rbacadmin->setRolePermission(1010,"b",array(20,22,23,25),1100);

		$rbacadmin->deleteRolePermission(1010,1100, "a");

		$assert = array("b" => array(20,22,23,25));
		$dest = $rbacreview->getAllOperationsOfRole(1010,1100);

		sort($dest["b"]);

		$this->assertEquals($assert, $dest);

		$rbacadmin->deleteTemplate(1010);
	}

	/**
	 * Test Copy Role Template Permission Method
	 * DB: rbac_template
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testSetRolePermission
	 */
	public function testCopyRoleTemplatePermissions()
	{
		global $rbacreview, $rbacadmin;
		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);

		$rbacadmin->setRolePermission(1010,"blub",array(10,11),1100);
		$rbacadmin->setRolePermission(2020,"bulb",array(20,22),2200);

		$rbacadmin->copyRoleTemplatePermissions(1010,1100,2200,2020);

		$one = $rbacreview->getAllOperationsOfRole(1010,1100);
		$two = $rbacreview->getAllOperationsOfRole(2020,2200);
		sort($one["blub"]);
		sort($two["blub"]);
		$this->assertEquals($one, $two);
		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);
	}

	/**
	 * Test Method
	 * DB: rbac_template
	 * DB: rbac_pa
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testGrantPermission
	 * @depends testRevokePermission
	 * @depends testSetRolePermission
	 */
	public function testCopyRolePermissions()
	{
		global $rbacreview, $rbacadmin, $ilDB;

		$req = $ilDB->query("SELECT ref.ref_id FROM object_reference AS ref LEFT JOIN object_data AS data ON data.obj_id = ref.obj_id WHERE data.type='seas';");

		$seas = 0;

		while($row = $ilDB->fetchAssoc($req))
		{
			$seas = $row["ref_id"];
		}

		$req = $ilDB->query("SELECT ref.ref_id FROM object_reference AS ref LEFT JOIN object_data AS data ON data.obj_id = ref.obj_id WHERE data.type='mail';");

		$mail = 0;

		while($row = $ilDB->fetchAssoc($req))
		{
			$mail = $row["ref_id"];
		}

		$req = $ilDB->query("SELECT obj_id FROM object_data WHERE type='role';");
		$ilDB->fetchAssoc($req);//First role is protected. Dont use it!
		$role = $ilDB->fetchAssoc($req)["obj_id"];

		//save normal operations
		$opt_mail = $rbacreview->getActiveOperationsOfRole($mail, $role);
		$opt_seas = $rbacreview->getActiveOperationsOfRole($seas, $role);
		$opt_temp_seas = $rbacreview->getAllOperationsOfRole($role, $seas);
		$opt_temp_mail = $rbacreview->getAllOperationsOfRole($role, $mail);

		//set values
		$rbacadmin->setRolePermission($role, "mail", array(1,2,3,4,5),$mail);
		$rbacadmin->grantPermission($role, array(1,2,3,4,5), $mail);
		$rbacadmin->setRolePermission($role, "seas", array(5,6,7,8,9),$seas);
		$rbacadmin->grantPermission($role, array(5,6,7,8,9), $seas);

		$rbacadmin->copyRolePermissions($role,$seas,$mail, $role);
		$this->assertEquals($rbacreview->getActiveOperationsOfRole($seas, $role),
			$rbacreview->getActiveOperationsOfRole($mail, $role));

		//set normal operations
		$rbacadmin->grantPermission($role,$opt_seas,$seas);
		$rbacadmin->grantPermission($role,$opt_mail,$mail);

		$rbacadmin->deleteRolePermission($role,$mail);
		$rbacadmin->deleteRolePermission($role,$seas);

		foreach($opt_temp_seas as $type => $opt)
		{
			$rbacadmin->setRolePermission($role, $type, $opt,$seas);
		}

		foreach($opt_temp_mail as $type => $opt)
		{
			$rbacadmin->setRolePermission($role, $type, $opt,$mail);
		}

	}

	/**
	 * Test Copy Role Permission Intersection Method
	 * DB: rbac_template
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testSetRolePermission
	 */
	public function testCopyRolePermissionIntersection()
	{
		global $rbacreview, $rbacadmin;
		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);
		$rbacadmin->deleteTemplate(3030);

		$rbacadmin->setRolePermission(1010,"a",array(10,11,13,15),1100);
		$rbacadmin->setRolePermission(2020,"a",array(11,12,13,16),2200);

		$rbacadmin->setRolePermission(1010,"b",array(20,22,23,25),1100);
		$rbacadmin->setRolePermission(2020,"b",array(20,23,24,26),2200);

		$rbacadmin->setRolePermission(3030,"c",array(30,33),3300);
		$rbacadmin->setRolePermission(3030,"a",array(30,33),3300);
		$rbacadmin->setRolePermission(3030,"b",array(30,33),3300);

		$rbacadmin->copyRolePermissionIntersection(1010,1100,2020,2200,3300,3030);

		$intersect = array("a" => array(11,13), "b" => array(20,23));
		$dest = $rbacreview->getAllOperationsOfRole(3030,3300);

		//sort
		sort($dest["a"]);
		sort($dest["b"]);

		$this->assertEquals($intersect, $dest);

		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);
		$rbacadmin->deleteTemplate(3030);
	}

	/**
	 * Test Copy Role Permission Union Method
	 * DB: rbac_template
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testCopyRoleTemplatePermissions
	 * @depends testSetRolePermission
	 */
	public function testCopyRolePermissionUnion()
	{
		global $rbacreview, $rbacadmin;
		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);
		$rbacadmin->deleteTemplate(3030);

		$rbacadmin->setRolePermission(1010,"a",array(10,11,13,15),1100);
		$rbacadmin->setRolePermission(2020,"a",array(11,12,13,16),2200);

		$rbacadmin->setRolePermission(1010,"b",array(20,22,23,25),1100);
		$rbacadmin->setRolePermission(2020,"b",array(20,23,24,26),2200);

		$rbacadmin->setRolePermission(1010,"c",array(30,33,34,35),1100);

		$rbacadmin->copyRolePermissionUnion(1010,1100,2020,2200,3030,3300);

		$union = array("a" => array(10,11,12,13,15,16), "b" => array(20,22,23,24,25,26), "c" => array(30,33,34,35));
		$dest = $rbacreview->getAllOperationsOfRole(3030,3300);

		sort($dest["a"]);
		sort($dest["b"]);
		sort($dest["c"]);

		$this->assertEquals($union, $dest);

		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);
		$rbacadmin->deleteTemplate(3030);
	}

	/**
	 * Test Copy Role Permission Subtract Method
	 * DB: rbac_template
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testSetRolePermission
	 */
	public function testCopyRolePermissionSubtract()
	{
		global $rbacreview, $rbacadmin;
		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);

		$rbacadmin->setRolePermission(1010,"a",array(10,11,13,15),1100);
		$rbacadmin->setRolePermission(2020,"a",array(11,12,13,16),2200);

		$rbacadmin->setRolePermission(1010,"b",array(20,22,23,25),1100);
		$rbacadmin->setRolePermission(2020,"b",array(20,23,24,26),2200);

		$rbacadmin->setRolePermission(2020,"c",array(30,33,34,35),2200);

		$rbacadmin->copyRolePermissionSubtract(1010,1100,2020,2200);

		$subtract = array("a" => array(12,16), "b" => array(24,26), "c" => array(30,33,34,35));
		$dest = $rbacreview->getAllOperationsOfRole(2020,2200);

		sort($dest["a"]);
		sort($dest["b"]);
		sort($dest["c"]);

		$this->assertEquals($subtract, $dest);

		$rbacadmin->deleteTemplate(1010);
		$rbacadmin->deleteTemplate(2020);
	}

	/**
	 * Test assignOperationToObject Method
	 * DB: rbac_ta
	 *
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 */
	public function testAssignOperationToObject()
	{
		global $rbacreview, $rbacadmin;

		$rbacadmin->assignOperationToObject(1001,10);
		$rbacadmin->assignOperationToObject(1001,20);

		$this->assertEquals($rbacreview->getOperationsOnType(1001), array(10,20));
	}

	/**
	 * Test deassignOperationFromObject Method
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacAdmin $rbacadmin
	 * @depends testAssignOperationToObject
	 */
	public function testDeassignOperationFromObject()
	{
		global $rbacreview, $rbacadmin;
		$rbacadmin->deassignOperationFromObject(1001,10);

		$this->assertEquals($rbacreview->getOperationsOnType(1001), array(20));

		$rbacadmin->deassignOperationFromObject(1001,20);

		$this->assertEmpty($rbacreview->getOperationsOnType(1001));
	}

}
?>
