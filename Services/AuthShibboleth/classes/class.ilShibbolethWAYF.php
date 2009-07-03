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
* @ingroup ServicesAuthShibboleth
*/
class ShibWAYF
{
	
	var $isSelection = false;
	var $isValidSelection = false;
	var $selectedIDP = '-';
	var $IDPList = false;
	
	function ShibWAYF()
	{
		// Was the WAYF form submitted?
		if (isset($_POST['home_organization_selection']))
		{
			$this->isSelection = true;
		}
		else
		{
			$this->isSelection = false;
		}
		
		// Was selected IdP a valid
		$this->IDPList = $this->getIDPList();
		if (
			isset($_POST['idp_selection'])
			&& $_POST['idp_selection'] != '-'
			&& isset($this->IDPList[$_POST['idp_selection']])
			)
		{
			$this->isValidSelection = true;
			$this->selectedIDP = $_POST['idp_selection'];
		}
		else
		{
			$this->isValidSelection = false;
		}
	}
	
	// Return true if WAYF form was used
	function isSelection()
	{
		
		return $this->isSelection;
	}
	
	// Return true if a valid IdP was submitted
	function isValidSelection()
	{
		
		return $this->isValidSelection;
		
	}
	
	// Generate select element displayed on login page
	function generateSelection()
	{
		global $ilSetting;
		
		// Calculate select element
		
		$selectElement = '';
		
		if (!$this->IDPList)
		{
			$idp_list = $this->getIDPList();
		}
		else
		{
			$idp_list = $this->IDPList;
		}
		
		$idp_cookie = $this->generateCookieArray($_COOKIE['_saml_idp']);
		
		$lng = new ilLanguage($_GET["lang"]);
		
		if (count($idp_cookie) > 0 && isset($idp_list[end($idp_cookie)]))
		{
			$selectedIDP = end($idp_cookie);
			$selectElement = '
		<select name="idp_selection">
			<option value="-">'.$lng->txt("shib_member_of").'</option>';
		}
		else
		{
			$selectElement = '
		<select name="idp_selection">
			<option value="-" selected="selected">'.$lng->txt("shib_member_of").'</option>';
		}
		
		foreach ($idp_list as $idp_id => $idp_data){
			
			if ($idp_id == $selectedIDP)
			{
				$selectElement .= '<option value="'.$idp_id.'" selected="selected">'.$idp_data[0].'</option>';
			}
			else
			{
				$selectElement .= '<option value="'.$idp_id.'">'.$idp_data[0].'</option>';
			}
		}
		
		$selectElement .= '
		</select>';
		
		return $selectElement;
	}
	
	// Redirects user to the local Shibboleth session initatiotor with
	// already set GET arguments for the right IdP and return location.
	function redirect()
	{
		if (!$this->IDPList)
		{
			$idp_list = $this->getIDPList();
		}
		else
		{
			$idp_list = $this->IDPList;
		}
		
		// Where to return after the authentication process
		$target = trim(ILIAS_HTTP_PATH, '/').'/shib_login.php?target='.$_POST["il_target"];
		
		$idp_data = $idp_list[$this->selectedIDP];
		if (isset($idp_data[1]))
		{
			ilUtil::redirect($idp_data[1].'?providerId='.urlencode($this->selectedIDP).'&target='.urlencode($target));
		}
		else
		{
			// TODO: This has to be changed to /Shibboleth.sso/DS?entityId= for 
			// Shibbolet 2.x sometime...
			ilUtil::redirect('/Shibboleth.sso?providerId='.urlencode($this->selectedIDP).'&target='.urlencode($target));
		}
		
	}
	
	// Sets the standard SAML domain cookie that is also used to preselect
	// the right entry on the local wayf
	function setSAMLCookie()
	{
		if (isset($_COOKIE['_saml_idp']))
		{
			$IDPArray = $this->generateCookieArray($_COOKIE['_saml_idp']);
		}
		else
		{
			$IDPArray = array();
		}
		$IDPArray = $this->appendCookieValue($this->selectedIDP, $IDPArray);
		setcookie ('_saml_idp', $this->generateCookieValue($IDPArray), time() + (100*24*3600), '/');
	}
	
	// Show notice in case no IdP was selected
	function showNotice()
	{
		$lng = new ilLanguage($_GET["lang"]);
		
		if (!$this->isSelection() or $this->isValidSelection())
		{
			return '';
		}
		else
		{
			return $lng->txt("shib_invalid_home_organization");
		}
	}
	
	// Generate array of IdPs from ILIAS Shibboleth settings
	function getIDPList()
	{
		global $ilSetting;
		
		$idp_list = array();
		
		$idp_raw_list = split("\n", $ilSetting->get("shib_idp_list"));
		
		foreach ($idp_raw_list as $idp_line){
			$idp_data = split(',', $idp_line);
			if (isset($idp_data[2]))
			{
				$idp_list[trim($idp_data[0])] = array(trim($idp_data[1]),trim($idp_data[2])); 
			}
			elseif(isset($idp_data[1]))
			{
				$idp_list[trim($idp_data[0])] = array(trim($idp_data[1]));
			}
		}
		
		return $idp_list;
		print_r($idp_list);exit;
	}
	
	// Generates an array of IDPs using the cookie value
	function generateCookieArray($value)
	{
		
		// Decodes and splits cookie value
		$CookieArray = split(' ', $value);
		$CookieArray = array_map('base64_decode', $CookieArray);
		
		return $CookieArray;
	}
	
	// Generate the value that is stored in the cookie using the list of IDPs
	function generateCookieValue($CookieArray)
	{
	
		// Merges cookie content and encodes it
		$CookieArray = array_map('base64_encode', $CookieArray);
		$value = implode(' ', $CookieArray);
		return $value;
	}
	
	// Append a value to the array of IDPs
	function appendCookieValue($value, $CookieArray)
	{
		
		array_push($CookieArray, $value);
		$CookieArray = array_reverse($CookieArray);
		$CookieArray = array_unique($CookieArray);
		$CookieArray = array_reverse($CookieArray);
		
		return $CookieArray;
	}
	
}
?>
