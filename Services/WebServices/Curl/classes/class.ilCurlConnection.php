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
* Wrapper for php's curl functions
*  
* @defgroup ServicesWebServicesCurl Services/WebServices/Curl
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesCurl
*/

include_once('Services/WebServices/Curl/classes/class.ilCurlConnectionException.php');

class ilCurlConnection
{
	protected $url = '';
	protected $ch = null;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string url for connection
	 * @throws ilCurlConnectionException
	 * 
	 */
	public function __construct($a_url = '')
	{
		$this->url = $a_url;
		
		if(!self::_isCurlExtensionLoaded())
		{
			throw new ilCurlConnectionException('Curl extension not enabled.');
		}
	}
	
	/**
	 * Check if curl extension is loaded
	 *
	 * @access public
	 * @static
	 *
	 */
	public static final function _isCurlExtensionLoaded()
	{
		if(!function_exists('curl_init'))
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Init curl connection
	 *
	 * @access public
	 * @throws ilCurlConnectionException on error
	 * 
	 */
	public final function init()
	{
	 	if(strlen($this->url))
	 	{
	 		$this->ch = curl_init($this->url);
	 	}
	 	else
	 	{
	 		$this->ch = curl_init();
	 	}
	 	if(!$this->ch)
	 	{
	 		throw new ilCurlConnectionException('Cannot init curl connection.');
	 	}
	 	if(curl_errno($this->ch))
	 	{
	 		throw new ilCurlConnectionException(curl_error($this->ch),curl_errno($this->ch));
	 	}
		
	 	return true;
	}
	
	/**
	 * Wrapper for curl_setopt
	 *
	 * @access public
	 * @param int CURL_OPTION
	 * @param mixed bool string or resource
	 * @throws ilCurlConnectionException on error
	 * 
	 */
	public final function setOpt($a_option,$a_value)
	{
	 	if(!@curl_setopt($this->ch,$a_option,$a_value))
	 	{
	 		throw new ilCurlConnectionException('Invalid option given for: '.$a_option,curl_errno($this->ch));
	 	}
	 	return true;
	}
	
	/**
	 * Wrapper for curl_exec
	 *
	 * @access public
	 * @param
	 * 
	 */
	public final function exec()
	{
	 	if(@$res = curl_exec($this->ch) === false)
	 	{
			if(strlen($err = curl_error($this->ch)))
			{
	 			throw new ilCurlConnectionException($err,curl_errno($this->ch));
			}
			else
			{
				throw new ilCurlConnectionException('Error calling curl_exec().');
			}
	 	}
		return $res;
	}
	
	/**
	 * Close connection
	 *
	 * @access public
	 * 
	 */
	public final function close()
	{
	 	if($this->ch != null)
	 	{
	 		curl_close($this->ch);
	 	}
	}
	
	/**
	 * Destructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __destruct()
	{
	 	$this->close();
	}
}

?>