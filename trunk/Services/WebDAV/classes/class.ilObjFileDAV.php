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

require_once "class.ilObjectDAV.php";

require_once './Services/History/classes/class.ilHistory.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once "Modules/File/classes/class.ilObjFile.php";

// NOTE: I changed class.ilObjFile.php to support the functionality needed by this handler.

/**
* Class ilObjFileDAV
*
* Handles DAV requests on a file object.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilObjFileDAV extends ilObjectDAV
{
	/** 
	* Constructor
	*
	* @param refid A refid to the object.
	*/
	function ilObjFileDAV($refid, $obj = null) 
	{
               $this->ilObjectDAV($refid, $obj);
              
               // Set debug to true, if you want to get debug output for this
               // object
               //$this->isDebug = true;
	}
	
	/**
	 * Initializes the object after it has been converted from the NULL type.
	 * We create all the additonal object data that is needed, to make the object work.
	 *
         * @return void.
	 */
	function initFromNull()
	{
		$this->obj->setFileName($this->getResourceName());
		$this->obj->setFileType($this->obj->guessFileType());
		$this->write();
		$this->obj->setPermissions($this->getRefId());
	}
	/**
	 * Creates a new version of the object.
	 * Only objects which support versioning need to implement this method.
	 */
	function createNewVersion() {
		$this->obj->setVersion($this->obj->getVersion() + 1);
		ilHistory::_createEntry($this->obj->getId(), "replace",
			$this->obj->getFileName().",".$this->obj->getVersion());
	}

	
	/**
	 * Returns the display name of this object.
	 * Precondition: Object must have been read.
         * @return String.
	 * FIXME - Method deactivated. We don't display the file name as 
	 *         display name. Because the file name is not necessarily 
	 *         known to the user.
	 * /
	function getDisplayName()
	{
		return $this->obj->getFileName();
	}*/
	
	/**
	 * Returns the DAV resource type of this object.
	 * 
         * @return String "collection" or "".
	 */
	function getResourceType()
	{
		return "";
	}

	/**
	 * Returns the mime type of the content of this object.
         * @return String.
	 */
	function getContentType()
	{
		//return $this->obj->getFileType();
		return  $this->obj->guessFileType();
	}
	/**
	 * Sets the mime type of the content of this object.
         * @param String.
	 */
	function setContentType($type)
	{
		$this->obj->setFileType($type);
	}
	/**
	 * Sets the length (in bytes) of the content of this object.
     * @param Integer.
	 */
	function setContentLength($length)
	{
		$this->writeLog('setContentLength('.$length.')');
		$this->obj->setFileSize($length);
	}
	/**
	 * Returns the number of bytes of the content.
         * @return int.
	 */
	function getContentLength()
	{
		return ilObjFile::_lookupFileSize($this->obj->getId());
	}
	/**
	 * Returns the content of the object as a stream.
     * @return Stream or null, if the content does not support streaming.
	 */
	function getContentStream()
	{
		$file = $this->obj->getFile();
		return (file_exists($file)) ? fopen($file,'r') : null;
	}
	/**
	 * Returns an output stream to the content.
     * @return Stream or null, if the content does not support streaming.
	 */
	function getContentOutputStream()
	{
		$file = $this->obj->getFile();
		$parent = dirname($file);
		if (! file_exists($parent))
		{
			ilUtil::makeDirParents($parent);
		}
		
		return fopen($file,'w');
	}
	/**
	 * Returns the length of the content output stream.
         * <p>
         * This method is used by the ilDAVServer, if a PUT operation
         * has been performed for which the client did not specify the
         * content length.
         * 
	 * @param Integer.
	 */
	function getContentOutputStreamLength()
	{
		$file = $this->obj->getFile();  
		return file_exists($file) ? filesize($file) : 0;
                
	}
	/**
	 * Returns the content of the object as a byte array.
         * @return Array, String. Return null if the content can not be delivered
	 * as data.
	 */
	function getContentData()
	{
		return null;
	}
	/**
	 * Reads the object data.
         * @return void.
	 */
	function read()
	{
		if (is_null($this->obj))
		{
			$this->obj = &new ilObjFile($this->getRefId(),true);
			$this->obj->read();
		}
	}
	/**
	 * Writes the object data.
         * @return void.
	 */
	function write()
	{
		$this->isNewFile = $this->obj->getVersion() == 0;
		if ($this->isNewFile)
		{
			$this->obj->setVersion(1);
		}
		parent::write();
		/*
		ilHistory::_createEntry($this->getObjectId(), 'update', '', '','', true);
		*/
	}
}
// END WebDAV
?>
