<?php
// BEGIN WebDAV
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
* Class ilObjFileAccessSettings*
*
* This class encapsulates accesses to settings which are relevant for file
* accesses to ILIAS.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
*
* @version $Id: class.ilObjFileAccessSettings.php 15697 2008-01-08 20:04:33Z hschottm $
*
* @extends ilObject
* @package WebDAV
*/

include_once "classes/class.ilObject.php";

class ilObjFileAccessSettings extends ilObject
{
	/**
	 * Boolean property. Set this to true, to enable WebDAV access to files.
	 */
	private $webdavEnabled;

	/**
	 * String property. Mount instructions for WebDAV access to files.
	 */
	private $webfolderMountInstructions;

	/**
	 * String property. Contains a list of file extensions separated by space.
	 * Files with a matching extension are displayed inline in the browser.
	 * Non-matching files are offered for download to the user.
	 */
	private $inlineFileExtensions;

	/**
	* Constructor
	*
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function ilObjFileAccessSettings($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "facs";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* Sets the webdavEnabled property.
	* 
	* @param	boolean	new value
	* @return	void
	*/
	public function setWebdavEnabled($newValue)
	{
		$this->webdavEnabled = $newValue;
	}
	/**
	* Gets the webdavEnabled property.
	* 
	* @return	boolean	value
	*/
	public function isWebdavEnabled()
	{
		return $this->webdavEnabled;
	}

	/**
	* Sets the webfolderMountInstructions property.
	* 
	* The mount instructions consist of HTML text, with placeholders.
	* The following placeholders are currently supported:
	*
	* [WEBFOLDER_TITLE] - the title of the webfolder
	* [WEBFOLDER_URL] - the URL for mounting the webfolder
	* [IF_WINDOWS]...[/IF_WINDOWS] - conditional contents, with instructions for Windows
	* [IF_MAC]...[/IF_MAC] - conditional contents, with instructions for Mac OS X
	* [IF_LINUX]...[/IF_LINUX] - conditional contents, with instructions for Linux
	* [ADMIN_MAIL] - the mailbox address of the system administrator
	* 
	* @param	string	HTML text with placeholders.
	* @return	void
	*/
	public function setWebfolderMountInstructions($newValue)
	{
		$this->webfolderMountInstructions = $newValue;
	}
	/**
	* Gets the webfolderMountInstructions property.
	* 
	* @return	boolean	value
	*/
	public function getWebfolderMountInstructions()
	{
		if (strlen($this->webfolderMountInstructions) == 0) 
		{
			require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
			$this->webfolderMountInstructions = ilDAVServer::_getDefaultWebfolderMountInstructions();
		}
		return $this->webfolderMountInstructions;
	}


	/**
	* Sets the inlineFileExtensions property.
	* 
	* @param	string	new value, a space separated list of filename extensions.
	* @return	void
	*/
	public function setInlineFileExtensions($newValue)
	{
		$this->inlineFileExtensions = $newValue;
	}
	/**
	* Gets the inlineFileExtensions property.
	* 
	* @return	boolean	value
	*/
	public function getInlineFileExtensions()
	{
		return $this->inlineFileExtensions;
	}

	/**
	* create
	*
	* note: title, description and type should be set when this function is called
	*
	* @return	integer		object id
	*/
	public function create()
	{
		parent::create();
		$this->write();
	}
	/**
	* update object in db
	*
	* @return	boolean	true on success
	*/
	public function update()
	{
		parent::update();
		$this->write();
	}
	/**
	* write object data into db
	* @param	boolean
	*/
	private function write()
	{
		global $ilClientIniFile;

		if (! $ilClientIniFile->groupExists('file_access'))
		{
			$ilClientIniFile->addGroup('file_access');
		}
		$ilClientIniFile->setVariable('file_access', 'webdav_enabled', $this->webdavEnabled ? '1' : '0');
		$ilClientIniFile->write();

		require_once 'Services/Administration/classes/class.ilSetting.php';
		$settings = new ilSetting('file_access');
		$settings->set('inline_file_extensions', $this->inlineFileExtensions);
		$settings->set('webfolder_mount_instructions', $this->webfolderMountInstructions);
	}
	/**
	* read object data from db into object
	* @param	boolean
	*/
	public function read($a_force_db = false)
	{
		parent::read($a_force_db);

		global $ilClientIniFile;
		$this->webdavEnabled = $ilClientIniFile->readVariable('file_access','webdav_enabled') == '1';

		require_once 'Services/Administration/classes/class.ilSetting.php';
		$settings = new ilSetting('file_access');
		$this->inlineFileExtensions = $settings->get('inline_file_extensions','');
		$this->webfolderMountInstructions = $settings->get('webfolder_mount_instructions', '');
	}



} // END class.ilObjFileAccessSettings
// END WebDAV
?>
