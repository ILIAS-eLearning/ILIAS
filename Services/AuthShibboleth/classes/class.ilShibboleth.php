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

require_once('Auth/Auth.php');
require_once('./Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php');
require_once('include/Unicode/UtfNormal.php');
require_once('./Services/AuthShibboleth/classes/class.ilShibbolethPluginWrapper.php');
require_once('./Services/AuthShibboleth/classes/Config/class.shibConfig.php');
require_once('./Services/AuthShibboleth/classes/ServerData/class.shibServerData.php');
require_once('./Services/AuthShibboleth/classes/User/class.shibUser.php');

/**
 * Class Shibboleth
 *
 * This class provides basic functionality for Shibboleth authentication
 *
 * @author   Fabian Schmid <fs@studer-raimann.ch>
 *
 * @defgroup ServicesAuthShibboleth Services/AuthShibboleth
 * @ingroup  ServicesAuthShibboleth
 */
class ShibAuth extends Auth {

//	/**
//	 * @param      $authParams
//	 * @param bool $updateUserData
//	 */
//	public function __construct($authParams, $updateUserData = false) {
////		if ($authParams['sessionName'] != '') {
////			parent::Auth('', array( 'sessionName' => $authParams['sessionName'] ));
////		} else {
////			parent::Auth('');
////		}
//
//		parent::__construct($authParams, $updateUserData);
//		$this->updateUserData = $updateUserData;
//		if (! empty($authParams['sessionName'])) {
//			$this->setSessionName($authParams['sessionName']);
//			unset($authParams['sessionName']);
//		}
//	}


	/**
	 * @return bool
	 */
	public function supportsRedirects() {
		return true;
	}


	/**
	 * Login function
	 *
	 * @access private
	 * @return void
	 */
	public function login() {
		global $DIC; // for backword compatibility of hook environment variables
		$ilias = $DIC['ilias'];
		$ilSetting = $DIC['ilSetting'];
		$shibServerData = shibServerData::getInstance();
		if ($shibServerData->getLogin()) {
			$shibUser = shibUser::buildInstance($shibServerData);
			// for backword compatibility of hook environment variables
			$userObj =& $shibUser; // For shib_data_conv included Script
			$newUser = $shibUser->isNew(); // For shib_data_conv included Script
			if ($shibUser->isNew()) {
				$shibUser->createFields();
				$shibUser->setPref('hits_per_page', $ilSetting->get('hits_per_page'));

				// Modify user data before creating the user
				// Include custom code that can be used to further modify
				// certain Shibboleth user attributes
				if ($ilias->getSetting('shib_data_conv') AND $ilias->getSetting('shib_data_conv') != ''
					AND is_readable($ilias->getSetting('shib_data_conv'))
				) {
					include($ilias->getSetting('shib_data_conv'));
				}
				$shibUser = ilShibbolethPluginWrapper::getInstance()->beforeCreateUser($shibUser);
				$shibUser->create();
				$shibUser->updateOwner();
				$shibUser->saveAsNew();
				$shibUser->writePrefs();
				$shibUser = ilShibbolethPluginWrapper::getInstance()->afterCreateUser($shibUser);
				ilShibbolethRoleAssignmentRules::doAssignments($shibUser->getId(), $_SERVER);
			} else {
				$shibUser->updateFields();
				// Include custom code that can be used to further modify
				// certain Shibboleth user attributes
				if ($ilias->getSetting('shib_data_conv') AND $ilias->getSetting('shib_data_conv') != ''
					AND is_readable($ilias->getSetting('shib_data_conv'))
				) {
					include($ilias->getSetting('shib_data_conv'));
				}
				//				$shibUser->update();
				$shibUser = ilShibbolethPluginWrapper::getInstance()->beforeUpdateUser($shibUser);
				$shibUser->update();
				$shibUser = ilShibbolethPluginWrapper::getInstance()->afterUpdateUser($shibUser);
				ilShibbolethRoleAssignmentRules::updateAssignments($shibUser->getId(), $_SERVER);
			}
			$this->setAuth($shibUser->getLogin(), $shibUser);
			ilObjUser::_updateLastLogin($shibUser->getId());
			if ($_GET['target'] != '') {
				ilUtil::redirect('goto.php?target=' . $_GET['target'] . '&client_id=' . CLIENT_ID);
			}
		} else {
			$this->status = AUTH_WRONG_LOGIN;
		}
	}


	/**
	 * @param           $username
	 * @param ilObjUser $userObj
	 */
	public function setAuth($username, ilObjUser $userObj = NULL) {
		if ($userObj) {
			ilShibbolethPluginWrapper::getInstance()->beforeLogin($userObj);
		}
		parent::setAuth($username);
		if ($userObj) {
			ilShibbolethPluginWrapper::getInstance()->afterLogin($userObj);
		}
	}


	public function logout() {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		ilShibbolethPluginWrapper::getInstance()->beforeLogout($ilUser);
		parent::logout();
		ilShibbolethPluginWrapper::getInstance()->afterLogout($ilUser);
	}
}
