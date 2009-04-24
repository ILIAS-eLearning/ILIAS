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

include_once('Modules/Session/classes/class.ilFSStorageSession.php');

/**
* class ilEvent
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* @version $Id$
* 
* @extends Object
*/


class ilSessionFile
{
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $event_id = null;
	var $file_id = null;

	private $fss_storage = null;

	function ilSessionFile($a_file_id = null)
	{
		global $ilErr,$ilDB,$lng;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->file_id = $a_file_id;
		$this->__read();
	}

	function setFileId($a_id)
	{
		$this->file_id = $a_id;
	}
	function getFileId()
	{
		return $this->file_id;
	}

	function getSessionId()
	{
		return $this->event_id;
	}
	function setSessionId($a_event_id)
	{
		$this->event_id = $a_event_id;
	}

	function setFileName($a_name)
	{
		$this->file_name = $a_name;
	}
	function getFileName()
	{
		return $this->file_name;
	}
	function setFileType($a_type)
	{
		$this->file_type = $a_type;
	}
	function getFileType()
	{
		return $this->file_type;
	}
	function setFileSize($a_size)
	{
		$this->file_size = $a_size;
	}
	function getFileSize()
	{
		return $this->file_size;
	}
	function setTemporaryName($a_name)
	{
		$this->tmp_name = $a_name;
	}
	function getTemporaryName()
	{
		return $this->tmp_name;
	}
	function setErrorCode($a_code)
	{
		$this->error_code = $a_code;
	}
	function getErrorCode()
	{
		return $this->error_code;
	}
	
	function getAbsolutePath()
	{
		return $this->fss_storage->getAbsolutePath()."/".$this->getFileId();
	}

	function validate()
	{
		switch($this->getErrorCode())
		{
			case UPLOAD_ERR_INI_SIZE:
				$this->ilErr->appendMessage($this->lng->txt('file_upload_ini_size'));
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$this->ilErr->appendMessage($this->lng->txt('file_upload_form_size'));
				break;

			case UPLOAD_ERR_PARTIAL:
				$this->ilErr->appendMessage($this->lng->txt('file_upload_only_partial'));
				break;

			case UPLOAD_ERR_NO_TMP_DIR:
				$this->ilErr->appendMessage($this->lng->txt('file_upload_no_tmp_dir'));
				break;

			#case UPLOAD_ERR_CANT_WRITE:
			#	$this->ilErr->appendMessage($this->lng->txt('file_upload_no_write'));
			#	break;

			case UPLOAD_ERR_OK:
			case UPLOAD_ERR_NO_FILE:
			default:
				return true;
		}
	}
	
	/**
	 * Clone files
	 *
	 * @access public
	 * @param int new event_id
	 * 
	 */
	public function cloneFiles($a_target_event_id)
	{
	 	$file = new ilSessionFile();
	 	$file->setSessionId($a_target_event_id);
	 	$file->setFileName($this->getFileName());
	 	$file->setFileType($this->getFileType());
	 	$file->setFileSize($this->getFileSize());
	 	$file->create(false);
	 	
	 	// Copy file
		$source = new ilFSStorageSession($this->getSessionId());
		$source->copyFile($this->getAbsolutePath(),$file->getAbsolutePath());	 	
	}

	function create($a_upload = true)
	{
		global $ilDB;
		
		if($this->getErrorCode() != 0)
		{
			return false;
		}

		$next_id = $ilDB->nextId('event_file');
		$query = "INSERT INTO event_file (file_id,event_id,file_name,file_size,file_type) ".
			"VALUES( ".
			$ilDB->quote($next_id ,'integer').", ".
			$ilDB->quote($this->getSessionId() ,'integer').", ".
			$ilDB->quote($this->getFileName() ,'text').", ".
			$ilDB->quote($this->getFileSize() ,'integer').", ".
			$ilDB->quote($this->getFileType() ,'text')." ".
			")";
		
		$res = $ilDB->manipulate($query);
		$this->setFileId($next_id);

		$this->fss_storage = new ilFSStorageSession($this->getSessionId());
		$this->fss_storage->createDirectory();

		if($a_upload)
		{
			// now create file
			ilUtil::moveUploadedFile($this->getTemporaryName(),
				$this->getFileName(),
				$this->fss_storage->getAbsolutePath().'/'.$this->getFileId());
			
		}

		return true;
	}

	function delete()
	{
		global $ilDB;
		
		// Delete db entry
		$query = "DELETE FROM event_file ".
			"WHERE file_id = ".$ilDB->quote($this->getFileId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		// Delete file
		$this->fss_storage->deleteFile($this->getAbsolutePath());
		return true;
	}
		
	function _deleteByEvent($a_event_id)
	{
		global $ilDB;

		// delete all event ids and delete assigned files
		$query = "DELETE FROM event_file ".
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')."";
		$res = $ilDB->manipulate($query);

		#$this->fss_storage->delete();
		return true;
	}

	function _readFilesByEvent($a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_file ".
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')."";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$files[] =& new ilSessionFile($row->file_id);
		}
		return is_array($files) ? $files : array();
	}

	function __read()
	{
		global $ilDB;
		
		if(!$this->file_id)
		{
			return true;
		}

		// read file data
		$query = "SELECT * FROM event_file WHERE file_id = ".$ilDB->quote($this->file_id ,'integer')."";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setFileName($row->file_name);
			$this->setFileSize($row->file_size);
			$this->setFileType($row->file_type);
			$this->setSessionId($row->event_id);
		}
		$this->fss_storage = new ilFSStorageSession($this->getSessionId());
		return true;
	}
		
}
?>