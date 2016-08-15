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
* ilHttpRequest class
*
* Class to retrieve an HTTP request
*
* @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
*/
	
class ilHttpRequest
{
	var $_fp;        // HTTP socket
	var $_url;        // full URL
	var $_host;        // HTTP host
	var $_protocol;    // protocol (HTTP/HTTPS)
	var $_uri;        // request URI
	var $_port;        // port
	
	// scan url
	function _scan_url()
	{
		$req = $this->_url;
		
		$pos = strpos($req, '://');
		$this->_protocol = strtolower(substr($req, 0, $pos));
		
		$req = substr($req, $pos+3);
		$pos = strpos($req, '/');
		if($pos === false) $pos = strlen($req);
		$host = substr($req, 0, $pos);
		
		if(strpos($host, ':') !== false)
		{
			list($this->_host, $this->_port) = explode(':', $host);
		}
		else
		{
			$this->_host = $host;
			$this->_port = ($this->_protocol == 'https') ? 443 : 80;
		}
		
		$this->_uri = substr($req, $pos);
		if($this->_uri == '') $this->_uri = '/';
	}
	
	// constructor
	function __construct($url)
	{
		$this->_url = $url;
		$this->_scan_url();
	}
	
	// download URL to string
	function downloadToString()
	{
		$crlf = "\r\n";
		
		// generate request
		$req = 'GET ' . $this->_uri . ' HTTP/1.0' . $crlf
		. 'Host: ' . $this->_host . $crlf
		.  $crlf;

		// fetch
		$this->_fp = fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port);
		fwrite($this->_fp, $req);
		while(is_resource($this->_fp) && $this->_fp && !feof($this->_fp))
		{
			$response .= fread($this->_fp, 1024);
		}
		fclose($this->_fp);
		
		// split header and body
		$pos = strpos($response, $crlf . $crlf);
		if($pos === false) return($response);
		$header = substr($response, 0, $pos);
		$body = substr($response, $pos + 2 * strlen($crlf));
		
		// parse headers
		$headers = array();
		$lines = explode($crlf, $header);
		foreach($lines as $line)
		{
			if(($pos = strpos($line, ':')) !== false)
			{
				$headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));
			}
		}
		
		// redirection?
		if(isset($headers['location']))
		{
			$http = new ilHttpRequest($headers['location']);
			return($http->DownloadToString($http));
		}
		else
		{
			return($body);
		}
	}
}
?>
