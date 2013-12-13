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
 * @author Stefan Meyer <meyer@leifos.com>
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

	private $header_plain = '';
	private $header_arr = array();

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
	 * Get response header as string
	 * @return string
	 */
	public function getResponseHeader()
	{
		return $this->header_plain;
	}

	/**
	 * Get response header as array
	 * @return array
	 */
	public function getResponseHeaderArray()
	{
		return (array) $this->header_arr;
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
			#$GLOBALS['ilLog']->write(__METHOD__ . ': ' . $this->url);
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
			throw new ilCurlConnectionException(curl_error($this->ch), curl_errno($this->ch));
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
	public final function setOpt($a_option, $a_value)
	{
		if(!@curl_setopt($this->ch, $a_option, $a_value))
		{
			throw new ilCurlConnectionException('Invalid option given for: ' . $a_option, curl_errno($this->ch));
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
		// Add header function 
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this,'parseHeader'));
		if((@$res = curl_exec($this->ch)) === false)
		{
			if(strlen($err = curl_error($this->ch)))
			{
				throw new ilCurlConnectionException($err, curl_errno($this->ch));
			}
			else
			{
				throw new ilCurlConnectionException('Error calling curl_exec().');
			}
		}
		return $res;
	}

	/**
	 * Get informations about a specific transfer
	 *
	 * @access public
	 * @param int option e.g CURLINFO_EFFECTIVE_URL
	 * @return mixed 
	 * 
	 */
	public function getInfo($opt = 0)
	{
		if($opt)
		{
			$res = curl_getinfo($this->ch, $opt);
		}
		else
		{
			$res = curl_getinfo($this->ch);
		}
		return $res;
	}

	/**
	 * Parse respone header
	 * @param mixed $handle
	 * @param string $header
	 * @return int strlen of header
	 */
	private function parseHeader($handle,$header)
	{
		$this->header_plain = $header;

		$lines = explode('\r\n',$this->getResponseHeader());
		foreach($lines as $line)
		{
			list($name,$value) = explode(':',$line,2);
			$this->header_arr[$name] = $value;
		}
		return strlen($this->getResponseHeader());
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