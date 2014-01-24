<?php
// BEGIN WebDAV
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

require_once "Services/WebDAV/classes/Server.php";
require_once "Services/WebDAV/classes/class.ilDAVLocks.php";
require_once "Services/WebDAV/classes/class.ilDAVProperties.php";
require_once 'Services/WebDAV/classes/class.ilObjectDAV.php';

require_once "Services/User/classes/class.ilObjUser.php";
require_once('include/Unicode/UtfNormal.php');
require_once('Services/Tracking/classes/class.ilChangeEvent.php');

/**
* Class ilDAVServer
*
* Provides access to objects in the repository of ILIAS by means of the WebDAV protocol.
* This class is never directly invoked from HTTP. It is always invoked by the
* script /ilias3/webdav.php.
*
* FIXME - We aren't able to handle filenames that contain a slash / character.
*
*
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilDAVServer extends HTTP_WebDAV_Server
{
	/**
	 * Singleton instance
	 * @var
	 */
	private static $instance = null;
	
	/**
	* Cached object handler.
	* This is a private variable of function getObject.
	*/
	private $cachedObjectDAV;

	/**
	* Handler for locks.
	*/
	private $locks;
	/**
	* Handler for properties.
	*/
	private $properties;

	/**
	 * The operating system of the WebDAV client.
	 * This is 'windows', 'unix' or 'unknown'.
	 * (Mac OS X considered as 'unix'.).
	 */
	private $clientOS = 'unknown';
	/**
	 * The flavor of the operating system of the WebDAV client.
	 * This is 'xp', 'osx', or 'unknown'.
	 */
	private $clientOSFlavor = 'unknown';
	/**
	 * The name of some well known browsers, that need special support.
	 * This is either "konqueror", or unknown.
	 */
	private $clientBrowser = 'unknown';

	/**
	 * This variable holds the DAV object of which the output stream was
	 * returned by method PUT.
	 *
	 * This variable is written inside of method PUT, and then reused
	 * in method PUTfinished, which updates the file size of the DAV object,
	 * after the data has been fully written into the output stream.
	 *
	 * @var ilObjectDav
	 */
	private $putObjDAV = null;

	/**
	 * Holds a boolean after it has been lazily created by method
	 * isWebDAVoverHTTPS().
	 *
	 * @var ilHTTPS object.
	 */
	private $isHTTPS = null;

	/**
	 * The WebDAVServer prints lots of log messages to the ilias log, if this
	 * variable is set to true.
	 */
	private $isDebug = false;

	/**
	* Constructor
	*
	* @param void
	* @deprecated should be used only as private constructor
	* 	to avoid one instance for every item on the personal dektop or in the repository
	* 	Use <code>ilDAVServer::getInstance()</code>
	* 
	*/
	public function ilDAVServer()
	{
		$this->writelog("<constructor>");

		// Initialize the WebDAV server and create
		// locking and property support objects
		$this->HTTP_WebDAV_Server();
		$this->locks = new ilDAVLocks();
		$this->properties = new ilDAVProperties();
		//$this->locks->createTable();
		//$this->properties->createTable();

		// Guess operating system, operating system flavor and browser of the webdav client
		//
		// - We need to know the operating system in order to properly
		// hide hidden resources in directory listings.
		//
		// - We need the operating system flavor and the browser to
		// properly support mounting of a webdav folder.
		//
		$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$this->writelog('userAgent='.$userAgent);
		if (strpos($userAgent,'windows') !== false
		|| strpos($userAgent,'microsoft') !== false)
		{
			$this->clientOS = 'windows';
                        if(strpos($userAgent,'nt 5.1') !== false){
                            $this->clientOSFlavor = 'xp';
                        }else{
                            $this->clientOSFlavor = 'nichtxp';
                        }

		} else if (strpos($userAgent,'darwin') !== false
                || strpos($userAgent,'macintosh') !== false
		|| strpos($userAgent,'linux') !== false
		|| strpos($userAgent,'solaris') !== false
		|| strpos($userAgent,'aix') !== false
		|| strpos($userAgent,'unix') !== false
		|| strpos($userAgent,'gvfs') !== false // nautilus browser uses this ID
		)
		{
			$this->clientOS = 'unix';
			if (strpos($userAgent,'linux') !== false)
			{
				$this->clientOSFlavor = 'linux';
			}
			else if (strpos($userAgent,'macintosh') !== false)
			{
				$this->clientOSFlavor = 'osx';
			}
		}
		if (strpos($userAgent,'konqueror') !== false)
		{
			$this->clientBrowser = 'konqueror';
		}
	}
	
	/**
	 * Get singelton iunstance
	 * @return 
	 */
	public static function getInstance()
	{
		if(self::$instance != NULL)
		{
			return self::$instance;
		}
		return self::$instance = new ilDAVServer();
	}

	/**
	 * Serves a WebDAV request.
	 */
	public function serveRequest()
	{
		// die quickly if plugin is deactivated
		if (!self::_isActive())
		{
			$this->writelog(__METHOD__.' WebDAV disabled. Aborting');
			$this->http_status('403 Forbidden');
			echo '<html><body><h1>Sorry</h1>'.
				'<p><b>Please enable the WebDAV plugin in the ILIAS Administration panel.</b></p>'.
				'<p>You can only access this page, if WebDAV is enabled on this server.</p>'.
				'</body></html>';
			return;
		}

		try {
			$start = time();
			$this->writelog('serveRequest():'.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['PATH_INFO'].' ...');
			parent::serveRequest();
			$end = time();
			$this->writelog('serveRequest():'.$_SERVER['REQUEST_METHOD'].' done status='.$this->_http_status.' elapsed='.($end - $start));
			$this->writelog('---');
		} 
		catch (Exception $e) 
		{
            $this->writelog('serveRequest():'.$_SERVER['REQUEST_METHOD'].' caught exception: '.$e->getMessage().'\n'.$e->getTraceAsString());
		}
	}

	/**
	* We do not implement this method, because authentication is done by
	* ilias3/webdav.php.
	*
	* @access private
	* @param  string  HTTP Authentication type (Basic, Digest, ...)
	* @param  string  Username
	* @param  string  Password
	* @return bool    true on successful authentication
	* /
	public function check_auth($type, $user, $pass)
	{
			$this->writelog('check_auth type='.$type.' user='.$user.' pass='.$pass);

			if (! $user)
			{
			return false;
			}
		return true;
	}*/

	/**
	 * Encodes an URL.
	 * This function differs from the PHP urlencode() function in the following
	 * way:
	 * - Unicode characters are composed into Unicode Normal Form NFC
	 *   This ensures that WebDAV clients running on Windows and Mac OS X
	 *   treat resource names that contain diacritic marks in the same way.
	 * - Slash characters '/' are preserved
	 *   This ensures that path components are properly recognized by
	 *   WebDAV clients.
	 * - Space characters are encoded as '%20' instead of '+'.
	 *   This ensures proper handling of spaces by WebDAV clients.
	 */
	private function davUrlEncode($path)
	{
		// We compose the path to Unicode Normal Form NFC
		// This ensures that diaeresis and other special characters
		// are treated uniformly on Windows and on Mac OS X
		$path = UtfNormal::toNFC($path);

		$c = explode('/',$path);
		for ($i = 0; $i < count($c); $i++)
		{
			$c[$i] = str_replace('+','%20',urlencode($c[$i]));
		}
		return implode('/',$c);
	}

	/**
	* PROPFIND method handler
	*
	* @param  array  general parameter passing array
	* @param  array  return array for file properties
	* @return bool   true on success
	*/
	public function PROPFIND(&$options, &$files)
	{
		// Activate tree cache
		global $tree;
		//$tree->useCache(true);

		$this->writelog('PROPFIND(options:'.var_export($options, true).' files:'.var_export($files, true).'.)');
		$this->writelog('PROPFIND '.$options['path']);

		// get dav object for path
		$path =& $this->davDeslashify($options['path']);
		$objDAV =& $this->getObject($path);

		// prepare property array
		$files['files'] = array();

		// sanity check
		if (is_null($objDAV)) {
			return false;
		}
		if (! $objDAV->isPermitted('visible,read')) {
			return '403 Forbidden';
		}

		// store information for the requested path itself
		// FIXME : create display name for object.
		$encodedPath = $this->davUrlEncode($path);
		
		$GLOBALS['ilLog']->write(print_r($encodedPath,true));

		$files['files'][] =& $this->fileinfo($encodedPath, $encodedPath, $objDAV);

		// information for contained resources requested?
		if (!empty($options['depth']))  {
			// The breadthFirst list holds the collections which we have not
			// processed yet. If depth is infinity we append unprocessed collections
			// to the end of this list, and remove processed collections from
			// the beginning of this list.
			$breadthFirst = array($objDAV);
			$objDAV->encodedPath = $encodedPath;

			while (count($breadthFirst) > 0) {
				// remove a collection from the beginning of the breadthFirst list
				$collectionDAV = array_shift($breadthFirst);
				$childrenDAV =& $collectionDAV->childrenWithPermission('visible,read');
				foreach ($childrenDAV as $childDAV)
				{
					// On duplicate names, work with the older object (the one with the
					// smaller object id).
					foreach ($childrenDAV as $duplChildDAV)
					{
						if ($duplChildDAV->getObjectId() < $childDAV->getObjectId() &&
								$duplChildDAV->getResourceName() == $childDAV->getResourceName())
						{
							continue 2;
						}
					}

					// only add visible objects to the file list
					if (!$this->isFileHidden($childDAV))
					{
						$this->writelog('PROPFIND() child ref_id='.$childDAV->getRefId());
						$files['files'][] =& $this->fileinfo(
							$collectionDAV->encodedPath.'/'.$this->davUrlEncode($childDAV->getResourceName()),
							$collectionDAV->encodedPath.'/'.$this->davUrlEncode($childDAV->getDisplayName()),
							$childDAV
						);
						if ($options['depth']=='infinity' && $childDAV->isCollection()) {
							// add a collection to the end of the breadthFirst list
							$breadthFirst[] = $childDAV;
							$childDAV->encodedPath = $collectionDAV->encodedPath.'/'.$this->davUrlEncode($childDAV->getResourceName());
						}
					}
				}
			}
		}

		// Record read event but don't catch up with write events, because
		// with WebDAV, a user can not see all objects contained in a folder.
		global $ilUser;
		ilChangeEvent::_recordReadEvent($objDAV->getILIASType(), $objDAV->getRefId(),
			$objDAV->getObjectId(), $ilUser->getId(), false);
		
		// ok, all done
		$this->writelog('PROPFIND():true options='.var_export($options, true).' files='.var_export($files,true));
		return true;
	}

	/**
     * Returns true, if the resource has a file name which is hidden from the user.
	 * Note, that resources with a hidden file name can still be accessed by a
     * WebDAV client, if the client knows the resource name.
	 *
	 * - We hide all Null Resources who haven't got an active lock
	 * - We hide all files with the prefix "." from Windows DAV Clients.
	 * - We hide all files which contain characters that are not allowed on Windows from Windows DAV Clients.
	 * - We hide the files with the prefix " ~$" or the name "Thumbs.db" from Unix DAV Clients.
	 */
	private function isFileHidden(&$objDAV)
	{
		// Hide null resources which haven't got an active lock
		if ($objDAV->isNullResource()) {
			if (count($this->locks->getLocksOnObjectDAV($objDAV)) == 0) {
				return;
			}
		}

		$name = $objDAV->getResourceName();
		$isFileHidden = false;
		switch ($this->clientOS)
		{
		case 'unix' :
			// Hide Windows thumbnail files, and files which start with '~$'.
			$isFileHidden =
				$name == 'Thumbs.db'
				|| substr($name, 0, 2) == '~$';
			// Hide files which contain /
			$isFileHidden |= preg_match('/\\//', $name);
			break;
		case 'windows' :
			// Hide files that start with '.'.
			$isFileHidden = substr($name, 0, 1) == '.';
			// Hide files which contain \ / : * ? " < > |
			$isFileHidden |= preg_match('/\\\\|\\/|:|\\*|\\?|"|<|>|\\|/', $name);
			break;
		default :
			// Hide files which contain /
			$isFileHidden |= preg_match('/\\//', $name);
			break;
		}
		$this->writelog($this->clientOS.' '.$name.' isHidden:'.$isFileHidden.' clientOS:'.$this->clientOS);
		return $isFileHidden;
	}

	/**
	* Creates file info properties for a single file/resource
	*
	* @param  string  resource path
	* @param  ilObjectDAV  resource DAV object
	* @return array   resource properties
	*/
	private function fileinfo($resourcePath, $displayPath, &$objDAV)
	{
		global $ilias;

		$this->writelog('fileinfo('.$resourcePath.')');
		// create result array
		$info = array();
		/* Some clients, for example WebDAV-Sync, need a trailing slash at the
		 * end of a resource path to a collection.
		 * However Mac OS X does not like this!
		 */
		if ($objDAV->isCollection() && $this->clientOSFlavor != 'osx') {
			$info['path'] = $resourcePath.'/';
		} else {
			$info['path'] = $resourcePath;
		}

		$info['props'] = array();

		// no special beautified displayname here ...
		$info["props"][] =& $this->mkprop("displayname", $displayPath);

		// creation and modification time
		$info["props"][] =& $this->mkprop("creationdate", $objDAV->getCreationTimestamp());
		$info["props"][] =& $this->mkprop("getlastmodified", $objDAV->getModificationTimestamp());

		// directory (WebDAV collection)
		$info["props"][] =& $this->mkprop("resourcetype", $objDAV->getResourceType());
		$info["props"][] =& $this->mkprop("getcontenttype", $objDAV->getContentType());
		$info["props"][] =& $this->mkprop("getcontentlength", $objDAV->getContentLength());

		// Only show supported locks for users who have write permission
		if ($objDAV->isPermitted('write'))
		{
			$info["props"][] =& $this->mkprop("supportedlock",
				'<D:lockentry>'
					.'<D:lockscope><D:exclusive/></D:lockscope>'
					.'<D:locktype><D:write/></D:locktype>'
				.'</D:lockentry>'
				.'<D:lockentry>'
					.'<D:lockscope><D:shared/></D:lockscope>'
					.'<D:locktype><D:write/></D:locktype>'
				.'</D:lockentry>'
			);
		}

		// Maybe we should only show locks on objects for users who have write permission.
		// But if we don't show these locks, users who have write permission in an object
		// further down in a hierarchy can't see who is locking their object.
		$locks = $this->locks->getLocksOnObjectDAV($objDAV);
		$lockdiscovery = '';
		foreach ($locks as $lock)
		{
			// DAV Clients expects to see their own owner name in
			// the locks. Since these names are not unique (they may
			// just be the name of the local user running the DAV client)
			// we return the ILIAS user name in all other cases.
			if ($lock['ilias_owner'] == $ilias->account->getId())
			{
				$owner = $lock['dav_owner'];
			} else {
				$owner = '<D:href>'.$this->getLogin($lock['ilias_owner']).'</D:href>';
			}
			$this->writelog('lockowner='.$owner.' ibi:'.$lock['ilias_owner'].' davi:'.$lock['dav_owner']);

			$lockdiscovery .=
			'<D:activelock>'
				.'<D:lockscope><D:'.$lock['scope'].'/></D:lockscope>'
				//.'<D:locktype><D:'.$lock['type'].'/></D:locktype>'
				.'<D:locktype><D:write/></D:locktype>'
				.'<D:depth>'.$lock['depth'].'</D:depth>'
				.'<D:owner>'.$owner.'</D:owner>'

				// more than a million is considered an absolute timestamp
				// less is more likely a relative value
				.'<D:timeout>Second-'.(($lock['expires'] > 1000000) ? $lock['expires']-time():$lock['expires']).'</D:timeout>'
				.'<D:locktoken><D:href>'.$lock['token'].'</D:href></D:locktoken>'
			.'</D:activelock>'
			;
		}
		if (strlen($lockdiscovery) > 0)
		{
			$info["props"][] =& $this->mkprop("lockdiscovery", $lockdiscovery);
		}

		// get additional properties from database
		$properties = $this->properties->getAll($objDAV);
		foreach ($properties as $prop)
		{
			$info["props"][] = $this->mkprop($prop['namespace'], $prop['name'], $prop['value']);
		}

		//$this->writelog('fileinfo():'.var_export($info, true));
		return $info;
	}

	/**
	* GET method handler.
	*
	* If the path denotes a directory, and if URL contains the query string "mount",
	* a WebDAV mount-request is sent to the client.
	* If the path denotes a directory, and if URL contains the query string "mount-instructions",
	* instructions for mounting the directory are sent to the client.
	*
	* @param  array  parameter passing array
	* @return bool   true on success
	*/
	public function GET(&$options)
	{
		global $ilUser;

		$this->writelog('GET('.var_export($options, true).')');
		$this->writelog('GET('.$options['path'].')');

		// get dav object for path
		$path = $this->davDeslashify($options['path']);
		$objDAV =& $this->getObject($path);

		// sanity check
		if (is_null($objDAV) || $objDAV->isNullResource())
		{
			return false;
		}

		if (! $objDAV->isPermitted('visible,read'))
		{
			return '403 Forbidden';
		}

		//  is this a collection?
		if ($objDAV->isCollection())
		{
			if (isset($_GET['mount']))
			{
				return $this->mountDir($objDAV, $options);
			} 
			else if (isset($_GET['mount-instructions']))
			{
				return $this->showMountInstructions($objDAV, $options);
			} 
			else 
			{
				return $this->getDir($objDAV, $options);
			}
		}
		// detect content type
		$options['mimetype'] =& $objDAV->getContentType();
		// detect modification time
		// see rfc2518, section 13.7
		// some clients seem to treat this as a reverse rule
		// requiring a Last-Modified header if the getlastmodified header was set
		$options['mtime'] =& $objDAV->getModificationTimestamp();

		// detect content length
		$options['size'] =& $objDAV->getContentLength();

		// get content as stream or as data array
		$options['stream'] =& $objDAV->getContentStream();
		if (is_null($options['stream']))
		{
			$options['data'] =& $objDAV->getContentData();
		}

		// Record read event and catch up write events
		ilChangeEvent::_recordReadEvent($objDAV->getILIASType(), $objDAV->getRefId(),
			$objDAV->getObjectId(), $ilUser->getId());
		
		$this->writelog('GET:'.var_export($options, true));

		return true;
	}
	/**
	* Mount method handler for directories
	*
	* Mounting is done according to the internet draft RFC 4709 "Mounting WebDAV servers"
	* "draft-reschke-webdav-mount-latest".
	* See
	* http://greenbytes.de/tech/webdav/draft-reschke-webdav-mount-latest.html
	*
	* @param  ilObjectDAV  dav object handler
	* @return This function does not return. It exits PHP.
	*/
	private function mountDir(&$objDAV, &$options)
	{
		$path = $this->davDeslashify($options['path']);

		header('Content-Type: application/davmount+xml');

		echo "<dm:mount xmlns:dm=\"http://purl.org/NET/webdav/mount\">\n";
		echo "  </dm:url>".$this->base_uri."</dm:url>\n";

		$xmlPath = str_replace('&','&amp;',$path);
		$xmlPath = str_replace('<','&lt;',$xmlPath);
		$xmlPath = str_replace('>','&gt;',$xmlPath);

		echo "  </dm:open>$xmlPath</dm:open>\n";
		echo "</dm:mount>\n";

		exit;

	}
	/**
	* Mount instructions method handler for directories
	*
	* @param  ilObjectDAV  dav object handler
	* @return This function does not return. It exits PHP.
	*/
	private function showMountInstructions(&$objDAV, &$options)
	{
		global $lng,$ilUser;

		$path = $this->davDeslashify($options['path']);

		// The $path variable may contain a full or a shortened DAV path.
		// We convert it into an object path, which we can then use to 
		// construct a new full DAV path.
		$objectPath = $this->toObjectPath($path);

		// Construct a (possibly) full DAV path from the object path.
		$fullPath = '';
		foreach ($objectPath as $object)
		{
			if ($object->getRefId() == 1 && $this->isFileHidden($object))
			{
				// If the repository root object is hidden, we can not
				// create a full path, because nothing would appear in the
				// webfolder. We resort to a shortened path instead.
				$fullPath .= '/ref_1';
			}
			else
			{
				$fullPath .= '/'.$this->davUrlEncode($object->getResourceName());
			}
		}

		// Construct a shortened DAV path from the object path.
		$shortenedPath = '/ref_'.
				$objectPath[count($objectPath) - 1]->getRefId();

		if ($objDAV->isCollection())
		{
			$shortenedPath .= '/';
			$fullPath .= '/';
		}

		// Prepend client id to path
		$shortenedPath = '/'.CLIENT_ID.$shortenedPath;
		$fullPath = '/'.CLIENT_ID.$fullPath;

		// Construct webfolder URI's. The URI's are used for mounting the
		// webfolder. Since mounting using URI's is not standardized, we have
		// to create different URI's for different browsers.
		$webfolderURI = $this->base_uri.$shortenedPath;
		$webfolderURI_Konqueror = ($this->isWebDAVoverHTTPS() ? "webdavs" : "webdav").
				substr($this->base_uri, strrpos($this->base_uri,':')).
				$shortenedPath;
				;
		$webfolderURI_Nautilus = ($this->isWebDAVoverHTTPS() ? "davs" : "dav").
				substr($this->base_uri, strrpos($this->base_uri,':')).
				$shortenedPath
				;
		$webfolderURI_IE = $this->base_uri.$shortenedPath;

		$webfolderTitle = $objectPath[count($objectPath) - 1]->getResourceName();

		header('Content-Type: text/html; charset=UTF-8');
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN\"\n";
		echo "	\"http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd\">\n";
		echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
		echo "  <head>\n";
		echo "  <title>".sprintf($lng->txt('webfolder_instructions_titletext'), $webfolderTitle)."</title>\n";
		echo "  </head>\n";
		echo "  <body>\n";

		echo ilDAVServer::_getWebfolderInstructionsFor($webfolderTitle,
			$webfolderURI, $webfolderURI_IE, $webfolderURI_Konqueror, $webfolderURI_Nautilus,
			$this->clientOS,$this->clientOSFlavor);

		echo "  </body>\n";
		echo "</html>\n";
		
		// Logout anonymous user to force authentication after calling mount uri
		if($ilUser->getId() == ANONYMOUS_USER_ID)
		{
			$GLOBALS['ilAuth']->logout();
			session_destroy();
		}
		
		exit;
	}
	/**
	* GET method handler for directories
	*
	* This is a very simple mod_index lookalike.
	* See RFC 2518, Section 8.4 on GET/HEAD for collections
	*
	* @param  ilObjectDAV  dav object handler
	* @return void    function has to handle HTTP response itself
	*/
	private function getDir(&$objDAV, &$options)
	{
		global $ilias, $lng;

		// Activate tree cache
		global $tree;
		//$tree->useCache(true);

		$path = $this->davDeslashify($options['path']);

		// The URL of a directory must end with a slash.
		// If it does not we are redirecting the browser.
		// The slash is required, because we are using relative links in the
		// HTML code we are generating below.
		if ($path.'/' != $options['path'])
		{
			header('Location: '.$this->base_uri.$path.'/');
			exit;
		}

		header('Content-Type: text/html; charset=UTF-8');

		// fixed width directory column format
		$format = "%15s  %-19s  %-s\n";

		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN\"\n";
		echo "	\"http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd\">\n";
		echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
		echo "<head>\n";
		echo "<title>".sprintf($lng->txt('webfolder_index_of'), $path)."</title>\n";

		// Create "anchorClick" behavior for for Internet Explorer
		// This allows to create a link to a webfolder
		echo "<style type=\"text/css\">\n";
		echo "<!--\n";
		echo "a {\n";
		echo "  behavior:url(#default#AnchorClick);\n";
		echo "}\n";
		echo "-->\n";
		echo "</style>\n";

		echo "</head><body>\n";

		$hrefPath = '';
		$pathComponents = explode('/',$path);
		$uriComponents = array();
		foreach ($pathComponents as $component)
		{
			$uriComponents[] = $this->davUrlEncode($component);
		}
		for ($i = 0; $i < count($pathComponents); $i++)
		{
			$displayName = htmlspecialchars($pathComponents[$i]);
			if ($i != 0) {
				$hrefPath .= '/';
			}
			$uriPath = implode('/', array_slice($uriComponents,0,$i + 1));
			if ($i < 2)
			{
				// The first two path elements consist of the webdav.php script
				// and the client id. These elements are not part of the
				// directory structure and thus are not represented as links.
				$hrefPath .= $displayName;
			}
			else
			{
				$hrefPath .= '<a href="'.$this->base_uri.$uriPath.'/">'.$displayName.'</a>';
			}
		}
		echo "<h3>".sprintf($lng->txt('webfolder_index_of'), $hrefPath)."</h3>\n";

		// Display user id
		if ($ilias->account->getLogin() == 'anonymous')
		{
			echo "<p><font size=\"-1\">".$lng->txt('not_logged_in')."</font><br>\n";
		} else {
			echo "<p><font size=\"-1\">".$lng->txt('login_as')." <i>"
				.$ilias->account->getFirstname().' '
				.$ilias->account->getLastname().' '
				.' '.$ilias->account->getLogin().'</i> '
				.', '.$lng->txt('client').' <i>'.$ilias->getClientId().'</i>.'
				."</font><p>\n";
		}

		// Create "open as webfolder" link
		$href = $this->base_uri.$uriPath;
		// IE can not mount long paths. If the path has more than one element, we
		// create a relative path with a ref-id.
		if (count($pathComponents) > 2)
		{
			$hrefIE = $this->base_uri.'/'.CLIENT_ID.'/ref_'.$objDAV->getRefId();
		} else {
		 	$hrefIE = $href;
		}
		echo "<p><font size=\"-1\">".
				sprintf($lng->txt('webfolder_dir_info'), "$href?mount-instructions").
				"</font></p>\n";
		echo "<p><font size=\"-1\">".
				sprintf($lng->txt('webfolder_mount_dir_with'),
					"$hrefIE\" folder=\"$hrefIE", // Internet Explorer
					'webdav'.substr($href,4), // Konqueror
					'dav'.substr($href,4), // Nautilus
					$href.'?mount' // RFC 4709
				)
			."</font></p>\n";

		echo "<pre>";
		printf($format, $lng->txt('size'), $lng->txt('last_change'), $lng->txt('filename'));
		echo "<hr>";

		$collectionCount = 0;
		$fileCount = 0;
		$children =& $objDAV->childrenWithPermission('visible,read');
		foreach ($children as $childDAV) {
			if ($childDAV->isCollection() && !$this->isFileHidden($childDAV))
			{
				$collectionCount++;
				$name = $this->davUrlEncode($childDAV->getResourceName());
				printf($format,
					'-',
					strftime("%Y-%m-%d %H:%M:%S", $childDAV->getModificationTimestamp()),
					'<a href="'.$name.'/'.'">'.$childDAV->getDisplayName()."</a>");
			}
		}
		foreach ($children as $childDAV) {
			if ($childDAV->isFile() && !$this->isFileHidden($childDAV))
			{
				$fileCount++;
				$name = $this->davUrlEncode($childDAV->getResourceName());
				printf($format,
					number_format($childDAV->getContentLength()),
					strftime("%Y-%m-%d %H:%M:%S", $childDAV->getModificationTimestamp()),
					'<a href="'.$name.'">'.$childDAV->getDisplayName()."</a>");
			}
		}
		foreach ($children as $childDAV) {
			if ($childDAV->isNullResource() && !$this->isFileHidden($childDAV))
			{
				$name = $this->davUrlEncode($childDAV->getResourceName());
				printf($format,
					'Lock',
					strftime("%Y-%m-%d %H:%M:%S", $childDAV->getModificationTimestamp()),
					'<a href="'.$name.'">'.$childDAV->getDisplayName()."</a>");
			}
		}
		echo "<hr>";
		echo $collectionCount.' '.$lng->txt(($collectionCount == 1) ? 'folder' : 'folders').', ';
		echo $fileCount.' '.$lng->txt(($fileCount == 1) ? 'file' : 'files').'.';
		echo "</pre>";
		echo "</body></html>\n";

		exit;
	}


	/**
	* PUT method handler
	*
	* @param  array  parameter passing array
	* @return bool   true on success
	*/
	public function PUT(&$options)
	{
		global $ilUser;

		$this->writelog('PUT('.var_export($options, true).')');

		$path = $this->davDeslashify($options['path']);
		$parent = dirname($path);
		$name = $this->davBasename($path);

		// get dav object for path
		$parentDAV =& $this->getObject($parent);

		// sanity check
		if (is_null($parentDAV) || ! $parentDAV->isCollection()) {
			return '409 Conflict';
		}

        // Prevent putting of files which exceed upload limit
        // FIXME: since this is an optional parameter, we should to do the
        // same check again in function PUTfinished.
		if ($options['content_length'] != null &&
                $options['content_length'] > $this->getUploadMaxFilesize()) {

            $this->writelog('PUT is forbidden, because content length='.
                        $options['content_length'].' is larger than upload_max_filesize='.
                        $this->getUploadMaxFilesize().'in php.ini');

            return '403 Forbidden';
        }
        
		// determine mime type
		include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
		$mime = ilMimeTypeUtil::getMimeType("", $name, $options['content_type']);

		$objDAV =& $this->getObject($path);
		if (is_null($objDAV))
		{
			$ttype = $parentDAV->getILIASFileType();
			$isperm = $parentDAV->isPermitted('create', $ttype);
			if (! $isperm)
			{
                $this->writelog('PUT is forbidden, because user has no create permission');

				return '403 Forbidden';
			}
			$options["new"] = true;
			$objDAV =& $parentDAV->createFile($name);
			$this->writelog('PUT obj='.$objDAV.' name='.$name.' content_type='.$options['content_type']);
			//$objDAV->setContentType($options['content_type']);
			$objDAV->setContentType($mime);
			if ($options['content_length'] != null)
			{
				$objDAV->setContentLength($options['content_length']);
			}
			$objDAV->write();
			// Record write event
			ilChangeEvent::_recordWriteEvent($objDAV->getObjectId(), $ilUser->getId(), 'create', $parentDAV->getObjectId());
		}
		else if ($objDAV->isNullResource())
		{
			if (! $parentDAV->isPermitted('create', $parentDAV->getILIASFileType()))
			{
                $this->writelog('PUT is forbidden, because user has no create permission');
				return '403 Forbidden';
			}
			$options["new"] = false;
			$objDAV =& $parentDAV->createFileFromNull($name, $objDAV);
			$this->writelog('PUT obj='.$objDAV.' name='.$name.' content_type='.$options['content_type']);
			//$objDAV->setContentType($options['content_type']);
			$objDAV->setContentType($mime);
			if ($options['content_length'] != null)
			{
				$objDAV->setContentLength($options['content_length']);
			}
			$objDAV->write();

			// Record write event
			ilChangeEvent::_recordWriteEvent($objDAV->getObjectId(), $ilUser->getId(), 'create', $parentDAV->getObjectId());
		}
		else
		{
			if (! $objDAV->isPermitted('write'))
			{
                $this->writelog('PUT is forbidden, because user has no write permission');
				return '403 Forbidden';
			}
			$options["new"] = false;
			$this->writelog('PUT obj='.$objDAV.' name='.$name.' content_type='.$options['content_type'].' content_length='.$options['content_length']);

			// Create a new version if the previous version is not empty
			if ($objDAV->getContentLength() != 0) {
				$objDAV->createNewVersion();
			}

			//$objDAV->setContentType($options['content_type']);
			$objDAV->setContentType($mime);
			if ($options['content_length'] != null)
			{
       			$objDAV->setContentLength($options['content_length']);
			}
			$objDAV->write();

			// Record write event
			ilChangeEvent::_recordWriteEvent($objDAV->getObjectId(), $ilUser->getId(), 'update');
			ilChangeEvent::_catchupWriteEvents($objDAV->getObjectId(), $ilUser->getId(), 'update');
		}
		// store this object, we reuse it in method PUTfinished
		$this->putObjDAV = $objDAV;

		$out =& $objDAV->getContentOutputStream();
		$this->writelog('PUT outputstream='.$out);

		return $out;
	}

	/**
	* PUTfinished handler
	*
	* @param  array  parameter passing array
	* @return bool   true on success
	*/
	public function PUTfinished(&$options)
	{
		$this->writelog('PUTfinished('.var_export($options, true).')');

		// Update the content length in the file object, if the
		// the client did not specify a content_length
		if ($options['content_length'] == null)
		{
			$objDAV = $this->putObjDAV;
     		$objDAV->setContentLength($objDAV->getContentOutputStreamLength());
			$objDAV->write();
			$this->putObjDAV = null;
		}
		return true;
	}


	/**
		* MKCOL method handler
		*
		* @param  array  general parameter passing array
		* @return bool   true on success
		*/
	public function MKCOL($options)
	{
		global $ilUser;

		$this->writelog('MKCOL('.var_export($options, true).')');
		$this->writelog('MKCOL '.$options['path']);

		$path =& $this->davDeslashify($options['path']);
		$parent =& dirname($path);
		$name =& $this->davBasename($path);

		// No body parsing yet
		if(!empty($_SERVER["CONTENT_LENGTH"])) {
			return "415 Unsupported media type";
		}

		// Check if an object with the path already exists.
		$objDAV =& $this->getObject($path);
		if (! is_null($objDAV))
		{
			return '405 Method not allowed';
		}

		// get parent dav object for path
		$parentDAV =& $this->getObject($parent);

		// sanity check
		if (is_null($parentDAV) || ! $parentDAV->isCollection())
		{
			return '409 Conflict';
		}

		if (! $parentDAV->isPermitted('create',$parentDAV->getILIASCollectionType()))
		{
			return '403 Forbidden';
		}

		// XXX Implement code that Handles null resource here

		$objDAV = $parentDAV->createCollection($name);

		if ($objDAV != null)
		{
			// Record write event
			ilChangeEvent::_recordWriteEvent((int) $objDAV->getObjectId(), $ilUser->getId(), 'create', $parentDAV->getObjectId());
		}

		$result = ($objDAV != null) ? "201 Created" : "409 Conflict";
		return $result;
	}


	/**
	* DELETE method handler
	*
	* @param  array  general parameter passing array
	* @return bool   true on success
	*/
	public function DELETE($options)
	{
		global $ilUser;

		$this->writelog('DELETE('.var_export($options, true).')');
		$this->writelog('DELETE '.$options['path']);

		// get dav object for path
		$path =& $this->davDeslashify($options['path']);
		$parentDAV =& $this->getObject(dirname($path));
		$objDAV =& $this->getObject($path);

		// sanity check
		if (is_null($objDAV) || $objDAV->isNullResource())
		{
			return '404 Not Found';
		}
		if (! $objDAV->isPermitted('delete'))
		{
			return '403 Forbidden';
		}

		$parentDAV->remove($objDAV);

		// Record write event
		ilChangeEvent::_recordWriteEvent($objDAV->getObjectId(), $ilUser->getId(), 'delete', $parentDAV->getObjectId());

		return '204 No Content';
	}

	/**
	* MOVE method handler
	*
	* @param  array  general parameter passing array
	* @return bool   true on success
	*/
	public function MOVE($options)
	{
		global $ilUser;

		$this->writelog('MOVE('.var_export($options, true).')');
		$this->writelog('MOVE '.$options['path'].' '.$options['dest']);

		// Get path names
		$src = $this->davDeslashify($options['path']);
		$srcParent = dirname($src);
		$srcName = $this->davBasename($src);
		$dst = $this->davDeslashify($options['dest']);

		$dstParent = dirname($dst);
		$dstName = $this->davBasename($dst);
		$this->writelog('move '.$dst.'   dstname='.$dstName);
		// Source and destination must not be the same
		if ($src == $dst)
		{
				return '409 Conflict (source and destination are the same)';
		}

		// Destination must not be in a subtree of source
		if (substr($dst,strlen($src)+1) == $src.'/')
		{
				return '409 Conflict (destination is in subtree of source)';
		}

		// Get dav objects for path
		$srcDAV =& $this->getObject($src);
		$dstDAV =& $this->getObject($dst);
		$srcParentDAV =& $this->getObject($srcParent);
		$dstParentDAV =& $this->getObject($dstParent);

		// Source must exist
		if ($srcDAV == null)
		{
				return '409 Conflict (source does not exist)';
		}

		// Overwriting is only allowed, if overwrite option is set to 'T'
		$isOverwritten = false;
		if ($dstDAV != null)
		{
				if ($options['overwrite'] == 'T')
				{
						// Delete the overwritten destination
						if ($dstDAV->isPermitted('delete'))
						{
								$dstParentDAV->remove($dstDAV);
								$dstDAV = null;
								$isOverwritten = true;
						} else {
								return '403 Not Permitted';
						}
				} else {
						return '412 Precondition Failed';
				}
		}

		// Parents of destination must exist
		if ($dstParentDAV == null)
		{
				return '409 Conflict (parent of destination does not exist)';
		}

		if ($srcParent == $dstParent)
		{
				// Rename source, if source and dest are in same parent

				// Check permission
				if (! $srcDAV->isPermitted('write'))
				{
						return '403 Forbidden';
				}
	$this->writelog('rename dstName='.$dstName);
				$srcDAV->setResourceName($dstName);
				$srcDAV->write();
		} else {
				// Move source, if source and dest are in same parent


				if (! $srcDAV->isPermitted('delete'))
				{
						return '403 Forbidden';
				}

				if (! $dstParentDAV->isPermitted('create', $srcDAV->getILIASType()))
				{
						return '403 Forbidden';
				}
				$dstParentDAV->addMove($srcDAV, $dstName);
                }

		// Record write event
		if ($isOverwritten)
		{
			ilChangeEvent::_recordWriteEvent($srcDAV->getObjectId(), $ilUser->getId(), 'rename');
		}
		else
		{
			ilChangeEvent::_recordWriteEvent($srcDAV->getObjectId(), $ilUser->getId(), 'remove', $srcParentDAV->getObjectId());
			ilChangeEvent::_recordWriteEvent($srcDAV->getObjectId(), $ilUser->getId(), 'add', $dstParentDAV->getObjectId());
		}		

		return ($isOverwritten) ? '204 No Content' : '201 Created';
	}

	/**
	 * COPY method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	public function COPY($options, $del=false)
	{
		global $ilUser;
		$this->writelog('COPY('.var_export($options, true).' ,del='.$del.')');
		$this->writelog('COPY '.$options['path'].' '.$options['dest']);

		// no copying to different WebDAV Servers
		if (isset($options["dest_url"])) {
			return "502 bad gateway";
		}

		$src = $this->davDeslashify($options['path']);
		$srcParent = dirname($src);
		$srcName = $this->davBasename($src);
		$dst = $this->davDeslashify($options['dest']);
		$dstParent = dirname($dst);
		$dstName = $this->davBasename($dst);

		// sanity check
		if ($src == $dst)
		{
			return '409 Conflict'; // src and dst are the same
		}

		if (substr($dst,strlen($src)+1) == $src.'/')
		{
			return '409 Conflict'; // dst is in subtree of src
		}

		$this->writelog('COPY src='.$src.' dst='.$dst);
		// get dav object for path
		$srcDAV =& $this->getObject($src);
		$dstDAV =& $this->getObject($dst);
		$dstParentDAV =& $this->getObject($dstParent);

		if (is_null($srcDAV) || $srcDAV->isNullResource())
		{
			return '409 Conflict'; // src does not exist
		}
		if (is_null($dstParentDAV) || $dstParentDAV->isNullResource())
		{
			return '409 Conflict'; // parent of dst does not exist
		}
		$isOverwritten = false;

		// XXX Handle nulltype for dstDAV
		if (! is_null($dstDAV))
		{
			if ($options['overwrite'] == 'T')
			{
				if ($dstDAV->isPermitted('delete'))
				{
					$dstParentDAV->remove($dstDAV);
					ilChangeEvent::_recordWriteEvent($dstDAV->getObjectId(), $ilUser->getId(), 'delete', $dstParentDAV->getObjectId());

					$dstDAV = null;
					$isOverwritten = true;
				} else {
					return '403 Forbidden';
				}
			} else {
					return '412 Precondition Failed';
			}
		}

		if (! $dstParentDAV->isPermitted('create', $srcDAV->getILIASType()))
		{
			return '403 Forbidden';
		}
		$dstDAV = $dstParentDAV->addCopy($srcDAV, $dstName);

		// Record write event
		ilChangeEvent::_recordReadEvent($srcDAV->getILIASType(), $srcDAV->getRefId(),
			$srcDAV->getObjectId(), $ilUser->getId());
		ilChangeEvent::_recordWriteEvent($dstDAV->getObjectId(), $ilUser->getId(), 'create', $dstParentDAV->getObjectId());		

		return ($isOverwritten) ? '204 No Content' : '201 Created';
	}

	/**
		* PROPPATCH method handler
		*
		* @param  array  general parameter passing array
		* @return bool   true on success
		*/
	public function PROPPATCH(&$options)
	{
		$this->writelog('PROPPATCH(options='.var_export($options, true).')');
		$this->writelog('PROPPATCH '.$options['path']);

		// get dav object for path
		$path =& $this->davDeslashify($options['path']);
		$objDAV =& $this->getObject($path);

		// sanity check
		if (is_null($objDAV) || $objDAV->isNullResource()) return false;

		$isPermitted = $objDAV->isPermitted('write');
		foreach($options['props'] as $key => $prop) {
			if (!$isPermitted || $prop['ns'] == 'DAV:')
			{
				$options['props'][$key]['status'] = '403 Forbidden';
			} else {
				$this->properties->put($objDAV, $prop['ns'],$prop['name'],$prop['val']);
			}
		}

		return "";
	}


	/**
		* LOCK method handler
		*
		* @param  array  general parameter passing array
		* @return bool   true on success
		*/
	public function LOCK(&$options)
	{
		global $ilias;
		$this->writelog('LOCK('.var_export($options, true).')');
		$this->writelog('LOCK '.$options['path']);

		// Check if an object with the path exists.
		$path =& $this->davDeslashify($options['path']);
		$objDAV =& $this->getObject($path);
		// Handle null-object locking
		// --------------------------
		if (is_null($objDAV))
		{
			$this->writelog('LOCK handling null-object locking...');

			// If the name does not exist, we create a null-object for it
			if (isset($options["update"]))
			{
				$this->writelog('LOCK lock-update failed on non-existing null-object.');
				return '412 Precondition Failed';
			}

			$parent = dirname($path);
			$parentDAV =& $this->getObject($parent);
			if (is_null($parentDAV))
			{
				$this->writelog('LOCK lock failed on non-existing path to null-object.');
				return '404 Not Found';
			}
			if (! $parentDAV->isPermitted('create', $parentDAV->getILIASFileType()) &&
				! $parentDAV->isPermitted('create', $parentDAV->getILIASCollectionType()))
			{
				$this->writelog('LOCK lock failed - creation of null object not permitted.');
				return '403 Forbidden';
			}

			$objDAV =& $parentDAV->createNull($this->davBasename($path));
			$this->writelog('created null resource for '.$path);
		}

		// ---------------------
		if (! $objDAV->isNullResource() && ! $objDAV->isPermitted('write'))
		{
			$this->writelog('LOCK lock failed - user has no write permission.');
			return '403 Forbidden';
		}

		// XXX - Check if there are other locks on the resource
		if (!isset($options['timeout']) || is_array($options['timeout']))
		{
			$options["timeout"] = time()+360; // 6min.
		}

		if(isset($options["update"])) { // Lock Update
			$this->writelog('LOCK update token='.var_export($options,true));
			$success = $this->locks->updateLockWithoutCheckingDAV(
				$objDAV,
				$options['update'],
				$options['timeout']
			);
			if ($success)
			{
				$data = $this->locks->getLockDAV($objDAV, $options['update']);
				if ($data['ilias_owner'] == $ilias->account->getId())
				{
					$owner = $data['dav_owner'];
				} else {
					$owner = '<D:href>'.$this->getLogin($data['ilias_owner']).'</D:href>';
				}
				$options['owner'] = $owner;
				$options['locktoken'] = $data['token'];
				$options['timeout'] = $data['expires'];
				$options['depth'] = $data['depth'];
				$options['scope'] = $data['scope'];
				$options['type'] = $data['scope'];
			}

		} else {
			$this->writelog('LOCK create new lock');

			// XXX - Attempting to create a recursive exclusive lock
			// on a collection must fail, if any of nodes in the subtree
			// of the collection already has a lock.
			// XXX - Attempting to create a recursive shared lock
			// on a collection must fail, if any of nodes in the subtree
			// of the collection already has an exclusive lock.
			//$owner = (strlen(trim($options['owner'])) == 0) ? $ilias->account->getLogin() : $options['owner'];
			$this->writelog('lock owner='.$owner);
			$success = $this->locks->lockWithoutCheckingDAV(
				$objDAV,
				$ilias->account->getId(),
				trim($options['owner']),
				$options['locktoken'],
				$options['timeout'],
				$options['depth'],
				$options['scope']
			);
		}

		// Note: As a workaround for the Microsoft WebDAV Client, we return
		//       true/false here (resulting in the status '200 OK') instead of
		//       '204 No Content').
		//return ($success) ? '204 No Content' : false;
		return $success;
	}

	/**
		* UNLOCK method handler
		*
		* @param  array  general parameter passing array
		* @return bool   true on success
		*/
	public function UNLOCK(&$options)
	{
		global $log, $ilias;
		$this->writelog('UNLOCK(options='.var_export($options, true).')');
		$this->writelog('UNLOCK '.$options['path']);

		// Check if an object with the path exists.
		$path =& $this->davDeslashify($options['path']);
		$objDAV =& $this->getObject($path);
		if (is_null($objDAV)) {
			return '404 Not Found';
		}
		if (! $objDAV->isPermitted('write')) {
			return '403 Forbidden';
		}

		$success = $this->locks->unlockWithoutCheckingDAV(
			$objDAV,
			$options['token']
		);

		// Delete null resource object if there are no locks associated to
		// it anymore
		if ($objDAV->isNullResource()
		&& count($this->locks->getLocksOnObjectDAV($objDAV)) == 0)
		{
			$parent = dirname($this->davDeslashify($options['path']));
			$parentDAV =& $this->getObject($parent);
			$parentDAV->remove($objDAV);
		}

		// Workaround for Mac OS X: We must return 200 here instead of
		// 204.
		//return ($success) ? '204 No Content' : '412 Precondition Failed';
		return ($success) ? '200 OK' : '412 Precondition Failed';
	}

	/**
		* checkLock() helper
		*
		* @param  string resource path to check for locks
		* @return array with the following entries: {
		*    type => "write"
		*    scope => "exclusive" | "shared"
		*    depth => 0 | -1
		*    owner => string
		*    token => string
		*    expires => timestamp
		*/
	protected function checkLock($path)
	{
		global $ilias;

		$this->writelog('checkLock('.$path.')');
		$result = null;

		// get dav object for path
		//$objDAV = $this->getObject($path);

		// convert DAV path into ilObjectDAV path
		$objPath = $this->toObjectPath($path);
		if (! is_null($objPath))
		{
			$objDAV = $objPath[count($objPath) - 1];
			$locks = $this->locks->getLocksOnPathDAV($objPath);
			foreach ($locks as $lock)
			{
				$isLastPathComponent = $lock['obj_id'] == $objDAV->getObjectId()
				&& $lock['node_id'] == $objDAV->getNodeId();

				// Check all locks on last object in path,
				// but only locks with depth infinity on parent objects.
				if ($isLastPathComponent || $lock['depth'] == 'infinity')
				{
					// DAV Clients expects to see their own owner name in
					// the locks. Since these names are not unique (they may
					// just be the name of the local user running the DAV client)
					// we return the ILIAS user name in all other cases.
					if ($lock['ilias_owner'] == $ilias->account->getId())
					{
						$owner = $lock['dav_owner'];
					} else {
						$owner = $this->getLogin($lock['ilias_owner']);
					}

					// FIXME - Shouldn't we collect all locks instead of
					//         using an arbitrary one?
					$result = array(
						"type"    => "write",
						"obj_id"   => $lock['obj_id'],
						"node_id"   => $lock['node_id'],
						"scope"   => $lock['scope'],
						"depth"   => $lock['depth'],
						"owner"   => $owner,
						"token"   => $lock['token'],
						"expires" => $lock['expires']
					);
					if ($lock['scope'] == 'exclusive')
					{
						// If there is an exclusive lock in the path, it
						// takes precedence over all non-exclusive locks in
						// parent nodes. Therefore we can can finish collecting
						// locks.
						break;
					}
				}
			}
		}
		$this->writelog('checkLock('.$path.'):'.var_export($result,true));

		return $result;
	}

	/**
	* Returns the login for the specified user id, or null if
	* the user does not exist.
	*/
	protected function getLogin($userId)
	{
		$login = ilObjUser::_lookupLogin($userId);
		$this->writelog('getLogin('.$userId.'):'.var_export($login,true));
		return $login;
	}


	/**
	* Gets a DAV object for the specified path.
	*
	* @param  String davPath A DAV path expression.
	* @return ilObjectDAV object or null, if the path does not denote an object.
	*/
	private function getObject($davPath)
	{
		global $tree;


		// If the second path elements starts with 'file_', the following
		// characters of the path element directly identify the ref_id of
		// a file object.
		$davPathComponents = split('/',substr($davPath,1));
		if (count($davPathComponents) > 1 &&
			substr($davPathComponents[1],0,5) == 'file_')
		{
			$ref_id = substr($davPathComponents[1],5);
			$nodePath = $tree->getNodePath($ref_id, $tree->root_id);

			// Poor IE needs this, in order to successfully display
			// PDF documents
			header('Pragma: private');
		}
		else
		{
			$nodePath = $this->toNodePath($davPath);
			if ($nodePath == null && count($davPathComponents) == 1)
			{
				return ilObjectDAV::createObject(-1,'mountPoint');
			}
		}
		if (is_null($nodePath))
		{
			return null;
		} else {
			$top = $nodePath[count($nodePath)  - 1];
			return ilObjectDAV::createObject($top['child'],$top['type']);
		}
	}
	/**
	* Converts a DAV path into an array of DAV objects.
	*
	* @param  String davPath A DAV path expression.
	* @return array<ilObjectDAV> object or null, if the path does not denote an object.
	*/
	private function toObjectPath($davPath)
	{
		$this->writelog('toObjectPath('.$davPath);
		global $tree;

		$nodePath = $this->toNodePath($davPath);

		if (is_null($nodePath))
		{
			return null;
		} else {
			$objectPath = array();
			foreach ($nodePath as $node)
			{
				$pathElement = ilObjectDAV::createObject($node['child'],$node['type']);
				if (is_null($pathElement))
				{
					break;
				}
				$objectPath[] = $pathElement;
			}
			return $objectPath;
		}
	}

	/**
	 * Converts a DAV path into a node path.
	 * The returned array is granted to represent an absolute path.
	 *
	 * The first component of a DAV Path is the ILIAS client id. The following
	 * component either denote an absolute path, or a relative path starting at
	 * a ref_id.
	 *
     * @param  String davPath A DAV path expression.
	 * @return Array<String> An Array of path titles.
	 */
	public function toNodePath($davPath)
	{
		global $tree;
		$this->writelog('toNodePath('.$davPath.')...');

		// Split the davPath into path titles
		$titlePath = split('/',substr($davPath,1));

		// Remove the client id from the beginning of the title path
		if (count($titlePath) > 0)
		{
			array_shift($titlePath);
		}

		// If the last path title is empty, remove it
		if (count($titlePath) > 0 && $titlePath[count($titlePath) - 1] == '')
		{
			array_pop($titlePath);
		}

		// If the path is empty, return null
		if (count($titlePath) == 0)
		{
			$this->writelog('toNodePath('.$davPath.'):null, because path is empty.');
			return null;
		}

		// If the path is an absolute path, ref_id is null.
		$ref_id = null;

		// If the path is a relative folder path, convert it into an absolute path
		if (count($titlePath) > 0 && substr($titlePath[0],0,4) == 'ref_')
		{
			$ref_id = substr($titlePath[0],4);
			array_shift($titlePath);
		}

		$nodePath = $tree->getNodePathForTitlePath($titlePath, $ref_id);

		$this->writelog('toNodePath():'.var_export($nodePath,true));
		return $nodePath;
	}

	/**
	* davDeslashify - make sure path does not end in a slash
	*
	* @param   string directory path
	* @returns string directory path without trailing slash
	*/
	private function davDeslashify($path)
	{
		$path = UtfNormal::toNFC($path);

		if ($path[strlen($path)-1] == '/') {
			$path = substr($path,0, strlen($path) - 1);
		}
		$this->writelog('davDeslashify:'.$path);
		return $path;
	}

	/**
	 * Private implementation of PHP basename() function.
	 * The PHP basename() function does not work properly with filenames that contain
	 * international characters.
	 * e.g. basename('/x/ö') returns 'x' instead of 'ö'
	 */
	private function davBasename($path)
	{
		$components = split('/',$path);
		return count($components) == 0 ? '' : $components[count($components) - 1];
	}

	/**
	* Writes a message to the logfile.,
	*
	* @param  message String.
	* @return void.
	*/
	protected function writelog($message)
	{
		// Only write log message, if we are in debug mode
		if ($this->isDebug)
		{
			global $ilLog, $ilias;
			if ($ilLog)
			{
				if ($message == '---')
				{
						$ilLog->write('');
				} else {
						$ilLog->write(
								$ilias->account->getLogin()
						.' '.$_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT']
						.' ilDAVServer.'.str_replace("\n",";",$message)
							);
				}
			}
			else
			{
				$fh = fopen('/opt/ilias/log/ilias.log', 'a');
				fwrite($fh, date('Y-m-d H:i:s '));
				fwrite($fh, str_replace("\n",";",$message));
				fwrite($fh, "\n\n");
				fclose($fh);
			}
		}
	}

	/**
	 * Returns an URI for mounting the repository object as a webfolder.
	 * The URI can be used as the value of a "href" attribute attribute
	 * inside of an HTML anchor tag "<a>".
	 *
	 * @param refId of the repository object.
	 * @param nodeId of a childnode of the repository object.
	 * @param ressourceName ressource name (if known), to reduce SQL queries
	 * @param parentRefId refId of parent object (if known), to reduce SQL queries
	 * @param genericURI boolean Returns a generic mount URI, which works on
	 * all platforms which support WebDAV as in the IETF specification.
	 */
	function getMountURI($refId, $nodeId = 0, $ressourceName = null, $parentRefId = null, $genericURI = false)
	{
		if ($genericURI) {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:");
			$query = null;
		} else if ($this->clientOS == 'windows') {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:");
			$query = 'mount-instructions';
		} else if ($this->clientBrowser == 'konqueror') {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "webdavs:" : "webdav:");
			$query = null;
		} else if ($this->clientBrowser == 'nautilus') {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "davs:" : "dav:");
			$query = null;
		} else {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:");
			$query = 'mount-instructions';
		}
		$baseUri.= "//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
		$baseUri = substr($baseUri,0,strrpos($baseUri,'/')).'/webdav.php/'.CLIENT_ID;

		$uri = $baseUri.'/ref_'.$refId.'/';
		if ($query != null)
		{
			$uri .= '?'.$query;
		}

		return $uri;
	}
	/**
	 * Returns an URI for mounting the repository object as a webfolder using Internet Explorer
	 * and Firefox with the "openwebfolder" plugin.
	 * The FolderURI is only in effect on Windows. Therefore we don't need to deal with other
	 * pecularities.
	 *
	 * The URI can be used as the value of a "folder" attribute
	 * inside of an HTML anchor tag "<a>".
	 *
	 * @param refId of the repository object.
	 * @param nodeId of a childnode of the repository object.
	 * @param ressourceName ressource name (if known), to reduce SQL queries
	 * @param parentRefId refId of parent object (if known), to reduce SQL queries
	 */
	function getFolderURI($refId, $nodeId = 0, $ressourceName = null, $parentRefId = null)
	{
		if ($this->clientOS == 'windows') {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:");
			$query = null;
		} else if ($this->clientBrowser == 'konqueror') {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "webdavs:" : "webdav:");
			$query = null;
		} else if ($this->clientBrowser == 'nautilus') {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "davs:" : "dav:");
			$query = null;
		} else {
			$baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:");
			$query = null;
		}
		$baseUri.= "//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
		$baseUri = substr($baseUri,0,strrpos($baseUri,'/')).'/webdav.php/'.CLIENT_ID;

		$uri = $baseUri.'/ref_'.$refId.'/';
		if ($query != null)
		{
			$uri .= '?'.$query;
		}

		return $uri;
	}
	/**
	 * Returns an URI for getting a object using WebDAV by its name.
	 *
	 * WebDAV clients can use this URI to access the object from ILIAS.
	 *
	 * @param refId of the object.
	 * @param ressourceName object title (if known), to reduce SQL queries
	 * @param parentRefId refId of parent object (if known), to reduce SQL queries
	 *
	 * @return Returns the URI or null if the URI can not be constructed.
	 */
	public function getObjectURI($refId, $ressourceName = null, $parentRefId = null)
	{
		$nodeId = 0;
		$baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:").
				"//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
		$baseUri = substr($baseUri,0,strrpos($baseUri,'/')).'/webdav.php/'.CLIENT_ID;

		if (! is_null($ressourceName) && ! is_null($parentRefId))
		{
			// Quickly create URI from the known data without needing SQL queries
			$uri = $baseUri.'/ref_'.$parentRefId.'/'.$this->davUrlEncode($ressourceName);
		} else {
			// Create URI and use some SQL queries to get the missing data
			global $tree;
			$nodePath = $tree->getNodePath($refId);

			if (is_null($nodePath) || count($nodePath) < 2)
			{
				// No object path? Return null - file is not in repository.
				$uri = null;
			} else {
				$uri = $baseUri.'/ref_'.$nodePath[count($nodePath) - 2]['child'].'/'.
						$this->davUrlEncode($nodePath[count($nodePath) - 1]['title']);
			}
		}
		return $uri;
	}

	/**
	 * Returns an URI for getting a file object using WebDAV.
	 *
	 * Browsers can use this URI to download a file from ILIAS.
	 *
	 * Note: This could be the same URI that is returned by getObjectURI.
	 * But we use a different URI, because we want to use the regular
	 * ILIAS authentication method, if no session exists, and we
	 * want to be able to download a file from the repository, even if
	 * the name of the file object is not unique.
	 *
	 * @param refId of the file object.
	 * @param ressourceName title of the file object (if known), to reduce SQL queries
	 * @param parentRefId refId of parent object (if known), to reduce SQL queries
	 *
	 * @return Returns the URI or null if the URI can not be constructed.
	 */
	public function getFileURI($refId, $ressourceName = null, $parentRefId = null)
	{
		$nodeId = 0;
		$baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:").
				"//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
		$baseUri = substr($baseUri,0,strrpos($baseUri,'/')).'/webdav.php/'.CLIENT_ID;

		if (! is_null($ressourceName) && ! is_null($parentRefId))
		{
			// Quickly create URI from the known data without needing SQL queries
			$uri = $baseUri.'/file_'.$refId.'/'.$this->davUrlEncode($ressourceName);
		} else {
			// Create URI and use some SQL queries to get the missing data
			global $tree;
			$nodePath = $tree->getNodePath($refId);

			if (is_null($nodePath) || count($nodePath) < 2)
			{
				// No object path? Return null - file is not in repository.
				$uri = null;
			} else {
				$uri = $baseUri.'/file_'.$nodePath[count($nodePath) - 1]['child'].'/'.
						$this->davUrlEncode($nodePath[count($nodePath) - 1]['title']);
			}
		}
		return $uri;
	}

	/**
	 * Returns true, if the WebDAV server transfers data over HTTPS.
	 *
	 * @return boolean Returns true if HTTPS is active.
	 */
	public function isWebDAVoverHTTPS() {
		if ($this->isHTTPS == null) {
			global $ilSetting;
			require_once './Services/Http/classes/class.ilHTTPS.php';
			$https = new ilHTTPS();
			$this->isHTTPS = $https->isDetected() || $ilSetting->get('https');
		}
		return $this->isHTTPS;
	}

	/**
	* Static getter. Returns true, if the WebDAV server is active.
	*
	* THe WebDAV Server is active, if the variable file_access::webdav_enabled
	* is set in the client ini file, and if PEAR Auth_HTTP is installed.
	*
	* @return	boolean	value
	*/
	public static function _isActive()
	{
		global $ilClientIniFile;
		return $ilClientIniFile->readVariable('file_access','webdav_enabled') == '1' &&
			 @include_once("Auth/HTTP.php");
	}
	/**
	* Static getter. Returns true, if WebDAV actions are visible for repository items.
	*
	* @return	boolean	value
	*/
	public static function _isActionsVisible()
	{
		global $ilClientIniFile;
		return $ilClientIniFile->readVariable('file_access','webdav_actions_visible') == '1';
	}

	/**
	* Gets instructions for the usage of webfolders.
	*
	* The instructions consist of HTML text with placeholders.
	* See _getWebfolderInstructionsFor for a description of the supported
	* placeholders.
	*
	* @return String HTML text with placeholders.
	*/
	public static function _getDefaultWebfolderInstructions()
	{
		global $lng;
		return $lng->txt('webfolder_instructions_text');
	}

	/**
	* Gets Webfolder mount instructions for the specified webfolder.
	*
	*
	* The following placeholders are currently supported:
	*
	* [WEBFOLDER_TITLE] - the title of the webfolder
	* [WEBFOLDER_URI] - the URL for mounting the webfolder with standard
	*                   compliant WebDAV clients
	* [WEBFOLDER_URI_IE] - the URL for mounting the webfolder with Internet Explorer
	* [WEBFOLDER_URI_KONQUEROR] - the URL for mounting the webfolder with Konqueror
	* [WEBFOLDER_URI_NAUTILUS] - the URL for mounting the webfolder with Nautilus
	* [IF_WINDOWS]...[/IF_WINDOWS] - conditional contents, with instructions for Windows
	* [IF_MAC]...[/IF_MAC] - conditional contents, with instructions for Mac OS X
	* [IF_LINUX]...[/IF_LINUX] - conditional contents, with instructions for Linux
	* [ADMIN_MAIL] - the mailbox address of the system administrator

	* @param String Title of the webfolder
	* @param String Mount URI of the webfolder for standards compliant WebDAV clients
	* @param String Mount URI of the webfolder for IE
	* @param String Mount URI of the webfolder for Konqueror
	* @param String Mount URI of the webfolder for Nautilus
	* @param String Operating system: 'windows', 'unix' or 'unknown'.
	* @param String Operating system flavor: 'xp', 'vista', 'osx', 'linux' or 'unknown'.
	* @return String HTML text.
	*/
	public static function _getWebfolderInstructionsFor($webfolderTitle,
			$webfolderURI, $webfolderURI_IE, $webfolderURI_Konqueror, $webfolderURI_Nautilus,
			$os = 'unknown', $osFlavor = 'unknown')
	{
		global $ilSetting;

		$settings = new ilSetting('file_access');
		$str = $settings->get('custom_webfolder_instructions', '');
		if (strlen($str) == 0 || ! $settings->get('custom_webfolder_instructions_enabled'))
		{
			$str = ilDAVServer::_getDefaultWebfolderInstructions();
		}
		if(is_file('Customizing/clients/'.CLIENT_ID.'/webdavtemplate.htm')){
			$str = fread(fopen('Customizing/clients/'.CLIENT_ID.'/webdavtemplate.htm', "rb"),filesize('Customizing/clients/'.CLIENT_ID.'/webdavtemplate.htm'));
		}
		$str=utf8_encode($str);

		preg_match_all('/(\\d+)/', $webfolderURI, $matches);
		$refID=$matches[0][0];
		
		$str = str_replace("[WEBFOLDER_ID]", $refID, $str);
		$str = str_replace("[WEBFOLDER_TITLE]", $webfolderTitle, $str);
		$str = str_replace("[WEBFOLDER_URI]", $webfolderURI, $str);
		$str = str_replace("[WEBFOLDER_URI_IE]", $webfolderURI_IE, $str);
		$str = str_replace("[WEBFOLDER_URI_KONQUEROR]", $webfolderURI_Konqueror, $str);
		$str = str_replace("[WEBFOLDER_URI_NAUTILUS]", $webfolderURI_Nautilus, $str);
		$str = str_replace("[ADMIN_MAIL]", $ilSetting->get("admin_email"), $str);

		if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false){
			$str = preg_replace('/\[IF_IEXPLORE\]((?:.|\n)*)\[\/IF_IEXPLORE\]/','\1', $str);
		}else{
			$str = preg_replace('/\[IF_NOTIEXPLORE\]((?:.|\n)*)\[\/IF_NOTIEXPLORE\]/','\1', $str);
		}
		
		switch ($os)
		{
			case 'windows' :
				$operatingSystem = 'WINDOWS';
				break;
			case 'unix' :
				switch ($osFlavor)
				{
					case 'osx' :
						$operatingSystem = 'MAC';
						break;
					case 'linux' :
						$operatingSystem = 'LINUX';
						break;
					default :
						$operatingSystem = 'LINUX';
						break;
				}
				break;
			default :
				$operatingSystem = 'UNKNOWN';
				break;
		}

		if ($operatingSystem != 'UNKNOWN')
		{
			$str = preg_replace('/\[IF_'.$operatingSystem.'\]((?:.|\n)*)\[\/IF_'.$operatingSystem.'\]/','\1', $str);
			$str = preg_replace('/\[IF_([A-Z_]+)\](?:(?:.|\n)*)\[\/IF_\1\]/','', $str);
		}
		else
		{
			$str = preg_replace('/\[IF_([A-Z_]+)\]((?:.|\n)*)\[\/IF_\1\]/','\2', $str);
		}
		return $str;
	}

    /**
     * Gets the maximum permitted upload filesize from php.ini in bytes.
     *
     * @return int Upload Max Filesize in bytes.
     */
    private function getUploadMaxFilesize() {
        $val = ini_get('upload_max_filesize');

        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}
// END WebDAV
?>