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

define ('APACHE_AUTH_TYPE_DIRECT_MAPPING', 1);
define ('APACHE_AUTH_TYPE_EXTENDED_MAPPING', 2);
define ('APACHE_AUTH_TYPE_BY_FUNCTION', 3);

/**
 * @classDescription Apache based authentication
 * @author Jan Posselt <jposselt@databay.de>
 * @version $id$
 * 
 *  @ingroup ServicesAuthentication
 */
class ilAuthApache extends Auth
{
	private $apache_settings;
	
	/**
	 * Returns true, if the current auth mode allows redirection to e.g 
	 * to loginScreen, public section... 
	 * @return 
	 */
	public function supportsRedirects()
	{
		return true;
	} 

	/**
	 * Contructor
	 * @return 
	 * @param object $a_container
	 * @param object $a_addition_options[optional]
	 */
	public function __construct($a_container,$a_addition_options = array())
	{
		global $lng;
		
		parent::__construct($a_container,$a_addition_options,'',false);
		$this->setSessionName("_authhttp".md5(CLIENT_ID));

		$this->apache_settings = new ilSetting('apache_auth');
		
		if (defined('IL_CERT_SSO') && IL_CERT_SSO) {
			// DO NOT DELETE!!!
			// faking post values is REQUIRED to avoid canceling of the
			// startup routines
                        // $_POST['username'] = 'xxx';
                        $_POST['password'] = 'yyy';
                        $_POST['sendLogin'] = '1';
                        $_POST['auth_mode'] = AUTH_APACHE;
                        $_POST['cmd[butSubmit]'] = 'Submit';

                        if ($_POST['username'] != 'anonymous') {

                                switch($this->apache_settings->get('apache_auth_username_config_type'))
                                {
                                        case APACHE_AUTH_TYPE_DIRECT_MAPPING:
                                                $_POST['username'] = $_SERVER[$this->apache_settings->get('apache_auth_username_direct_mapping_fieldname')];
                                                break;
                                        case APACHE_AUTH_TYPE_EXTENDED_MAPPING:
                                                throw new ilException("APACHE_AUTH_TYPE_EXTENDED_MAPPING not yet implemented");
                                        case APACHE_AUTH_TYPE_BY_FUNCTION:
                                                include_once 'Services/AuthApache/classes/custom_username_func.php';
                                                $_POST['username'] = ApacheCustom::getUsername();
                                                break;
                                }

                        }
		}

		if (defined('IL_CERT_SSO') && IL_CERT_SSO && !$_POST['username']) {
			$_POST['username'] = '§invalid';
			$_POST['password'] = 'anonymous';
			$_SESSION['username_invalid'] = true;
		}
			
		$this->initAuth();
	}
	
	
	public function login() {
		$skipClasses = array('ilpasswordassistancegui', 'ilaccountregistrationgui');
		$skipFiles   = array('pwassist.php');
		if(in_array(strtolower($_REQUEST['cmdClass']), $skipClasses))
		{
			return;
		}
		else
		{
			$script = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
			if(in_array(strtolower($script), $skipFiles))
				return;
		}
		if(
			!$this->apache_settings->get('apache_auth_authenticate_on_login_page') &&
			(
				preg_match('/.*login\.php$/', $_SERVER['SCRIPT_NAME']) ||
				((in_array($_REQUEST['cmd'], array('showLogin', 'showTermsOfService')) || isset($_POST['change_lang_to'])) && strtolower($_REQUEST['cmdClass']) == 'ilstartupgui')
			)
		)
		{
			return;
		}

		if(!$this->apache_settings->get('apache_auth_authenticate_on_login_page') && preg_match('/.*login\.php$/', $_SERVER['SCRIPT_NAME']))
		{
			return;
		}
		
		if (ilContext::supportsRedirects() && !isset($_GET['passed_sso']) && (!defined('IL_CERT_SSO') || IL_CERT_SSO == false)) {

			// redirect to sso			
			// this part is executed in default ilias context...

			$path = $_SERVER['REQUEST_URI'];

                        if ($path{0} == '/') {
				$path = substr($path, 1);
                        }

                        if (substr($path, 0, 4) != 'http') {
                                $parts = parse_url(ILIAS_HTTP_PATH);
                                $path = $parts['scheme'] . '://' . $parts['host'] . '/' . $path;
                        }

			$path = urlencode($path);
			ilUtil::redirect(ilUtil::getHtmlPath('/sso/index.php?force_mode_apache=1&r=' . $path . '&cookie_path='.IL_COOKIE_PATH . '&ilias_path=' . ILIAS_HTTP_PATH));
		}
		else {
			return parent::login();
		}
	}
}