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
* Singleton class that stores all security settings
*
* @author Roland Küstermann <roland@kuestermann.com>
* @version $Id$
*
*
* @ingroup Services/PrivacySecurity
*/

class ilSecuritySettings
{
    public static $SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS = 1;
    public static $SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE = 2;
    public static $SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE = 3;

    private static $instance = null;
	private $db;
	private $settings;

	private $https_header_enable;
	private $https_header_name;
	private $https_header_value;
	private $https_enable;

	/**
	 * Private constructor: use _getInstance()
	 *
	 * @access private
	 * @param
	 *
	 */
	private function __construct()
	{
		global $ilSetting,$ilDB;

		$this->db = $ilDB;
		$this->settings = $ilSetting;

	 	$this->read();
	}

	/**
	 * Get instance of ilSecuritySettings
	 *
	 * @return ilSecuritySettings  instance
	 * @access public
	 *
	 */
	public function _getInstance()
	{
		if(is_object(self::$instance))
		{
			return self::$instance;
		}
	 	return self::$instance = new ilSecuritySettings();
	}

	public function getSecuritySettingsRefId()
	{
		return $this->ref_id;
	}

	/**
	 * write access to enable automatic https detection
	 *
	 * @param boolean $varname
	 *
	 */
	public function setAutomaticHTTPSEnabled($varname)
	{
	    $this->https_header_enable = $varname;
	}


	/**
	 * set header name for automatic https detection
	 *
	 * @param string $varname
	 */
	public function setAutomaticHTTPSHeaderName($varname)
	{
	    $this->https_header_name = $varname;
	}

	/**
	 * set header value for automatic https detection
	 *
	 * @param string $varname
	 */
	public function setAutomaticHTTPSHeaderValue($varname)
	{
	    $this->https_header_value = $varname;
	}

	/**
	 * read access to header name for automatic https detection
	 *
	 * @return string  header name
	 */
	public function getAutomaticHTTPSHeaderName()
	{
	    return $this->https_header_name;
	}

	/**
	 * read access to header value for automatic https detection
	 *
	 * @return string header value
	 */
	public function getAutomaticHTTPSHeaderValue()
	{
	    return $this->https_header_value;
	}

    /**
     * read access to switch if automatic https detection is enabled
     *
     * @return boolean  true, if detection is enabled, false otherwise
     */
	public function isAutomaticHTTPSEnabled()
	{
	    return $this->https_header_enable;
	}

	/**
	 * Enable https for certain scripts
	 *
	 * @param boolean $value
	 */
    public function setHTTPSEnabled ($value)
    {
        $this->https_enable = $value;
    }


    /**
     * read access to https enabled property
     *
     * @return boolean  true, if enabled, false otherwise
     */
    public function isHTTPSEnabled ()
    {
        return $this->https_enable;
    }
	/**
	 * Save settings
	 *
	 *
	 */
	public function save()
	{
	 	$this->settings->set('ps_auto_https_enabled',(bool) $this->isAutomaticHTTPSEnabled());
	 	$this->settings->set('ps_auto_https_headername',(string) $this->getAutomaticHTTPSHeaderName());
	 	$this->settings->set('ps_auto_https_headervalue',(string) $this->getAutomaticHTTPSHeaderValue());
	 	$this->settings->set('https',(string) $this->isHTTPSEnabled());
	}
	/**
	 * read settings
	 *
	 * @access private
	 * @param
	 *
	 */
	private function read()
	{
		global $ilDB;
		
	    $query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data ".
				"WHERE tree.parent = ".$ilDB->quote(SYSTEM_FOLDER_ID)." ".
				"AND object_data.type = 'ps' ".
				"AND object_reference.ref_id = tree.child ".
				"AND object_reference.obj_id = object_data.obj_id";
		$res = $this->db->query($query);
		$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
		$this->ref_id = $row["ref_id"];

    	$this->https_header_enable = (bool) $this->settings->get('ps_auto_https_enabled',false);
		$this->https_header_name = (string) $this->settings->get('ps_auto_https_headername',"ILIAS_HTTPS_ENABLED");
		$this->https_header_value = (string) $this->settings->get('ps_auto_https_headervalue',"1");
		$this->https_enable = (boolean) $this->settings->get('https', false);
	}

	/**
	 * validate settings
	 *
	 * @return 0, if everything is ok, an error code otherwise
	 */
	public function validate() {
	    if ($this->isAutomaticHTTPSEnabled() &&
	        (strlen($this->getAutomaticHTTPSHeaderName()) == 0 ||
	         strlen($this->getAutomaticHTTPSHeaderValue()) == 0)
	        )
        {
	        return ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS;
	    }
        include_once './classes/class.ilHTTPS.php';

	    if ($this->isHTTPSEnabled())
	    {
			if(!ilHTTPS::_checkHTTPS())
			{
				return ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE;
			}
	    } elseif(!ilHTTPS::_checkHTTP())
			{
			    return ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE;
			}

	    return 0;
	}


}
?>