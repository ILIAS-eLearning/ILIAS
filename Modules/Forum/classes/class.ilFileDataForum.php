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

require_once("classes/class.ilFileData.php");

/**
* This class handles all operations on files for the forum object.
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ingroup ModulesForum
*/
class ilFileDataForum extends ilFileData
{
	/**
	* obj_id
	* @var integer obj_id of exercise object
	* @access private
	*/
	var $obj_id;
	var $pos_id;

	/**
	* path of exercise directory
	* @var string path
	* @access private
	*/
	var $forum_path;

	/**
	* Constructor
	* call base constructors
	* checks if directory is writable and sets the optional obj_id
	* @param integereger obj_id
	* @access	public
	*/
	function ilFileDataForum($a_obj_id = 0,$a_pos_id = 0)
	{
		define('FORUM_PATH', 'forum');
		parent::ilFileData();
		$this->forum_path = parent::getPath()."/".FORUM_PATH;
		
		// IF DIRECTORY ISN'T CREATED CREATE IT
		if(!$this->__checkPath())
		{
			$this->__initDirectory();
		}
		$this->obj_id = $a_obj_id;
		$this->pos_id = $a_pos_id;
	}

	function getObjId()
	{
		return $this->obj_id;
	}
	function getPosId()
	{
		return $this->pos_id;
	}
	function setPosId($a_id)
	{
		$this->pos_id = $a_id;
	}
	/**
	* get forum path 
	* @access	public
	* @return string path
	*/
	function getForumPath()
	{
		return $this->forum_path;
	}

	function getFiles()
	{
		$files = array();
		$dp = opendir($this->forum_path);

		while($file = readdir($dp))
		{
			if(is_dir($file))
			{
				continue;
			}
			list($obj_id,$rest) = split('_',$file,2);
			if($obj_id == $this->obj_id)
			{
				if(!is_dir($this->forum_path.'/'.$file))
				{
					$files[] = array(
						'name'     => $rest,
						'size'     => filesize($this->forum_path.'/'.$file),
						'ctime'    => ilFormat::formatDate(date('Y-m-d H:i:s',filectime($this->forum_path.'/'.$file))));
				}
			}
		}
		closedir($dp);
		return $files;
	}
	function getFilesOfPost()
	{
		$files = array();
		$dp = opendir($this->forum_path);

		while($file = readdir($dp))
		{
			if(is_dir($file))
			{
				continue;
			}
			list($obj_id,$rest) = split('_',$file,2);
			if($obj_id == $this->obj_id)
			{
				list($pos_id,$rest) = split('_',$rest,2);
				if($pos_id == $this->getPosId())
				{
					if(!is_dir($this->forum_path.'/'.$file))
					{
						$files[] = array(
							'name'     => $rest,
							'size'     => filesize($this->forum_path.'/'.$file),
							'ctime'    => ilFormat::formatDate(date('Y-m-d H:i:s',filectime($this->forum_path.'/'.$file))));
					}
				}
			}
		}
		closedir($dp);
		return $files;
	}
	
	public function moveFilesOfPost($a_new_frm_id = 0)
	{
		if((int)$a_new_frm_id)
		{
			$dp = opendir($this->forum_path);
	
			while($file = readdir($dp))
			{
				if(is_dir($file))
				{
					continue;
				}
				list($obj_id,$rest) = split('_',$file,2);
				if($obj_id == $this->obj_id)
				{
					list($pos_id,$rest) = split('_',$rest,2);
					if($pos_id == $this->getPosId())
					{
						if(!is_dir($this->forum_path.'/'.$file))
						{
							@rename($this->forum_path.'/'.$file, $this->forum_path.'/'.$a_new_frm_id.'_'.$this->pos_id.'_'.$rest);
						}
					}
				}
			}
			closedir($dp);
			return true;
		}
		
		return false;
	}

	function ilClone($a_new_obj_id,$a_new_pos_id)
	{
		foreach($this->getFilesOfPost() as $file)
		{
			@copy($this->getForumPath()."/".$this->obj_id."_".$this->pos_id."_".$file["name"],
				  $this->getForumPath()."/".$a_new_obj_id."_".$a_new_pos_id."_".$file["name"]);
		}
		return true;
	}
	function delete()
	{
		foreach($this->getFiles() as $file)
		{
			if(file_exists($this->getForumPath()."/".$this->getObjId()."_".$file["name"]))
			{
				unlink($this->getForumPath()."/".$this->getObjId()."_".$file["name"]);
			}
		}
		return true;
	}

	/**
	* store uploaded file in filesystem
	* @param array HTTP_POST_FILES
	* @access	public
	* @return bool
	*/
	function storeUploadedFile($a_http_post_file)
	{
		// TODO: 
		// CHECK UPLOAD LIMIT
		// 

		if(isset($a_http_post_file) && $a_http_post_file['size'])
		{
			// CHECK IF FILE WITH SAME NAME EXISTS
			$this->__rotateFiles($this->getForumPath().'/'.$this->obj_id.'_'.$this->pos_id."_".$a_http_post_file['name']);
			ilUtil::moveUploadedFile($a_http_post_file['tmp_name'], $a_http_post_file['name'],
				$this->getForumPath().'/'.$this->obj_id.'_'.$this->pos_id."_".
				$a_http_post_file['name']);
			//move_uploaded_file($a_http_post_file['tmp_name'],$this->getForumPath().'/'.$this->obj_id.'_'.$this->pos_id."_".
			//   $a_http_post_file['name']);
		}
		return true;
	}
	/**
	* unlink files: expects an array of filenames e.g. array('foo','bar')
	* @param array filenames to delete
	* @access	public
	* @return string error message with filename that couldn't be deleted
	*/
	function unlinkFiles($a_filenames)
	{
		if(is_array($a_filenames))
		{
			foreach($a_filenames as $file)
			{
				if(!$this->unlinkFile($file))
				{
					return $file;
				}
			}
		}
		return '';
	}
	/**
	* unlink one uploaded file expects a filename e.g 'foo'
	* @param string filename to delete
	* @access	public
	* @return bool
	*/
	function unlinkFile($a_filename)
	{
		if(file_exists($this->forum_path.'/'.$this->obj_id.'_'.$this->pos_id.'_'.$a_filename))
		{
			return unlink($this->forum_path.'/'.$this->obj_id.'_'.$this->pos_id."_".$a_filename);
		}
	}
	/**
	* get absolute path of filename
	* @param string relative path
	* @access	public
	* @return string absolute path
	*/
	function getAbsolutePath($a_path)
	{
		return $this->forum_path.'/'.$this->obj_id.'_'.$this->pos_id."_".$a_path;
	}

	/**
	* check if files exist
	* @param array filenames to check
	* @access	public
	* @return bool
	*/
	function checkFilesExist($a_files)
	{
		if($a_files)
		{
			foreach($a_files as $file)
			{
				if(!file_exists($this->forum_path.'/'.$this->obj_id.'_'.$this->pos_id.'_'.$file))
				{
					return false;
				}
			}
			return true;
		}
		return true;
	}

	// PRIVATE METHODS
	function __checkPath()
	{
		if(!@file_exists($this->getForumPath()))
		{
			return false;
		}
		$this->__checkReadWrite();

		return true;
	}
	/**
	* check if directory is writable
	* overwritten method from base class
	* @access	private
	* @return bool
	*/
	function __checkReadWrite()
	{
		if(is_writable($this->forum_path) && is_readable($this->forum_path))
		{
			return true;
		}
		else
		{
			$this->ilias->raiseError("Forum directory is not readable/writable by webserver",$this->ilias->error_obj->FATAL);
		}
	}
	/**
	* init directory
	* overwritten method
	* @access	public
	* @return string path
	*/
	function __initDirectory()
	{
		if(is_writable($this->getPath()))
		{
			if(mkdir($this->getPath().'/'.FORUM_PATH))
			{
				if(chmod($this->getPath().'/'.FORUM_PATH,0755))
				{
					$this->forum_path = $this->getPath().'/'.FORUM_PATH;
					return true;
				}
			} 
		}
		return false;
	}
	/**
	* rotate files with same name
	* recursive method
	* @param string filename
	* @access	private
	* @return bool
	*/
	function __rotateFiles($a_path)
	{
		if(file_exists($a_path))
		{
			$this->__rotateFiles($a_path.".old");
			return rename($a_path,$a_path.'.old');
		}
		return true;
	}

	//BEGIN DiskQuota: Get used disk space
	/**
	 * Returns the number of bytes used on the harddisk for forum attachments,
	 * by the user with the specified user id.
	 * @param int user id.
	 */
	public static function _getDiskSpaceUsedBy($user_id, $as_string = false)
	{
		// XXX - This method is extremely slow. We should
		// use a cache to speed it up, for example, we should
		// store the disk space used in table forum_attachment.
		global $ilDB, $lng;
		
		$mail_data_dir = ilUtil::getDataDir('filesystem').DIRECTORY_SEPARATOR."forum";
		
		$q = "SELECT top_frm_fk, pos_pk ".
			"FROM frm_posts AS p  ".
			"JOIN frm_data AS d ON d.top_pk=p.pos_top_fk ".
			"WHERE p.pos_usr_id = ".$ilDB->quote($user_id);
		$result_set = $ilDB->query($q);
		$size = 0;
		$count = 0;
		while($row = $result_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$fileDataForum = new ilFileDataForum($row['top_frm_fk'],$row['pos_pk']);
			$filesOfPost = $fileDataForum->getFilesOfPost();
			foreach ($filesOfPost as $attachment)
			{
				$size += $attachment['size'];
				$count++;
			}
			unset($fileDataForum);
			unset($filesOfPost);
		}
		include_once("Modules/File/classes/class.ilObjFileAccess.php");
		return ($as_string) ? 
			$count.' '.$lng->txt('forum_attachments').', '.ilObjFileAccess::_sizeToString($size) : 
			$size;
	}
	//END DiskQuota: Get used disk space
}
