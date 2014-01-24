<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/FileSystem/classes/class.ilFileSystemStorage.php");

/** 
 * 
 * 
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilFSStoragePreview extends ilFileSystemStorage
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int storage type
	 * @param bool En/Disable automatic path conversion. If enabled files with id 123 will be stored in directory files/1/file_123
	 * @param int object id of container (e.g file_id or mob_id)
	 * 
	 */
	public function __construct($a_container_id = 0)
	{
		parent::__construct(self::STORAGE_WEB, true, $a_container_id);
	}
	
	/**
	 * Get directory name. E.g for files => file
	 * Only relative path, no trailing slash
	 * '_<obj_id>' will be appended automatically
	 *
	 * @access protected
	 * 
	 * @return string directory name
	 */
	protected function getPathPostfix()
	{
		return "preview";
	}
	
	/**
	 * Get path prefix. Prefix that will be prepended to the path
	 * No trailing slash. E.g ilFiles for files
	 *
	 * @access protected
	 * 
	 * @return string path prefix e.g files
	 */
	protected function getPathPrefix()
	{
		return "previews";
	}
}
?>