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
 * Class ShibbolethWAYF
 *
 * This class handles the Home Organization selection (also called Where Are You
 * From service) process for Shibboleth users.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup ServicesAuthShibboleth
 */
class ShibWAYF {

	/**
	 * @var bool
	 */
	public $is_selection = false;
	/**
	 * @var bool
	 */
	public $is_valid_selection = false;
	/**
	 * @var string
	 */
	public $selected_idp = '-';
	/**
	 * @var array|bool
	 */
	public $idp_list = false;


	public function __construct() {
		// Was the WAYF form submitted?
		if (isset($_POST['home_organization_selection'])) {
			$this->is_selection = true;
		} else {
			$this->is_selection = false;
		}
		// Was selected IdP a valid
		$this->idp_list = $this->getIdplist();
		if (isset($_POST['idp_selection']) AND
			$_POST['idp_selection'] != '-' AND isset($this->idp_list[$_POST['idp_selection']])
		) {
			$this->is_valid_selection = true;
			$this->selected_idp = $_POST['idp_selection'];
		} else {
			$this->is_valid_selection = false;
		}
	}


	/**
	 * @return bool
	 */
	public function is_selection() {
		return $this->isSelection();
	}


	/**
	 * @return bool
	 */
	public function isSelection() {
		return $this->is_selection;
	}


	/**
	 * @return bool
	 */
	public function is_valid_selection() {
		return $this->isValidSelection();
	}


	/**
	 * @return bool
	 */
	public function isValidSelection() {
		return $this->is_valid_selection;
	}


	/**
	 * @return string
	 */
	public function generateSelection() {
		if (! $this->idp_list) {
			$arr_idp_list = $this->getIdplist();
		} else {
			$arr_idp_list = $this->idp_list;
		}
		$idp_cookie = $this->generateCookieArray($_COOKIE['_saml_idp']);
		$lng = new ilLanguage($_GET["lang"]);
		if (count($idp_cookie) > 0 AND isset($arr_idp_list[end($idp_cookie)])) {
			$selectedIDP = end($idp_cookie);
			$selectElement = '
		<select name="idp_selection">
			<option value="-">' . $lng->txt("shib_member_of") . '</option>';
		} else {
			$selectElement = '
		<select name="idp_selection">
			<option value="-" selected="selected">' . $lng->txt("shib_member_of") . '</option>';
		}
		foreach ($arr_idp_list as $idp_id => $idp_data) {
			if ($idp_id == $selectedIDP) {
				$selectElement .= '<option value="' . $idp_id . '" selected="selected">' . $idp_data[0] . '</option>';
			} else {
				$selectElement .= '<option value="' . $idp_id . '">' . $idp_data[0] . '</option>';
			}
		}
		$selectElement .= '
		</select>';

		return $selectElement;
	}


	/**
	 * @description Redirects user to the local Shibboleth session initatiotor with already set GET arguments for the right IdP and return location.
	 */
	public function redirect() {
		if (! $this->idp_list) {
			$arr_idp_list = $this->getIdplist();
		} else {
			$arr_idp_list = $this->idp_list;
		}
		// Where to return after the authentication process
		$target = trim(ILIAS_HTTP_PATH, '/') . '/shib_login.php?target=' . $_POST["il_target"];
		$idp_data = $arr_idp_list[$this->selected_idp];
		if (isset($idp_data[1])) {
			ilUtil::redirect($idp_data[1] . '?providerId=' . urlencode($this->selected_idp) . '&target='
				. urlencode($target));
		} else {
			// TODO: This has to be changed to /Shibboleth.sso/DS?entityId= for 
			// Shibbolet 2.x sometime...
			ilUtil::redirect('/Shibboleth.sso?providerId=' . urlencode($this->selected_idp) . '&target='
				. urlencode($target));
		}
	}


	/**
	 * @description Sets the standard SAML domain cookie that is also used to preselect the right entry on the local wayf
	 */
	public function setSAMLCookie() {
		if (isset($_COOKIE['_saml_idp'])) {
			$arr_idps = $this->generateCookieArray($_COOKIE['_saml_idp']);
		} else {
			$arr_idps = array();
		}
		$arr_idps = $this->appendCookieValue($this->selected_idp, $arr_idps);
		setcookie('_saml_idp', $this->generateCookieValue($arr_idps), time() + (100 * 24 * 3600), '/');
	}


	/**
	 * @return string
	 * @description Show notice in case no IdP was selected
	 */
	public function showNotice() {
		$lng = new ilLanguage($_GET["lang"]);
		if (! $this->is_selection() or $this->is_valid_selection()) {
			return '';
		} else {
			return $lng->txt("shib_invalid_home_organization");
		}
	}


	/**
	 * @return array
	 * @description Generate array of IdPs from ILIAS Shibboleth settings
	 */
	public function getIdplist() {
		global $ilSetting;
		$idp_list = array();
		$idp_raw_list = split("\n", $ilSetting->get("shib_idp_list"));
		foreach ($idp_raw_list as $idp_line) {
			$idp_data = split(',', $idp_line);
			if (isset($idp_data[2])) {
				$idp_list[trim($idp_data[0])] = array( trim($idp_data[1]), trim($idp_data[2]) );
			} elseif (isset($idp_data[1])) {
				$idp_list[trim($idp_data[0])] = array( trim($idp_data[1]) );
			}
		}

		return $idp_list;
	}


	/**
	 * @param $value
	 *
	 * @description Generates an array of IDPs using the cookie value
	 *
	 * @return array
	 */
	public function generateCookieArray($value) {
		$arr_cookie = explode(' ', $value);
		$arr_cookie = array_map('base64_decode', $arr_cookie);

		return $arr_cookie;
	}


	/**
	 * @param $arr_cookie
	 *
	 * @description Generate the value that is stored in the cookie using the list of IDPs
	 *
	 * @return string
	 */
	public function generateCookieValue(array $arr_cookie) {
		$arr_cookie = array_map('base64_encode', $arr_cookie);
		$value = implode(' ', $arr_cookie);

		return $value;
	}


	/**
	 * @param $value
	 * @param $arr_cookie
	 *
	 * @description Append a value to the array of IDPs
	 *
	 * @return array
	 */
	public function appendCookieValue($value, array $arr_cookie) {
		array_push($arr_cookie, $value);
		$arr_cookie = array_reverse($arr_cookie);
		$arr_cookie = array_unique($arr_cookie);
		$arr_cookie = array_reverse($arr_cookie);

		return $arr_cookie;
	}
}

?>
