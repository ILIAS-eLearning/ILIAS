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

include_once('Modules/Course/classes/class.ilFSStorageCourse.php');

/**
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
*/


class ilCourseFile
{
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $course_id = null;
	var $file_id = null;
	
	private $fss_storage = null;

	function ilCourseFile($a_file_id = null)
	{
		global $ilErr,$ilDB,$lng;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->file_id = $a_file_id;
		$this->__read();
	}
	
	/**
	 * Clone course files
	 *
	 * @access public
	 * @static
	 *
	 * @param int source id
	 * @param int target_id
	 */
	public static function _cloneFiles($a_source_id,$a_target_id)
	{
		$source = new ilFSStorageCourse($a_source_id);

		foreach(ilCourseFile::_readFilesByCourse($a_source_id) as $file_obj)
		{
			$new_file = new ilCourseFile();
			$new_file->setCourseId($a_target_id);
			$new_file->setFileName($file_obj->getFileName());
			$new_file->setFileSize($file_obj->getFileSize());
			$new_file->setFileType($file_obj->getFileType());
			$new_file->create(false);

			$target = new ilFSStorageCourse($a_target_id);
			$target->initInfoDirectory();
			$source->copyFile($file_obj->getAbsolutePath(),$new_file->getAbsolutePath());
		}
	}

	function setFileId($a_id)
	{
		$this->file_id = $a_id;
	}
	function getFileId()
	{
		return $this->file_id;
	}

	function getCourseId()
	{
		return $this->course_id;
	}
	function setCourseId($a_course_id)
	{
		$this->course_id = $a_course_id;
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
		if(is_object($this->fss_storage))
		{
			return $this->fss_storage->getInfoDirectory().'/'.$this->getFileId();
		}
		return false;
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

				// not possible with php 4
			#case UPLOAD_ERR_CANT_WRITE:
			#	$this->ilErr->appendMessage($this->lng->txt('file_upload_no_write'));
			#	break;

			case UPLOAD_ERR_OK:
			case UPLOAD_ERR_NO_FILE:
			default:
				return true;
		}
	}

	function create($a_upload = true)
	{
		global $ilDB;
		
		if($this->getErrorCode() != 0)
		{
			return false;
		}

		$next_id = $ilDB->nextId('crs_file');
		$query = "INSERT INTO crs_file (file_id,course_id,file_name,file_size,file_type) ".
			"VALUES( ".
			$ilDB->quote($next_id,'integer').", ".
			$ilDB->quote($this->getCourseId(),'integer').", ".
			$ilDB->quote($this->getFileName(),'text').", ".
			$ilDB->quote($this->getFileSize(),'integer').", ".
			$ilDB->quote($this->getFileType(),'text')." ".
			")";
		$res = $ilDB->manipulate($query);
		$this->setFileId($next_id);

		$this->fss_storage = new ilFSStorageCourse($this->getCourseId());
		$this->fss_storage->initInfoDirectory();

		if($a_upload)
		{
			// now create file
			ilUtil::moveUploadedFile($this->getTemporaryName(),
				$this->getFileName(),
				$this->fss_storage->getInfoDirectory().'/'.$this->getFileId());
			
		}
		return true;
	}

	function delete()
	{
		global $ilDB;
		
		// Delete db entry
		$query = "DELETE FROM crs_file ".
			"WHERE file_id = ".$ilDB->quote($this->getFileId(),'integer')."";
		$res = $ilDB->manipulate($query);

		// Delete file
		unlink($this->getAbsolutePath());

		return true;
	}
		
	function _deleteByCourse($a_course_id)
	{
		global $ilDB;

		// delete all course ids and delete assigned files
		$query = "DELETE FROM crs_file ".
			"WHERE course_id = ".$ilDB->quote($a_course_id,'integer')."";
		$res = $ilDB->manipulate($query);

		return true;
	}

	function &_readFilesByCourse($a_course_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_file ".
			"WHERE course_id = ".$ilDB->quote($a_course_id,'integer')."";

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$files[] =& new ilCourseFile($row->file_id);
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
		$query = "SELECT * FROM crs_file WHERE file_id = ".$ilDB->quote($this->file_id,'integer');
		$res = $this->db->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$this->setFileName($row->file_name);
			$this->setFileSize($row->file_size);
			$this->setFileType($row->file_type);
			$this->setCourseId($row->course_id);
		}
		$this->fss_storage = new ilFSStorageCourse($this->getCourseId());
		return true;
	}
		
}
?>