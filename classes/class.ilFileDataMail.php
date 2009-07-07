<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* This class handles all operations on files (attachments) in directory ilias_data/mail
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
require_once("classes/class.ilFileData.php");
				
class ilFileDataMail extends ilFileData
{
	/**
	* user id
	* @var integer user_id
	* @access private
	*/
	var $user_id;

	/**
	* path of mail directory
	* @var string path
	* @access private
	*/
	var $mail_path;

	var $mail_maxsize_attach;

	/**
	* Constructor
	* call base constructors
	* checks if directory is writable and sets the optional user_id
	* @param integereger user_id
	* @access	public
	*/
	function ilFileDataMail($a_user_id = 0)
	{
		define('MAILPATH','mail');
		parent::ilFileData();
		$this->mail_path = parent::getPath()."/".MAILPATH;
		$this->checkReadWrite();
		$this->user_id = $a_user_id;


		$this->__initAttachmentMaxSize();
	}

	/**
	* init directory
	* overwritten method
	* @access	public
	* @return string path
	*/
	function initDirectory()
	{
		if(is_writable($this->getPath()))
		{
			if(mkdir($this->getPath().'/'.MAILPATH))
			{
				if(chmod($this->getPath().'/'.MAILPATH,0755))
				{
					$this->mail_path = $this->getPath().'/'.MAILPATH;
					return true;
				}
			} 
		}
		return false;
	}
	function getUploadLimit()
	{
		return $this->mail_maxsize_attach_message;
	}

	/**
	* get mail path 
	* @access	public
	* @return string path
	*/
	function getMailPath()
	{
		return $this->mail_path;
	}
	
	/**
	* get the path of a specific attachment
	* @param string md5 encrypted filename
	* @param integer mail_id
	* @access	public
	* @return string path
	*/
	public function getAttachmentPathByMD5Filename($a_filename,$a_mail_id)
	{
		global $ilDB;
		
/*		$query = "SELECT path FROM mail_attachment ".
			"WHERE mail_id = ".$ilDB->quote($a_mail_id)."";
		
		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
		$path = $this->getMailPath().'/'.$row->path;
*/
		$query = $ilDB->query("SELECT path FROM mail_attachment 
				  WHERE mail_id = ".$ilDB->quote($a_mail_id,'integer')."");
		
		$rel_path = "";
		while($row = $ilDB->fetchObject($query))
		{
			$rel_path = $row->path;
			$path = $this->getMailPath().'/'.$row->path;

		}

		$files = ilUtil::getDir($path);
		foreach((array)$files as $file)
		{
			if($file['type'] == 'file' && md5($file['entry']) == $a_filename)
			{
				return array(
					'path' => $this->getMailPath().'/'.$rel_path.'/'.$file['entry'],
					'filename' => $file['entry']
				);
			}
		}
		return '';
	}
	

	/**
	* get the path of a specific attachment
	* @param string filename
	* @param integer mail_id
	* @access	public
	* @return string path
	*/
	function getAttachmentPath($a_filename,$a_mail_id)
	{
		global $ilDB;
		
/*		$query = "SELECT path FROM mail_attachment ".
			"WHERE mail_id = ".$ilDB->quote($a_mail_id)."";
		
		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
		$path = $this->getMailPath().'/'.$row->path.'/'.$a_filename;
*/
		$query = $ilDB->query("SELECT path FROM mail_attachment ".
			"WHERE mail_id = ".$ilDB->quote($a_mail_id, 'integer')."");

		while($row = $ilDB->fetchObject($query))
		{
			$path = $this->getMailPath().'/'.$row->path.'/'.$a_filename;			
		}
			
		if(file_exists($path))
		{
			if(is_readable($path))
			{
				return $path;
			}
			return '';
		}
		return '';
	}
	/**
	* adopt attachments (in case of forwarding a mail)
	* @param array attachments
	* @param integer mail_id
	* @access	public
	* @return string error message
	*/
	function adoptAttachments($a_attachments,$a_mail_id)
	{
		if(is_array($a_attachments))
		{
			foreach($a_attachments as $file)
			{
				$path = $this->getAttachmentPath($file,$a_mail_id);
				if(!copy($path,$this->getMailPath().'/'.$this->user_id.'_'.$file))
				{
					return "ERROR: $this->getMailPath().'/'.$this->user_id.'_'.$file cannot be created";
				}
			}
		}
		else
		{
			return "ARRAY REQUIRED";
		}
		return '';
	}

	/**
	* check if directory is writable
	* overwritten method from base class
	* @access	private
	* @return bool
	*/
	function checkReadWrite()
	{
		if(is_writable($this->mail_path) && is_readable($this->mail_path))
		{
			return true;
		}
		else
		{
			$this->ilias->raiseError("Mail directory is not readable/writable by webserver: ".$this->mail_path,$this->ilias->error_obj->FATAL);
		}
	}
	/**
	* get all attachments of a specific user
	* @access	public
	* @return bool
	*/
	function getUserFilesData()
	{
		// FIRST GET FILES OF USER IN BASE DIRECTORY
		return $files = $this->getUnsentFiles();
	}

	/**
	* get all files which are not sent
	* find them in directory data/mail/
	* @access	private
	* @return bool
	*/
	function getUnsentFiles()
	{
		$files = array();
		$dp = opendir($this->mail_path);

		while($file = readdir($dp))
		{
			if(is_dir($file))
			{
				continue;
			}
			list($uid,$rest) = split('_',$file,2);
			if($uid == $this->user_id)
			{
				if(!is_dir($this->mail_path.'/'.$file))
				{
					$files[] = array(
						'name'     => $rest,
						'size'     => filesize($this->mail_path.'/'.$file),
						'ctime'    => ilFormat::formatDate(date('Y-m-d H:i:s',filectime($this->mail_path.'/'.$file))));
				}
			}
		}
		closedir($dp);
		return $files;
	}
	/**
	* store uploaded file in filesystem
	* @param array HTTP_POST_FILES
	* @access	public
	* @return integer 0 on success
	*/
	function storeUploadedFile($a_http_post_file)
	{
		if($a_http_post_file["size"] >= $this->mail_maxsize_attach)
		{
			return 1;
		}

		if(isset($a_http_post_file) && $a_http_post_file['size'])
		{
			$a_http_post_file['name'] = ilUtil::_sanitizeFilemame($a_http_post_file['name']);
			
			// CHECK IF FILE WITH SAME NAME EXISTS
			$this->rotateFiles($this->getMailPath().'/'.$this->user_id.'_'.$a_http_post_file['name']);
			
			ilUtil::moveUploadedFile($a_http_post_file['tmp_name'],
				$a_http_post_file['name'], $this->getMailPath().'/'.$this->user_id.'_'.
				$a_http_post_file['name']);

			//move_uploaded_file($a_http_post_file['tmp_name'],$this->getMailPath().'/'.$this->user_id.'_'.
			//				   $a_http_post_file['name']);
			return 0;
		}
		return 1;
	}

	/**
	* Copy files in mail directory. This is used for sending ILIAS generated mails with attachments
	* @param array Array with files. Absolute path required
	* @access	public
	* @return 
	*/
	function copyAttachmentFile($a_abs_path,$a_new_name)
	{
		@copy($a_abs_path,$this->getMailPath()."/".$this->user_id."_".$a_new_name);
		
		return true;
	}
		


	/**
	* rotate files with same name
	* recursive method
	* @param string filename
	* @access	private
	* @return bool
	*/
	function rotateFiles($a_path)
	{
		if(file_exists($a_path))
		{
			$this->rotateFiles($a_path.".old");
			return rename($a_path,$a_path.'.old');
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
		if(file_exists($this->mail_path.'/'.basename($this->user_id.'_'.$a_filename)))
		{
			return unlink($this->mail_path.'/'.basename($this->user_id.'_'.$a_filename));
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
		return $this->mail_path.'/'.$this->user_id.'_'.$a_path;
	}

	/**
	* save all attachment files in a specific mail directory .../mail/<calculated_path>/mail_<mail_id>_<user_id>/...
	* @param integer mail id of mail in sent box
	* @param array filenames to save
	* @access	public
	* @return string error message
	*/
	function saveFiles($a_mail_id,$a_attachments)
	{
		if(!$a_mail_id)
		{
			return "INTERNAL HERE ERROR: No valid mail_id given";
		}
		if(is_array($a_attachments))
		{
			foreach($a_attachments as $attachment)
			{
				if(!$this->saveFile($a_mail_id,$attachment))
				{
					return $attachment;
				}
			}
		}
		else
		{
			return "ARRAY REQUIRED";
		}
		return '';
	}
	
	public static function getStorage($a_mail_id, $a_usr_id)
	{
		static $fsstorage_cache = array();
		
		if(!is_object($fsstorage_cache[$a_mail_id][$a_usr_id]))
		{
			include_once 'Services/Mail/classes/class.ilFSStorageMail.php';
			$fsstorage_cache[$a_mail_id][$a_usr_id] = new ilFSStorageMail($a_mail_id, $a_usr_id);
		} 
		
		return $fsstorage_cache[$a_mail_id][$a_usr_id];
	}
	
	/**
	* save attachment file in a specific mail directory .../mail/<calculated_path>/mail_<mail_id>_<user_id>/...
	* @param integer mail id of mail in sent box
	* @param array filenames to save
	* @access	public
	* @return bool
	*/
	function saveFile($a_mail_id,$a_attachment)
	{
		$oStorage = self::getStorage($a_mail_id, $this->user_id);
		$oStorage->create();
		$storage_directory = $oStorage->getAbsolutePath();
				
		if(@!is_dir($storage_directory))
		{
			return false;
		}
		
		return copy($this->mail_path.'/'.$this->user_id.'_'.$a_attachment,
					$storage_directory.'/'.$a_attachment);
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
				if(!file_exists($this->mail_path.'/'.$this->user_id.'_'.$file))
				{
					return false;
				}
			}
			return true;
		}
		return true;
	}
	/**
	* assign attachments to mail directory
	* @param integer mail_id
	* @param integer key for directory assignment
	* @access	public
	* @return bool
	*/
	function assignAttachmentsToDirectory($a_mail_id,$a_sent_mail_id)
	{
		global $ilDB;
		
/*		$query = "INSERT INTO mail_attachment ".
			"SET mail_id = ".$ilDB->quote($a_mail_id).", ".
			"path = ".$ilDB->quote($this->user_id."_".$a_sent_mail_id)." ";
		$res = $this->ilias->db->query($query);
*/

		$oStorage = self::getStorage($a_sent_mail_id, $this->user_id);
		$res = $ilDB->manipulateF('
			INSERT INTO mail_attachment 
			( mail_id, path) VALUES (%s, %s)',
			array('integer', 'text'),
			array($a_mail_id, $oStorage->getRelativePathExMailDirectory())
		); 
				
	}
	/**
	* dassign attachments from mail directory
	* @param integer mail_id
	* @access	public
	* @return bool
	*/
	function deassignAttachmentFromDirectory($a_mail_id)
	{
		global $ilDB;
		// IF IT'S THE LAST MAIL CONTAINING THESE ATTACHMENTS => DELETE ATTACHMENTS
		$res = $ilDB->query("SELECT path FROM mail_attachment
				WHERE mail_id = ".$ilDB->quote($a_mail_id,'integer'));
	
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$path = $row->path;
		}
		if($path)
		{
			$res = $ilDB->query("SELECT COUNT(mail_id) count_mail_id FROM mail_attachment 
					WHERE path = ".$ilDB->quote($path,'text')) ;
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$cnt_mail_id = $row->count_mail_id;
			}
			if($cnt_mail_id == 1)
			{
				$this->__deleteAttachmentDirectory($path);
			}
		}

		$res = $ilDB->manipulateF("DELETE FROM mail_attachment 
				WHERE mail_id = %s",
				array('integer'),
				array($a_mail_id));
		return true;
	}

	function __deleteAttachmentDirectory($a_rel_path)
	{
		ilUtil::delDir($this->mail_path."/".$a_rel_path);		
		
		return true;
	}
	function __initAttachmentMaxSize()
	{

		$this->mail_maxsize_attach = $this->ilias->getSetting("mail_maxsize_attach") ? 
			$this->ilias->getSetting("mail_maxsize_attach") * 1024 : 1024 * 1024 * 64;

		$this->mail_maxsize_attach_message = $this->ilias->getSetting("mail_maxsize_attach") ? 
			$this->ilias->getSetting("mail_maxsize_attach") :
			ini_get("upload_max_filesize");
	}

	//BEGIN DiskQuota: Get used disk space
	/**
	 * Returns the number of bytes used on the harddisk for mail attachments,
	 * by the user with the specified user id.
	 * @param int user id.
	 */
	public static function _getDiskSpaceUsedBy($user_id, $as_string = false)
	{
		// XXX - This method is extremely slow. We should
		// use a cache to speed it up, for example, we should
		// store the disk space used in table mail_attachment.
		global $ilDB, $lng;
		
		$mail_data_dir = ilUtil::getDataDir('filesystem').DIRECTORY_SEPARATOR."mail";
		
		$result_set = $ilDB->query("SELECT path FROM mail_attachment ma ".
			"JOIN mail m ON ma.mail_id = m.mail_id ".
			"WHERE m.user_id = ".$ilDB->quote($user_id, 'integer'));
	
		$size = 0;
		$count = 0;
		while($row = $result_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$attachment_path = $mail_data_dir.DIRECTORY_SEPARATOR.$row['path'];
			$attachment_size = ilUtil::dirsize($attachment_path);
			if ($attachment_size != -1)
			{
				$size += $attachment_size;
			}
			$count++;
		}
		return ($as_string) ? 
			$count.' '.$lng->txt('mail_attachments').', '.ilFormat::_sizeToString($size,'short') :
			$size;
	}
	//END DiskQuota: Get used disk space
}
?>