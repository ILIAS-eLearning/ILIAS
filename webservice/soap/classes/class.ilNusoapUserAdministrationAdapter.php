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
* adapter class for nusoap server
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

include_once './webservice/soap/lib/nusoap.php';
include_once './webservice/soap/include/inc.soap_functions.php';

class ilNusoapUserAdministrationAdapter
{
	/*
	 * @var object Nusoap-Server
	 */
	var $server = null;


    function ilNusoapUserAdministrationAdapter($a_use_wsdl = true)
    {
		define('SERVICE_NAME','ILIASSoapWebservice');
		define('SERVICE_NAMESPACE','urn:ilUserAdministration');
		define('SERVICE_STYLE','rpc');
		define('SERVICE_USE','encoded');
		global $debug; $debug = true;
		$this->server =& new soap_server();
		$this->server->decode_utf8 = false;
		if($a_use_wsdl)
		{
			$this->__enableWSDL();
		}

		$this->__registerMethods();


    }

	function start()
	{
		global $HTTP_RAW_POST_DATA;

		$this->server->service($HTTP_RAW_POST_DATA);
		exit();
	}

	// PRIVATE
	function __enableWSDL()
	{
		$this->server->configureWSDL(SERVICE_NAME,SERVICE_NAMESPACE);

		return true;
	}


	function __registerMethods()
	{

		// Add useful complex types. E.g. array("a","b") or array(1,2)
		$this->server->wsdl->addComplexType('intArray',
											'complexType',
											'array',
											'',
											'SOAP-ENC:Array',
											array(),
											array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:int[]')),
											'xsd:int');

		$this->server->wsdl->addComplexType('stringArray',
											'complexType',
											'array',
											'',
											'SOAP-ENC:Array',
											array(),
											array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')),
											'xsd:string');

		// It's not possible to register classes in nusoap

		// login()
		$this->server->register('login',
								array('client' => 'xsd:string',
									  'username' => 'xsd:string',
									  'password' => 'xsd:string'),
								array('sid' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#login',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS login function');

		// loginCAS()
		$this->server->register('loginCAS',
								array('client' => 'xsd:string',
									  'PT' => 'xsd:string',
									  'user' => 'xsd:string'),
								array('sid' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#loginCAS',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS login function via CAS');
		// loginLDAP()
		$this->server->register('loginLDAP',
								array('client' => 'xsd:string',
									  'username' => 'xsd:string',
									  'password' => 'xsd:string'),
								array('sid' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#login',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS login function via LDAP');



								// logout()
		$this->server->register('logout',
								array('sid' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#logout',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS logout function');
		// user_data definitions
		$this->server->wsdl->addComplexType('ilUserData',
											'complexType',
											'struct',
											'all',
											'',
											array('usr_id' => array('name' => 'usr_id','type' => 'xsd:int'),
												  'login' => array('name' => 'login', 'type' => 'xsd:string'),
												  'passwd' => array('name' => 'passwd', 'type' => 'xsd:string'),
												  'firstname' => array('name' => 'firstname', 'type' => 'xsd:string'),
												  'lastname' => array('name' => 'lastname', 'type' => 'xsd:string'),
												  'title' => array('name' => 'title', 'type' => 'xsd:string'),
												  'gender' => array('name' => 'gender', 'type' => 'xsd:string'),
												  'email' => array('name' => 'email', 'type' => 'xsd:string'),
												  'institution' => array('name' => 'institution', 'type' => 'xsd:string'),
												  'street' => array('name' => 'street', 'type' => 'xsd:string'),
												  'city' => array('name' => 'city', 'type' => 'xsd:string'),
												  'zipcode' => array('name' => 'zipcode', 'type' => 'xsd:string'),
												  'country' => array('name' => 'country', 'type' => 'xsd:string'),
												  'phone_office' => array('name' => 'phone_office', 'type' => 'xsd:string'),
												  'last_login' => array('name' => 'last_login', 'type' => 'xsd:string'),
												  'last_update' => array('name' => 'last_update', 'type' => 'xsd:string'),
												  'create_date' => array('name' => 'create_date', 'type' => 'xsd:string'),
												  'hobby' => array('name' => 'hobby', 'type' => 'xsd:string'),
												  'department' => array('name' => 'department', 'type' => 'xsd:string'),
												  'phone_home' => array('name' => 'phone_home', 'type' => 'xsd:string'),
												  'phone_mobile' => array('name' => 'phone_mobile', 'type' => 'xsd:string'),
												  'fax' => array('name' => 'fax', 'type' => 'xsd:string'),
												  'time_limit_owner' => array('name' => 'time_limit_owner', 'type' => 'xsd:int'),
												  'time_limit_unlimited' => array('name' => 'time_limit_unlimited', 'type' => 'xsd:int'),
												  'time_limit_from' => array('name' => 'time_limit_from', 'type' => 'xsd:int'),
												  'time_limit_until' => array('name' => 'time_limit_until', 'type' => 'xsd:int'),
												  'time_limit_message' => array('name' => 'time_limit_message', 'type' => 'xsd:int'),
												  'referral_comment' => array('name' => 'referral_comment', 'type' => 'xsd:string'),
												  'matriculation' => array('name' => 'matriculation', 'type' => 'xsd:string'),
												  'active' => array('name' => 'active', 'type' => 'xsd:int'),
												  'accepted_agreement' => array('name' => 'accepted_agreement','type' => 'xsd:boolean'),
												  'approve_date' => array('name' => 'approve_date', 'type' => 'xsd:string'),
												  'user_skin' => array('name' => 'user_skin', 'type' => 'xsd:string'),
												  'user_style' => array('name' => 'user_style', 'type' => 'xsd:string'),
												  'user_language' => array('name' => 'user_language', 'type' => 'xsd:string'),
												  'import_id' => array('name' => 'import_id', 'type' => 'xsd:string')
												  ));


		// lookupUser()
		$this->server->register('lookupUser',
								array('sid' => 'xsd:string',
									  'user_name' => 'xsd:string'),
								array('usr_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#lookupUser',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS lookupUser(): check if username exists. Return usr_id or 0 if lookup fails.');


		// getUser()
		$this->server->register('getUser',
								array('sid' => 'xsd:string',
									  'user_id' => 'xsd:int'),
								array('user_data' => 'tns:ilUserData'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getUser',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getUser(): get complete set of user data.');
		// updateUser()
		$this->server->register('updateUser',
								array('sid' => 'xsd:string',
									  'user_data' => 'tns:ilUserData'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#updateUser',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS updateUser(). DEPRECATED: Use importUsers() for modifications of user data. Updates all user data. '.
								'Use getUser(), then modify desired fields and finally start the updateUser() call.');
		// Update password
		$this->server->register('updatePassword',
								array('sid' => 'xsd:string',
									  'user_id' => 'xsd:int',
									  'new_password' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#updatePassword',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS updatePassword(). Updates password of given user. Password must be MD5 hash');


		// addUser()
		$this->server->register('addUser',
								array('sid' => 'xsd:string',
									  'user_data' => 'tns:ilUserData',
									  'global_role_id' => 'xsd:int'),
								array('user_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addUser',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addUser() user. DEPRECATED: Since it is not possible to add new user data fields '.
								'without breaking the backward compatability, this method is deprecated. Please use importUser() instead. '.
								'Add new ILIAS user. Requires complete or subset of user_data structure');

		// deleteUser()
		$this->server->register('deleteUser',
								array('sid' => 'xsd:string',
									  'user_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#deleteUser',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS deleteUser(). Deletes all user related data (Bookmarks, Mails ...)');

		// addCourse()
		$this->server->register('addCourse',
								array('sid' => 'xsd:string',
									  'target_id' => 'xsd:int',
									  'crs_xml' => 'xsd:string'),
								array('course_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addCourse',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addCourse(). Course import. See ilias_course_0_1.dtd for details about course xml structure');

		// deleteCourse()
		$this->server->register('deleteCourse',
								array('sid' => 'xsd:string',
									  'course_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#deleteCourse',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS deleteCourse(). Deletes a course. Delete courses are stored in "Trash" and can be undeleted in '.
								' the ILIAS administration. ');
		// assignCourseMember()
		$this->server->register('assignCourseMember',
								array('sid' => 'xsd:string',
									  'course_id' => 'xsd:int',
									  'user_id' => 'xsd:int',
									  'type' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#assignCourseMember',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS assignCourseMember(). Assigns an user to an existing course. Type should be "Admin", "Tutor" or "Member"');

		// excludeCourseMember()
		$this->server->register('excludeCourseMember',
								array('sid' => 'xsd:string',
									  'course_id' => 'xsd:int',
									  'user_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#excludeCourseMember',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS excludeCourseMember(). Excludes an user from an existing course.');

		// isAssignedToCourse()
		$this->server->register('isAssignedToCourse',
								array('sid' => 'xsd:string',
									  'course_id' => 'xsd:int',
									  'user_id' => 'xsd:int'),
								array('role' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#isAssignedToCourse',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS isAssignedToCourse(). Checks whether an user is assigned to a given course. '.
								'Returns 0 => not assigned, 1 => course admin, 2 => course member or 3 => course tutor');

		// getCourseXML($sid,$course_id)
		$this->server->register('getCourseXML',
								array('sid' => 'xsd:string',
									  'course_id' => 'xsd:int'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getCourseXML',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getCourseXML(). Get a xml description of a specific course.');

		// updateCourse($sid,$course_id,$xml)
		$this->server->register('updateCourse',
								array('sid' => 'xsd:string',
									  'course_id' => 'xsd:int',
									  'xml' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#updateCourse',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS updateCourse(). Update course settings, assigned members, tutors, administrators with a '.
								'given xml description');

		// get obj_id by import id
		$this->server->register('getObjIdByImportId',
								array('sid' => 'xsd:string',
									  'import_id' => 'xsd:string'),
								array('obj_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getCourseIdByImportId',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getObjIdByImportId(). Get the obj_id of an ILIAS obj by a given import id.');


		// get ref ids by import id
		$this->server->register('getRefIdsByImportId',
								array('sid' => 'xsd:string',
									  'import_id' => 'xsd:string'),
								array('ref_ids' => 'tns:intArray'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getRefIdsByImportId',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getRefIdsByImportId(). Get all reference ids by a given import id.');

		// get obj_id by import id
		$this->server->register('getRefIdsByObjId',
								array('sid' => 'xsd:string',
									  'obj_id' => 'xsd:string'),
								array('ref_ids' => 'tns:intArray'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getRefIdsByObjId',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getRefIdsByObjId(). Get all reference ids by a given object id.');

		// Object administration
		$this->server->register('getObjectByReference',
								array('sid' => 'xsd:string',
									  'reference_id' => 'xsd:int',
									  'user_id' => 'xsd:int'),
								array('object_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getObjectByReference',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getObjectByReference(). Get XML-description of an ILIAS object. If a user id is given, '.
								'this methods also checks the permissions of that user on the object.');

		$this->server->register('getObjectsByTitle',
								array('sid' => 'xsd:string',
									  'title' => 'xsd:string',
									  'user_id' => 'xsd:int'),
								array('object_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getObjectsByTitle',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getObjectsByTitle(). Get XML-description of an ILIAS object with given title. '.
								'If a user id is given this method also checks the permissions of that user on the object.');

		$this->server->register('searchObjects',
								array('sid' => 'xsd:string',
									  'types' => 'tns:stringArray',
									  'key' => 'xsd:string',
									  'combination' => 'xsd:string',
									  'user_id' => 'xsd:int'),
								array('object_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#searchObjects',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS searchObjects(): Searches for objects. Key is within "title" or "description" '.
								'Typical calls are searchObject($sid,array("lm","crs"),"\"this and that\"","and"); '.
								' If an optional user id is given, this methods also return the permissions for that user '.
								'on the found objects');

		$this->server->register('getTreeChilds',
								array('sid' => 'xsd:string',
									  'ref_id' => 'xsd:int',
									  'types' => 'tns:stringArray',
									  'user_id' => 'xsd:int'),
								array('object_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getTreeChilds',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getTreeChilds(): Get all child objects of a given object.'.
								'Choose array of types to filter the output. Choose empty type array to receive all object types');

		$this->server->register('getXMLTree',
					array('sid' => 'xsd:string',
					      'ref_id' => 'xsd:int',
					      'types' => 'tns:stringArray',
					      'user_id' => 'xsd:int'),
					array('object_xml' => 'xsd:string'),
					SERVICE_NAMESPACE,
					SERVICE_NAMESPACE.'#getXMLTree',
					SERVICE_STYLE,
					SERVICE_USE,
					'ILIAS getXMLTree(): Returns a xml stream with the subtree objects.');



		$this->server->register('addObject',
								array('sid' => 'xsd:string',
									  'target_id' => 'xsd:int',
									  'object_xml' => 'xsd:string'),
								array('ref_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addObject',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addObject. Create new object based on xml description under a given node '.
								'("category,course,group or folder). Return created reference id of the new object.' );

		$this->server->register('updateObjects',
								array('sid' => 'xsd:string',
									  'object_xml' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#updateObjects',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS updateObjects. Update object data (title,description,owner)');

		$this->server->register('addReference',
								array('sid' => 'xsd:string',
									  'source_id' => 'xsd:int',
									  'target_id' => 'xsd:int'),
								array('ref_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addReference',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addReference. Create new link of given object to new object. Return the new reference id');

		$this->server->register('deleteObject',
								array('sid' => 'xsd:string',
									  'reference_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#deleteObject',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS deleteObject. Stores object in trash. If multiple references exist, only the reference is deleted ');


		$this->server->register('removeFromSystemByImportId',
								array('sid' => 'xsd:string',
									  'import_id' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#removeFromSystemByImportId',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS removeFromSystemByImportId(). Removes an object identified by its import id permanently from the '.
								'system. All data will be deleted. There will be no possibility to restore it from the trash. Do not use '.
								'this function for deleting roles or users. Use deleteUser() or deleteRole() instead.');

		$this->server->register('addUserRoleEntry',
								array('sid' => 'xsd:string',
									  'user_id' => 'xsd:int',
									  'role_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addUserRoleEntry',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addUserRoleEntry. Assign user to role.');

		$this->server->register('deleteUserRoleEntry',
								array('sid' => 'xsd:string',
									  'user_id' => 'xsd:int',
									  'role_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#deleteUserRoleEntry',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS deleteUserRoleEntry. Deassign user from role.');


		// Add complex type for operations e.g array(array('name' => 'read','ops_id' => 2),...)
		$this->server->wsdl->addComplexType('ilOperation',
											'complexType',
											'struct',
											'all',
											'',
											array('ops_id' => array('name' => 'ops_id',
																	'type' => 'xsd:int'),
												  'operation' => array('name' => 'operation',
																	   'type' => 'xsd:string'),
												  'description' => array('name' => 'description',
																		 'type' => 'xsd:string')));
		// Now create an array of ilOperations
		$this->server->wsdl->addComplexType('ilOperations',
											'complexType',
											'array',
											'',
											'SOAP-ENC:Array',
											array(),
											array(array('ref' => 'SOAP-ENC:arrayType',
														'wsdl:arrayType' => 'tns:ilOperation[]')),
											'tns:ilOperation');
		$this->server->register('getOperations',
								array('sid' => 'xsd:string'),
								array('operations' => 'tns:ilOperations'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getOperations',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getOperations(): get complete set of RBAC operations.');

		$this->server->register('revokePermissions',
								array('sid' => 'xsd:string',
									  'ref_id' => 'xsd:int',
									  'role_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#revokePermissions',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS revokePermissions(): Revoke all permissions for a specific role on an object.');

		$this->server->wsdl->addComplexType('ilOperationIds',
											'complexType',
											'array',
											'',
											'SOAP-ENC:Array',
											array(),
											array(array('ref' => 'SOAP-ENC:arrayType',
														'wsdl:arrayType' => 'xsd:int[]')),
											'xsd:int');

		$this->server->register('grantPermissions',
								array('sid' => 'xsd:string',
									  'ref_id' => 'xsd:int',
									  'role_id' => 'xsd:int',
									  'operations' => 'tns:intArray'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#grantPermissions',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS grantPermissions(): Grant permissions for a specific role on an object. '.
								'(Substitutes existing permission settings)');

		$this->server->register('getLocalRoles',
								array('sid' => 'xsd:string',
									  'ref_id' => 'xsd:int'),
								array('role_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getLocalRoles',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getLocalRoles(): Get all local roles assigned to an specific object.');

		$this->server->register('getUserRoles',
								array('sid' => 'xsd:string',
									  'user_id' => 'xsd:int'),
								array('role_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getLocalRoles',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getUserRoles(): Get all local roles assigned to an specific user. ');

		$this->server->register('addRole',
								array('sid' => 'xsd:string',
									  'target_id' => 'xsd:int',
									  'obj_xml' => 'xsd:string'),
								array('role_ids' => 'tns:intArray'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addRole',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addRole(): Creates new role under given node. "target_id" is the reference id of an ILIAS '.
								'ILIAS object. E.g ref_id of crs,grp. If no role folder exists, a new role folder will be created.');

		$this->server->register('deleteRole',
								array('sid' => 'xsd:string',
									  'role_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#deleteRole',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS deleteRole(): Deletes an role and all user assignments. Fails if it is the last role of an user');

		$this->server->register('addRoleFromTemplate',
								array('sid' => 'xsd:string',
									  'target_id' => 'xsd:int',
									  'obj_xml' => 'xsd:string',
									  'role_template_id' => 'xsd:int'),
								array('role_ids' => 'tns:intArray'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addRole',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addRole(): Creates new role under given node. "target_id" is the reference id of an ILIAS '.
								'ILIAS object. E.g ref_id of crs,grp. If no role folder exists, a new role folder will be created. '.
								'In addition to addRole the template permissions will be copied from the given role template');

		$this->server->register('getObjectTreeOperations',
								array('sid' => 'xsd:string',
									  'ref_id' => 'xsd:int',
									  'user_id' => 'xsd:int'),
								array('operations' => 'tns:ilOperations'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getPermissionsForObject',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getObjectTreeOperations(): Get all granted permissions for all references of '.
								'an object for a specific user. Returns array of granted operations or empty array');

		$this->server->register('addGroup',
								array('sid' => 'xsd:string',
									  'target_id' => 'xsd:int',
									  'group_xml' => 'xsd:string'),
								array('ref_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addGroup',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addGroup(): Add grop according to valid group XML '.
								'@See ilias_group_0_1.dtd');

		$this->server->register('groupExists',
								array('sid' => 'xsd:string',
									  'title' => 'xsd:string'),
								array('exists' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#groupExists',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addGroup(): Check if group with given name exists. ');


		// getGroup
		$this->server->register('getGroup',
								array('sid' => 'xsd:string',
									  'ref_id' => 'xsd:int'),
								array('group_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getGroup',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getGroup(): get xml description of grouip with given reference id.');

		// assignGroupMember()
		$this->server->register('assignGroupMember',
								array('sid' => 'xsd:string',
									  'group_id' => 'xsd:int',
									  'user_id' => 'xsd:int',
									  'type' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#assignGroupMember',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS assignGroupMember(). Assigns an user to an existing group. Type should be "Admin","Member"');

		// excludeGroupMember()
		$this->server->register('excludeGroupMember',
								array('sid' => 'xsd:string',
									  'group_id' => 'xsd:int',
									  'user_id' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#excludeGroupMember',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS excludeGroupMember(). Excludes an user from an existing group.');

		// isAssignedToGroup()
		$this->server->register('isAssignedToGroup',
								array('sid' => 'xsd:string',
									  'group_id' => 'xsd:int',
									  'user_id' => 'xsd:int'),
								array('role' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#isAssignedToGroup',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS isAssignedToGroup(). Checks whether an user is assigned to a given group. '.
								'Returns 0 => not assigned, 1 => group admin, 2 => group member');



		// ILIAS util functions
		$this->server->register('sendMail',
								array('sid' => 'xsd:string',
									  'rcp_to' => 'xsd:string',
									  'rcp_cc' => 'xsd:string',
									  'rcp_bcc' => 'xsd:string',
									  'sender' => 'xsd:string',
									  'subject' => 'xsd:string',
									  'message' => 'xsd:string',
									  'attachments' => 'xsd:string'),
								array('status' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#sendMail',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS sendMail(): Send mime mails according to xml description. Only for internal usage '.
								'Syntax, parameters may change in future releases');
		// Clone functions
		$this->server->register('ilClone',
								array('sid' => 'xsd:string','copy_identifier' => 'xsd:int'),
								array('new_ref_id' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#ilClone',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS ilClone(): Only for internal usage.'.
								'Syntax, parameters may change in future releases. ');

		$this->server->register('ilCloneDependencies',
								array('sid' => 'xsd:string','copy_identifier' => 'xsd:int'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#ilCloneDependencies',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS ilCloneDependencies(): Only for internal usage.'.
								'Syntax, parameters may change in future releases. ');

		$this->server->register('saveQuestionResult',
								array('sid' => 'xsd:string',
									  'user_id' => 'xsd:int',
									  'test_id' => 'xsd:int',
									  'question_id' => 'xsd:int',
									  'pass' => 'xsd:int',
									  'solution' => 'tns:stringArray'),
								array('status' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#saveQuestionResult',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS saveQuesionResult: Typically called from an external assessment question to save the user input. DEPRECATED since ILIAS 3.9');

		$this->server->register('saveQuestion',
								array('sid' => 'xsd:string',
									  'active_id' => 'xsd:long',
									  'question_id' => 'xsd:long',
									  'pass' => 'xsd:int',
									  'solution' => 'tns:stringArray'),
								array('status' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#saveQuestion',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS saveQuestion: Saves the result of a question in a given test pass for the active test user. The active user is identified by the active ID, which assigns a user to a test.');

		$this->server->register('getQuestionSolution',
								array('sid' => 'xsd:string',
									  'active_id' => 'xsd:long',
									  'question_id' => 'xsd:int',
									  'pass' => 'xsd:int'),
								array('solution' => 'tns:stringArray'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getQuestionSolution',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getQuestionSolution: Typically called from external assessment questions to retrieve the previous input of a user.');

		$this->server->register('getStructureObjects',
								array('sid' => 'xsd:string',
									  'ref_id' => 'xsd:int'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getStructureObjects',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getStructureObjects: delivers structure of content objects like learning modules (chapters/pages) or glossary (terms)');

		// importUsers()
		$this->server->register('importUsers',
								array('sid' => 'xsd:string',
									  'folder_id' => 'xsd:int',
									  'usr_xml' => 'xsd:string',
									  'conflict_rule' => 'xsd:int',
									  'send_account_mail' => 'xsd:int'),
								array('protocol' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#importUsers',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS import users into folder id, which should be ref_id of folder or user folder (-1:System user folder, 0: checks access at user level, otherwise refid): conflict_rule: IL_FAIL_ON_CONFLICT = 1, IL_UPDATE_ON_CONFLICT = 2, IL_IGNORE_ON_CONFLICT = 3. The Return-Value is a protocol with the columns userid, login, action, message, following xmlresultset dtd. Send Account Mail = 0 deactivates sending a mail to each user, 1 activates it');

		$this->server->register('getRoles',
								array('sid' => 'xsd:string',
								      'role_type' => 'xsd:string',
								      'id' => 'xsd:string'),
								array('role_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getRoles',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getRoles():if id equals -1, get all roles specified by type (global|local|user|user_login|template or empty), if type is empty all roles with all types are delivered, if id > -1 and role_type <> user or user_login, delivers all roles which belong to a repository object with specified ref_id, if roletype is user a numeric id is interpreted as userid, if roletype is user_login it is interpreted as login,if roletype is template all role templates will be listed');

		$this->server->register('getUsersForContainer',
								array('sid' => 'xsd:string',
								'ref_id' => 'xsd:int',
	   			     				'attach_roles' => 'xsd:int',
								    'active' => 'xsd:int'),
								array('user_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getUsersForContainer',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getUsersForContainer(): get all users of a specific ref_id, which can be crs, group, category or user folder (value: -1). Choose if all roles of a user should be attached (1) or not (0). set active to -1 to get all, 0, to get inactive users only, 1 to get active users only');

		$this->server->register('getUsersForRole',
								array('sid' => 'xsd:string',
								      'role_id' => 'xsd:int',
								      'attach_roles' => 'xsd:int',
								      'active' => 'xsd:int'),
								array('user_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getUsersForRole',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getUsersForRole(): get all users of a role with specified id, specify attach_roles to 1, to attach all role assignmnents; specify active: 1, to import active only, 0: inactive only, -1: both');

		$this->server->register('searchUser',
								array('sid' => 'xsd:string',
								      'key_fields' => 'tns:stringArray',
								      'query_operator' => 'xsd:string',
								      'key_values' => 'tns:stringArray',
								      'attach_roles' => 'xsd:int',
								      'active' => 'xsd:int'),

								array('user_xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#searchUsers',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS searchUser(): get all users, which match a query, consisting of the keyfields, matched with values of the field values, concatenated with the logical query operator. Specify attach_roles to 1, to attach all role assignmnents; specify active: 1, to import active only, 0: inactive only, -1: both');

		// Mail Functions
		// Check whether current user has new mail
		$this->server->register('hasNewMail',
								array('sid' => 'xsd:string'),
								array('status' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#hasNewMail',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS hasNewMail(): Checks whether the current authenticated user has a new mail.');

		$this->server->register('getNIC',
								array('sid' => 'xsd:string'),
								array('xmlresultset' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getNIC',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getNIC(): return client information from current client as xml result set containing installation_id, installation_version, installation_url, installation_description, installation_language_default as columns');

        $this->server->register('getExerciseXML',
								array('sid' => 'xsd:string', "ref_id" => 'xsd:int', "attachment_mode" => "xsd:int"),
								array('exercisexml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getExerciseXML',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getExerciseXML(): returns xml description of exercise. Attachment mode: 0 - no file contents, 1 - plain content (base64encoded), 2 zlib + base64, 3 gzip + base64)');

        $this->server->register('addExercise',
								array('sid' => 'xsd:string', "target_id" => 'xsd:int', "xml" => "xsd:string"),
								array('refid' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addExercise',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addExercise(): create exercise, put it into target (ref_id) and update exercise properties from xml (see ilias_exercise_3_8.dtd for details). Obj_id must not be set!');

        $this->server->register('updateExercise',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int', 'xml' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#updateExercise',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS updateExercise():update existing exercise, update exercise properties from xml (see ilias_exercise_3_8.dtd for details). obj_id in xml must match according obj id of refid.!');

        $this->server->register('getFileXML',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int', 'attachment_mode' => 'xsd:int'),
								array('filexml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getFileXML',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getFileXML(): returns xml description of file. Attachment mode: 0 - no file contents, 1 - plain content (base64encoded), 2 zlib + base64, 3 gzip + base64)');

        $this->server->register('addFile',
								array('sid' => 'xsd:string', 'target_id' => 'xsd:int', 'xml' => 'xsd:string'),
								array('refid' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#addFile',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS addFile(): create file, put it into target (ref_id) and update file properties from xml (see ilias_file_3_8.dtd for details). Obj_id must not be set!');

        $this->server->register('updateFile',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int', 'xml' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#updateFile',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS updateFile():update existing file, update file properties from xml (see ilias_file_3_8.dtd for details). obj_id in xml must match according obj id of refid.!');


      	$this->server->register('getUserXML',
								array('sid' => 'xsd:string', 'user_ids' => 'tns:intArray', 'attach_roles' => 'xsd:int'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#resolveUsers',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getUserXML(): get xml records for user ids, e.g. retrieved vom members of course xml. Returns user xml dtds. ids are numeric ids of user');


		// get objs ids by ref id
		$this->server->register('getObjIdsByRefIds',
								array('sid' => 'xsd:string',
									  'ref_ids' => 'tns:intArray'),
								array('obj_ids' => 'tns:intArray'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getRefIdsByImportId',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getObjIdsForRefIds: Returns a array of object ids which match the references id, given by a comma seperated string. Returns an array of ref ids, in the same order as object ids. Therefore, there might by duplicates');

		$this->server->register('updateGroup',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int', 'xml' => 'xsd:string'),
								array('success' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#updateGroup',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS updateGroup(): update existing group using ref id and group xml (see DTD).');


		
        $this->server->register('getIMSManifestXML',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getIMSManifestXML',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getIMSManifestXML(): returns xml of ims manifest file (scorm learning module) referred by refid');


		$this->server->register('copyObject',
								array('sid' => 'xsd:string', 'xml' => 'xsd:string'),
								array('xml' => 'xsd:int'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#copyObject',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS copyObject(): returns reference of copy, if copy is created directly, or the ref id of the target if copy is in progress.');
		
		$this->server->register('moveObject',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int', 'target_id' => 'xsd:int'),
								array('result' => 'xsd:boolean'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#moveObject',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS moveObject(): returns true, if object with refid could be successfully moved to target id, other it raises an error.');
								
		
		$this->server->register ('getTestResults',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getTestResults',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getTestResults(): returns XMLResultSet with columns firstname, lastname, matriculation, maximum points, received points');
								

		$this->server->register ('getCoursesForUser',
								array('sid' => 'xsd:string', 'parameters' => 'xsd:string'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getCoursesForUser',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getTestResults(): returns XMLResultSet with columns ref_id, course xml. $parameters has to contain a column user_id and a column status. Status is a logical AND combined value of (MEMBER = 1, TUTOR = 2, ADMIN = 4, OWNER = 8) and determines which courses should be returned.');
								
		$this->server->register ('getGroupsForUser',
								array('sid' => 'xsd:string', 'parameters' => 'xsd:string'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getGroupsForUser',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getTestResults(): returns XMLResultSet with columns ref_id, group xml. $parameters has to contain a column user_id and a column status. Status is a logical AND combined value of (MEMBER = 1, TUTOR = 2, OWNER = 4) and determines which groups should be returned.');
								
		$this->server->register ('getPathForRefId',
								array('sid' => 'xsd:string', 'ref_id' => 'xsd:int'),
								array('xml' => 'xsd:string'),
								SERVICE_NAMESPACE,
								SERVICE_NAMESPACE.'#getPathForRefId',
								SERVICE_STYLE,
								SERVICE_USE,
								'ILIAS getTestResults(): returns XMLResultSet with columns ref_id, type and title.');
								
								
		return true;

	}

}
?>