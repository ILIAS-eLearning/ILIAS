<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/FileSystem/classes/class.ilFileData.php");

/**
* This class handles all operations on files for the forum object.
*  
* @author	Stefan Meyer <meyer@leifos.com>
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
	public function __construct($a_obj_id = 0,$a_pos_id = 0)
	{
		define('FORUM_PATH', 'forum');
		parent::__construct();
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
			if( is_dir( $this->forum_path . $file ) )
			{
				continue;
			}
			list($obj_id,$rest) = split('_',$file,2);
			if($obj_id == $this->obj_id)
			{
				if(!is_dir($this->forum_path.'/'.$file))
				{
					$files[] = array(
						'path' 	   => $this->forum_path.'/'.$file,
						'md5'	   => md5($this->obj_id.'_'.$this->pos_id.'_'.$rest),
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
			if( is_dir( $this->forum_path.$file ) )
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
							'path' 	   => $this->forum_path.'/'.$file,
							'md5'	   => md5($this->obj_id.'_'.$this->pos_id.'_'.$rest),
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
				if( is_dir( $this->forum_path . $file ) )
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
	 * 
	 * Store uploaded files in filesystem
	 * 
	 * @param	array	$files	Copy of $_FILES array,
	 * @access	public
	 * @return	bool
	 * 
	 */
	function storeUploadedFile($files)
	{
		if(isset($files['name']) && is_array($files['name']))
		{
			foreach($files['name'] as $index => $name)
			{
				// remove trailing '/'
				while(substr($name, -1) == '/')
				{
					$name = substr($name, 0, -1);
				}	
				$filename = ilUtil::_sanitizeFilemame($name);				
				$temp_name = $files['tmp_name'][$index];
				$error = $files['error'][$index];
				
				if(strlen($filename) && strlen($temp_name) && $error == 0)
				{				
					$path = $this->getForumPath().'/'.$this->obj_id.'_'.$this->pos_id.'_'.$filename;
					
					$this->__rotateFiles($path);
					ilUtil::moveUploadedFile($temp_name, $filename, $path);				
				}
			}
			
			return true;
		}
		else if(isset($files['name']) && is_string($files['name']))
		{
			// remove trailing '/'
			while(substr($files['name'], -1) == '/')
			{
				$files['name'] = substr($files['name'], 0, -1);
			}			
			$filename = ilUtil::_sanitizeFilemame($files['name']);
			$temp_name = $files['tmp_name'];
			
			$path = $this->getForumPath().'/'.$this->obj_id.'_'.$this->pos_id.'_'.$filename;
			
			$this->__rotateFiles($path);
			ilUtil::moveUploadedFile($temp_name, $filename, $path);
			
			return true;
		}
		
		return false;
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
	* get file data of a specific attachment
	* @param string md5 encrypted filename
	* @access public
	* @return array filedata
	*/
	public function getFileDataByMD5Filename($a_md5_filename)
	{
		$files = ilUtil::getDir( $this->forum_path );
		foreach((array)$files as $file)
		{
			if($file['type'] == 'file' && md5($file['entry']) == $a_md5_filename)
			{
				return array(
					'path' => $this->forum_path.'/'.$file['entry'],
					'filename' => $file['entry'],
					'clean_filename' => str_replace($this->obj_id.'_'.$this->pos_id.'_', '', $file['entry'])
				);
			}
		}
		
		return false;
	}
	
	/**
	* get file data of a specific attachment
	* @param string|array md5 encrypted filename or array of multiple md5 encrypted files
	* @access public
	* @return boolean status
	*/
	function unlinkFilesByMD5Filenames($a_md5_filename)
	{
		$files = ilUtil::getDir( $this->forum_path );
		if(is_array($a_md5_filename))
		{
			foreach((array)$files as $file)
			{
				if($file['type'] == 'file' && in_array(md5($file['entry']), $a_md5_filename))
				{
					unlink( $this->forum_path.'/'.$file['entry'] );
				}
			}
			
			return true;
		}
		else
		{
			foreach((array)$files as $file)
			{
				if($file['type'] == 'file' && md5($file['entry']) == $a_md5_filename)
				{
					return unlink( $this->forum_path.'/'.$file['entry'] );
				}
			}
		}
		
		return false;
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

}
