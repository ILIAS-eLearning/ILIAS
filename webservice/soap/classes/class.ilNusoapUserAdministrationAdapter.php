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


/**
* adapter class for nusoap server
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

include_once './webservice/soap/lib/nusoap.php';
include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

class ilNusoapUserAdministrationAdapter
{
	/*
	 * @var object Nusoap-Server
	 */
	var $server = null;

    
    function ilNusoapUserAdministrationAdapter($a_use_wsdl = true)
    {
		define('SERVICE_NAME','ilUserAdministration');
		define('SERVICE_NAMESPACE','urn:ilUserAdministration');
		define('SERVICE_STYLE','rpc');
		define('SERVICE_USE','encoded');

		$this->server =& new soap_server();

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
												  'approve_date' => array('name' => 'approve_date', 'type' => 'xsd:string'),
												  'user_skin' => array('name' => 'user_skin', 'type' => 'xsd:string'),
												  'user_style' => array('name' => 'user_style', 'type' => 'xsd:string'),
												  'user_language' => array('name' => 'user_languaage', 'type' => 'xsd:string')));

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
								'ILIAS updateUser(). Updates all user data. '.
								'Use getUser(), then modify desired fields and finally start the updateUser() call.');

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
								'ILIAS addUser() user. Add new ILIAS user. Requires complete or subset of user_data structure');

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
		return true;
	}
		
}
?>