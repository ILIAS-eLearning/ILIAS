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
* HTTPS
*
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
*/

class ilHTTPS
{
	var $enabled = false;
	var $protected_scripts = array();

	var $automaticHTTPSDetectionEnabled = false;
	var $headerName = false;
	var $headerValue = false;

	function ilHTTPS()
	{
		global $ilSetting;

		if($this->enabled = (bool) $ilSetting->get('https'))
		{
			$this->__readProtectedScripts();
			$this->__readProtectedClasses();
		}
		if ($this->automaticHTTPSDetectionEnabled = (bool) $ilSetting->get("ps_auto_https_enabled"))
		{
		    $this->headerName = $ilSetting->get("ps_auto_https_headername");
		    $this->headerValue = $ilSetting->get("ps_auto_https_headervalue");
		}
	}

	/**
	 * check if current port usage is right: if https should be used than redirection is done, to http otherwise.
	 *
	 * @return unknown
	 */
	function checkPort()
	{
		// if https is enabled for scripts or classes, check for redirection
	    if ($this->enabled)
		{
    		if((in_array(basename($_SERVER["SCRIPT_NAME"]),$this->protected_scripts) or
    			in_array($_GET['cmdClass'],$this->protected_classes)) and
    		   $_SERVER["HTTPS"] != "on")
    		{
    			header("location: https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
    			exit;
    		}
    		if((!in_array(basename($_SERVER["SCRIPT_NAME"]),$this->protected_scripts) and
    			!in_array($_GET['cmdClass'],$this->protected_classes)) and
    		   $_SERVER["HTTPS"] == "on")
    		{
    			header("location: http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
    			exit;
    		}
		}
		return true;
	}

	function __readProtectedScripts()
	{
		$this->protected_scripts[] = 'login.php';
		$this->protected_scripts[] = 'index.php';
		$this->protected_scripts[] = 'payment.php';
		$this->protected_scripts[] = 'register.php';
		// BEGIN WebDAV Use SSL for WebDAV.
		$this->protected_scripts[] = 'webdav.php';
		// END WebDAV Use SSL for WebDAV.

		return true;
	}

	/**
	 * check if https is detected
	 *
	 * @return boolean true, if https is detected by protocol or by automatic detection, if enabled, false otherwise
	 */
	public function isDetected () 
	{
   		if ($_SERVER["HTTPS"] == "on")
   		   return true;

	    if ($this->automaticHTTPSDetectionEnabled)
		{
		    $headerName = "HTTP_".str_replace("-","_",$this->headerName);
		   /* echo $headerName;
		    echo $_SERVER[$headerName];*/
		    if (strcasecmp($_SERVER[$headerName],$this->headerValue)==0) 
		    {
           		$_SERVER["HTTPS"] = "on";
		    	return true;
		    }
		    /*
		    if(isset($_SERVER[$this->headerName]) && (strcasecmp($_SERVER[$this->headerName],$this->headerValue) == 0))
		    {
		    	$_SERVER['HTTPS'] = 'on';
		    	return true;
		    }
		    */
		}

        return false;
	}

	function __readProtectedClasses()
	{
		$this->protected_classes[] = 'ilstartupgui';
		$this->protected_classes[] = 'ilaccountregistrationgui';
	}

	/**
	* static method to check if https connections are possible for this server
	* @access	public
	* @return	boolean
	*/
	function _checkHTTPS()
	{
		// only check standard port in the moment
		$port = 443;

		if(($sp = @fsockopen($_SERVER["SERVER_NAME"],$port,$errno,$error)) === false)
		{
			return false;
		}
		fclose($sp);
		return true;
	}
	/**
	* static method to check if http connections are possible for this server
	*
	* @access	public
	* @return	boolean
	*/
	function _checkHTTP()
	{
		$port = 80;

		if(($sp = @fsockopen($_SERVER["SERVER_NAME"],$port,$errno,$error)) === false)
		{
			return false;
		}
		fclose($sp);
		return true;
	}
	
	/**
	 * enable secure cookies
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function enableSecureCookies()
	{
		global $ilLog,$ilClientIniFile;
		
		$secure_disabled = $ilClientIniFile->readVariable('session','disable_secure_cookies');
		if(!$secure_disabled and !$this->enabled and $this->isDetected() and !session_id())
		{
			$ilLog->write(__CLASS__.': Enabled secure cookies');
			session_set_cookie_params(0,'/','',true);
		}
		return true;
	}
}
?>