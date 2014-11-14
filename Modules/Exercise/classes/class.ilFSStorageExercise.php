<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');
/** 
* 
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
* @ingroup ModulesExercise 
*/
class ilFSStorageExercise extends ilFileSystemStorage
{
	/**
	 * Constructor
	 *
	 * @param int	exercise id 
	 */
	public function __construct($a_container_id = 0, $a_ass_id = 0)
	{
		$this->ass_id = $a_ass_id;
		parent::__construct(self::STORAGE_DATA,true,$a_container_id);
	}
	
	/**
	 * Append ass_<ass_id> to path (assignment id)
	 */
	function init()
	{
		if (parent::init())
		{
			if ($this->ass_id > 0)
			{
				$this->submission_path = $this->path."/subm_".$this->ass_id;
				$this->tmp_path = $this->path."/tmp_".$this->ass_id;
				$this->feedb_path = $this->path."/feedb_".$this->ass_id;
				$this->multi_feedback_upload_path = $this->path."/mfb_up_".$this->ass_id;
				$this->peer_review_upload_path =  $this->path."/peer_up_".$this->ass_id;
				$this->path.= "/ass_".$this->ass_id;
			}
		}
		else
		{
			return false;
		}
		return true;
	}
	
	
	/**
	 * Implementation of abstract method
	 *
	 * @access protected
	 * 
	 */
	protected function getPathPostfix()
	{
	 	return 'exc';
	}
	
	/**
	 * Implementation of abstract method
	 *
	 * @access protected
	 * 
	 */
	protected function getPathPrefix()
	{
	 	return 'ilExercise';
	}
	
	/**
	 * Get submission path
	 */
	function getAbsoluteSubmissionPath()
	{
		return $this->submission_path;
	}
	
	/**
	 * Get submission path
	 */
	function getTempPath()
	{
		return $this->tmp_path;
	}

	/**
	 * Get feedback path
	 */
	function getFeedbackPath($a_user_id)
	{
		$path = $this->feedb_path."/".$a_user_id;
		if(!file_exists($path))
		{
			ilUtil::makeDirParents($path);
		}
		return $path;
	}
	
	function getGlobalFeedbackPath()
	{
		$path = $this->feedb_path."/0";
		if(!file_exists($path))
		{
			ilUtil::makeDirParents($path);
		}
		return $path;
	}

	/**
	 * Get multi feedback upload path
	 * (each uploader handled in a separate path)
	 */
	function getMultiFeedbackUploadPath($a_user_id)
	{
		$path = $this->multi_feedback_upload_path."/".$a_user_id;
		if(!file_exists($path))
		{
			ilUtil::makeDirParents($path);
		}
		return $path;
	}
	
	/**
	 * Get pear review upload path
	 * (each peer handled in a separate path)
	 */
	function getPeerReviewUploadPath($a_peer_id, $a_giver_id)
	{
		$path = $this->peer_review_upload_path."/".$a_peer_id."/".$a_giver_id;
		if(!file_exists($path))
		{
			ilUtil::makeDirParents($path);
		}
		return $path;
	}
	
	/**
	 * Remove all uploads
	 */
	function deletePeerReviewUploads()
	{
		$this->deleteDirectory($this->peer_review_upload_path);		
	}
		
	/**
	 * Create directory
	 *
	 * @access public
	 * 
	 */
	public function create()
	{
		parent::create();
		if(!file_exists($this->submission_path))
		{
			ilUtil::makeDirParents($this->submission_path);
		}
		if(!file_exists($this->tmp_path))
		{
			ilUtil::makeDirParents($this->tmp_path);
		}
		if(!file_exists($this->feedb_path))
		{
			ilUtil::makeDirParents($this->feedb_path);
		}
		return true;
	}

	/**
	 * Get assignment files
	 */
	function getFiles()
	{
		$files = array();
		if (!is_dir($this->path))
		{
			return $files;
		}

		$dp = opendir($this->path);
		while($file = readdir($dp))
		{
			if(!is_dir($this->path.'/'.$file))
			{
				$files[] = array(
					'name'     => $file,
					'size'     => filesize($this->path.'/'.$file),
					'ctime'    => ilFormat::formatDate(date('Y-m-d H:i:s',filectime($this->path.'/'.$file))),
					'fullpath' => $this->path.'/'.$file);
			}
		}
		closedir($dp);
		$files = ilUtil::sortArray($files, "name", "asc");
		return $files;
	}
	
	
	////
	//// Handle submitted files
	////
	
	/**
	* store delivered file in filesystem
	* @param array HTTP_POST_FILES
	* @param numeric database id of the user who delivered the file
	* @access	public
	* @return mixed Returns a result array with filename and mime type of the saved file, otherwise false
	*/
	function deliverFile($a_http_post_file, $user_id, $is_unziped = false)
	{
		$this->create();
		
		// TODO:
		// CHECK UPLOAD LIMIT
		//
		$result = false;
		if(isset($a_http_post_file) && $a_http_post_file['size'])
		{
			$filename = $a_http_post_file['name'];
			// replace whitespaces with underscores
			$filename = preg_replace("/\s/", "_", $filename);
			// remove all special characters
			$filename = preg_replace("/[^_a-zA-Z0-9\.]/", "", $filename);

			if(!is_dir($savepath = $this->getAbsoluteSubmissionPath()))
			{
				ilUtil::makeDir($savepath);
			}
			$savepath .= '/' .$user_id;
			if(!is_dir($savepath))
			{
				ilUtil::makeDir($savepath);
			}

			// CHECK IF FILE PATH EXISTS
			if (!is_dir($savepath))
			{
				require_once "./Services/Utilities/classes/class.ilUtil.php";
				#ilUtil::makeDirParents($savepath);
				ilUtil::makeDir($savepath);
			}
			$now = getdate();
			$prefix = sprintf("%04d%02d%02d%02d%02d%02d", $now["year"], $now["mon"], $now["mday"], $now["hours"],
							  $now["minutes"], $now["seconds"]);

			if (!$is_unziped)
			{
				//move_uploaded_file($a_http_post_file["tmp_name"], $savepath . $prefix . "_" . $filename);
				ilUtil::moveUploadedFile($a_http_post_file["tmp_name"], $a_http_post_file["name"],
				$savepath . "/" . $prefix . "_" . $filename);
			}
			else
			{

				rename($a_http_post_file['tmp_name'],
				$savepath . "/" . $prefix . "_" . $filename);
			}
			
			require_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";

			if (is_file($savepath . "/" . $prefix . "_" . $filename))
			{
				$result = array(
					"filename" => $prefix . "_" . $filename,
					"fullname" => $savepath . "/" . $prefix . "_" . $filename,
					"mimetype" =>	ilObjMediaObject::getMimeType($savepath . "/" . $prefix . "_" . $filename)
				);
			}
		}
		return $result;
	}

	/**
	 * Get number of feedback files
	 */
	function getFeedbackFiles($a_user_id)
	{
		$files = array();
	
		$dir = $this->getFeedbackPath($a_user_id);		
		if (@is_dir($dir))
		{
			$dp = opendir($dir);
			while($file = readdir($dp))
			{
				if(!is_dir($this->path.'/'.$file) && substr($file, 0, 1) != ".")
				{					
					$files[] = $file;					
				}
			}
		}
		
		return $files;
	}
	
	/**
	 * Count number of feedback files for a user
	 */
	function countFeedbackFiles($a_user_id)
	{
		$fbf = $this->getFeedbackFiles($a_user_id);
		return count($fbf);
	}
	
	/**
	 * Get path for assignment file
	 */
	function getAssignmentFilePath($a_file)
	{
		return $this->getAbsolutePath()."/".$a_file;
	}
	
	/**
	 * Get path for feedback file
	 */
	function getFeedbackFilePath($a_user_id, $a_file)
	{
		$dir = $this->getFeedbackPath($a_user_id);
		return $dir."/".$a_file;
	}

	/**
	 * Upload assignment files
	 * (e.g. from assignment creation form)
	 */
	function uploadAssignmentFiles($a_files)
	{
		if (is_array($a_files["name"]))
		{
			foreach ($a_files["name"] as $k => $name)
			{
				if ($name != "")
				{
					$type = $a_files["type"][$k];
					$tmp_name = $a_files["tmp_name"][$k];
					$size = $a_files["size"][$k];
					ilUtil::moveUploadedFile($tmp_name,
						basename($name),
						$this->path.DIRECTORY_SEPARATOR.basename($name),
						false);
				}
			}
		}
	}
}
?>