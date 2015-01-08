<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";
include_once('Modules/File/classes/class.ilFSStorageFile.php');

/** @defgroup ModulesFile Modules/File
 */

/**
* Class ilObjFile
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @ingroup ModulesFile
*/
class ilObjFile extends ilObject2
{
	var $filename;
	var $filetype;
	var $filemaxsize = "20000000";	// not used yet
	var $raise_upload_error;
	var $mode = "object";
	protected $rating;
	
	private $file_storage = null;


	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->version = 0;
		$this->raise_upload_error = true;
		parent::__construct($a_id,$a_call_by_reference);
		
		if($this->getId())
		{
			$this->initFileStorage();
		}
	}

	function initType()
	{
		$this->type = "file";
	}

	/**
	* create object
	* 
	* @param bool upload mode (if enabled no entries in file_data will be done)
	*/
	protected function doCreate($a_upload = false, $a_prevent_meta_data_creation = false)
	{
		//BEGIN WebDAV Move Property creation into a method of its own.
		$this->createProperties($a_upload);
		//END WebDAV Move Property creation into a method of its own.	
	}
	
	//BEGIN WebDAV: Move Property creation into a method of its own.
	/**
	 * The basic properties of a file object are stored in table object_data.
	 * This is not sufficient for a file object. Therefore we create additional
	 * properties in table file_data.
	 * This method has been put into a separate operation, to allow a WebDAV Null resource
	 * (class.ilObjNull.php) to become a file object.
	 */
	function createProperties($a_upload = false, $a_prevent_meta_data_creation = false)
	{
		global $ilDB,$tree;
		
		// Create file directory
		$this->initFileStorage();
		$this->file_storage->create();
		
		if($a_upload)
		{
			return true;
		}
		
		// not upload mode
		require_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_createEntry($this->getId(), "create", $this->getFileName().",1");
		$this->addNewsNotification("file_created");


		require_once("./Services/News/classes/class.ilNewsItem.php");
		$default_visibility = ilNewsItem::_getDefaultVisibilityForRefId($_GET['ref_id']);
		if ($default_visibility == "public")
		{
			ilBlockSetting::_write("news", "public_notifications",
				1, 0, $this->getId());
		}

		$q = "INSERT INTO file_data (file_id,file_name,file_type,file_size,version,f_mode) "
			."VALUES (".$ilDB->quote($this->getId() ,'integer').","
			.$ilDB->quote($this->getFileName() ,'text').","
			.$ilDB->quote($this->getFileType() ,'text').","
			.$ilDB->quote((int) $this->getFileSize() ,'integer').","
			.$ilDB->quote(1 ,'integer').",".$ilDB->quote($this->getMode() ,'text').")";
		$res = $ilDB->manipulate($q);
		
		// no meta data handling for file list files
		if ($this->getMode() != "filelist" && !$a_prevent_meta_data_creation)
		{
			$this->createMetaData();
		}
	}
	//END WebDAV: Move Property creation into a method of its own.
	
	/**
	* create file object meta data
	*/
	protected function doCreateMetaData()
	{
		// add technical section with file size and format
		$md_obj =& new ilMD($this->getId(),0,$this->getType());
		$technical = $md_obj->addTechnical();
		$technical->setSize($this->getFileSize());
		$technical->save();
		$format = $technical->addFormat();
		$format->setFormat($this->getFileType());
		$format->save();
		$technical->update();
	}

	protected function beforeMDUpdateListener($a_element)
	{
		// Check file extension
		// Removing the file extension is not allowed
		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md = new ilMD($this->getId(),0, $this->getType());
		if(!is_object($md_gen = $md->getGeneral()))
		{
			return false;
		}
		$title = $this->checkFileExtension($this->getFileName(), $md_gen->getTitle());
		$md_gen->setTitle($title);
		$md_gen->update();
		return true;
	}

	protected function doMDUpdateListener($a_element)
	{
		// handling for technical section 
		include_once 'Services/MetaData/classes/class.ilMD.php';
//echo "-".$a_element."-";
		switch($a_element)
		{
			case 'Technical':

				// Update Format (size is not stored in db)
				$md = new ilMD($this->getId(),0, $this->getType());
				if(!is_object($md_technical = $md->getTechnical()))
				{
					return false;
				}

				foreach($md_technical->getFormatIds() as $id)
				{
					$md_format = $md_technical->getFormat($id);
					ilObjFile::_writeFileType($this->getId(),$md_format->getFormat());
					$this->setFileType($md_format->getFormat());
					break;
				}

				break;
		}
		return true;
	}


	function getDirectory($a_version = 0)
	{
		$version_subdir = "";

		if ($a_version)
		{
			// BEGIN WebDAV Avoid double slash before version subdirectory
			$version_subdir = sprintf("%03d", $a_version);
			// END WebDAV Avoid  double slash before version subdirectory
		}
		
		if(!is_object($this->file_storage))
		{
			$this->initFileStorage();
		}
		
		return $this->file_storage->getAbsolutePath().'/'.$version_subdir;
	}

	function createDirectory()
	{
		ilUtil::makeDirParents($this->getDirectory());
	}
	
	function raiseUploadError($a_raise = true)
	{
		$this->raise_upload_error = $a_raise;
	}

	function getUploadFile($a_upload_file, $a_filename, $a_prevent_preview = false)
	{
		$this->setVersion($this->getVersion() + 1);

		if (@!is_dir($this->getDirectory($this->getVersion())))
		{
			ilUtil::makeDirParents($this->getDirectory($this->getVersion()));
		}

		$file = $this->getDirectory($this->getVersion())."/".$a_filename;
		//move_uploaded_file($a_upload_file, $file);
		ilUtil::moveUploadedFile($a_upload_file, $a_filename, $file, $this->raise_upload_error);
		
		$this->handleQuotaUpdate($this);
		
		// create preview?
		if (!$a_prevent_preview)
		{
			$this->createPreview(false);
		}			
	}

	/**
	* replace file with new file
	*/
	function replaceFile($a_upload_file, $a_filename)
	{
		$this->getUploadFile($a_upload_file, $a_filename, true);
		
		require_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_createEntry(
			$this->getId(),
			"replace",
			$a_filename.",".$this->getVersion()
		);
		$this->setFilename($a_filename);
		$this->addNewsNotification("file_updated");
		
		// create preview
		$this->createPreview(true);
	}
	
	
	public function addFileVersion($a_upload_file,$a_filename)
	{
		$this->getUploadFile($a_upload_file, $a_filename, true);
		
		require_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_createEntry(
			$this->getId(),
			"new_version",
			$a_filename.",".$this->getVersion()
		);
		$this->setFilename($a_filename);
		$this->addNewsNotification("file_updated");
				
		// create preview
		$this->createPreview($this->getVersion() > 1);
	}


	/**
	* copy file
	*/
	function copy($a_source,$a_destination)
	{
		return copy($a_source,$this->getDirectory()."/".$a_destination);
	}
	
	/**
	* clear data directory
	*/
	function clearDataDirectory()
	{
		ilUtil::delDir($this->getDirectory());
		$this->createDirectory();
	}
	
	/**
	 * Deletes the specified history entries or all entries if no ids are specified.
	 *
	 * @param array	$a_hist_entry_ids The ids of the entries to delete or null to delete all entries
	 */
	public function deleteVersions($a_hist_entry_ids = null)
	{
		global $ilDB;
		
		require_once("./Services/History/classes/class.ilHistory.php");
		
		if ($a_hist_entry_ids == null || count($a_hist_entry_ids) < 1)
		{
			$ilDB->manipulate("UPDATE file_data SET version = 1 WHERE file_id = ".$ilDB->quote($this->getId() ,'integer'));
			$this->setVersion(0);
			$this->clearDataDirectory();
		
			ilHistory::_removeEntriesForObject($this->getId());
			
			self::handleQuotaUpdate($this);
		}
		else
		{
			$actualVersionDeleted = false;
			
			// get all versions
			$versions = $this->getVersions();
			
			// delete each version
			foreach ($a_hist_entry_ids as $hist_id)
			{
				$entry = null;
				
				// get version
				foreach ($versions as $index => $version)
				{
					if ($version["hist_entry_id"] == $hist_id)	
					{
						// remove each history entry
						ilHistory::_removeEntryByHistoryID($hist_id);
				
						// delete directory				
						$version_dir = $this->getDirectory($version["version"]);
						ilUtil::delDir($version_dir);
						
						// is actual version?
						if ($version["version"] == $this->getVersion())
							$actualVersionDeleted = true;
						
						// remove from array
						unset($versions[$index]);						
						break;
					}
				}
			}
			
			// update actual version if it was deleted before
			if ($actualVersionDeleted)
			{			
				// get newest version (already sorted by getVersions) 
				$version = reset($versions);
				$this->updateWithVersion($version);
			}
			else
			{
				// updateWithVersion() will trigger quota, too
				self::handleQuotaUpdate($this);
			}
		}				
	}

	/**
	* read file properties
	*/
	protected function doRead()
	{
		global $ilDB;
		
		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($this->getId() ,'integer');
		$r = $this->ilias->db->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		$this->setFileName($row->file_name);
		$this->setFileType($row->file_type);
		$this->setFileSize($row->file_size);
		$this->setVersion($row->version);
		$this->setMode($row->f_mode);
		$this->setRating($row->rating);
		
		$this->initFileStorage();
	}

	protected function beforeUpdate()
	{
		// no meta data handling for file list files
		if ($this->getMode() != "filelist")
		{
			$this->updateMetaData();
		}
		
		return true;
	}

	/**
	* update file
	*/
	protected function doUpdate()
	{
		global $ilDB, $ilLog;
		
		//$ilLog->write(__METHOD__.' File type: '.$this->getFileType());
		
		$q = "UPDATE file_data SET file_name = ".$ilDB->quote($this->getFileName() ,'text').
			", file_type = ".$ilDB->quote($this->getFiletype() ,'text')." ".
			", file_size = ".$ilDB->quote((int) $this->getFileSize() ,'integer')." ".
			", version = ".$ilDB->quote($this->getVersion() ,'integer')." ".
			", f_mode = ".$ilDB->quote($this->getMode() ,'text')." ".
			", rating = ".$ilDB->quote($this->hasRating() ,'integer')." ".
			"WHERE file_id = ".$ilDB->quote($this->getId() ,'integer');
		$res = $ilDB->manipulate($q);
		
		self::handleQuotaUpdate($this);
		
		return true;
	}
	
	/**
	* update meta data
	*/
	protected function doUpdateMetaData()
	{
		// add technical section with file size and format
		$md_obj =& new ilMD($this->getId(),0,$this->getType());
		if(!is_object($technical = $md_obj->getTechnical()))
		{
			$technical = $md_obj->addTechnical();
			$technical->save();
		}
		$technical->setSize($this->getFileSize());
		
		$format_ids = $technical->getFormatIds();
		if (count($format_ids) > 0)
		{
			$format = $technical->getFormat($format_ids[0]);
			$format->setFormat($this->getFileType());
			$format->update();
		}
		else
		{
			$format = $technical->addFormat();
			$format->setFormat($this->getFileType());
			$format->save();
		}
		$technical->update();
	}

	/**
	* set filename
	*/
	function setFileName($a_name)
	{
		$this->filename = $a_name;
	}

	function getFileName()
	{
		return $this->filename;
	}

	function setFileType($a_type)
	{
		global $ilLog;
		
		
		$this->filetype = $a_type;
	}

	function getFileType()
	{
		return $this->filetype;
	}

	function setFileSize($a_size)
	{
		$this->filesize = $a_size;
	}

	function getFileSize()
	{
		return $this->filesize;
	}
	/**
	* Gets the disk usage of the object in bytes.
    *
	* @access	public
	* @return	integer		the disk usage in bytes
	*/
	function getDiskUsage()
	{
	    require_once("./Modules/File/classes/class.ilObjFileAccess.php");
		return ilObjFileAccess::_lookupDiskUsage($this->id);
	}


	// END PATCH WebDAV Encapsulate file access in ilObjFile class.
	function getFile($a_hist_entry_id = null)
	{
		if (is_null($a_hist_entry_id))
		{
			$file = $this->getDirectory($this->getVersion())."/".$this->getFileName();
		}
		else
		{
			require_once("./Services/History/classes/class.ilHistory.php");
			$entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);
			
			if ($entry === false)
			{
				return false;
			}

			$data = $this->parseInfoParams($entry);
			$file = $this->getDirectory($data["version"])."/".$data["filename"];
		}
		return $file;
	}
	// END PATCH WebDAV Encapsulate file access in ilObjFile class.
	
	function setVersion($a_version)
	{
		$this->version = $a_version;
	}

	function getVersion()
	{
		return $this->version;
	}
	
	/**
	* mode is object or filelist
	*
	* @param	string		$a_mode		mode
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* mode is object or filelist
	*
	* @return	string		mode
	*/
	function getMode()
	{
		return $this->mode;
	}
	
	function _writeFileType($a_id ,$a_format)
	{
		global $ilDB;
		
		$q = "UPDATE file_data SET ".
			" file_type = ".$ilDB->quote($a_format ,'text').
			" WHERE file_id = ".$ilDB->quote($a_id ,'integer');
		$res = $ilDB->manipulate($q);
		
	}

	function _lookupFileName($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($a_id ,'integer');
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		return ilUtil::stripSlashes($row->file_name);
	}


	/** Lookups the file size of the file in bytes. */
	function _lookupFileSize($a_id)
	{
	    require_once("./Modules/File/classes/class.ilObjFileAccess.php");
		return ilObjFileAccess::_lookupFileSize($a_id);
	}
	
	/**
	* lookup version
	*/
	function _lookupVersion($a_id)
	{
		require_once("./Modules/File/classes/class.ilObjFileAccess.php");
		return ilObjFileAccess::_lookupVersion($a_id);
	}

	/**
	* Determine File Size
	*/
	function determineFileSize($a_hist_entry_id = null)
	{
		if (is_null($a_hist_entry_id))
		{
			$file = $this->getDirectory($this->getVersion())."/".$this->getFileName();
		}
		else
		{
			require_once("./Services/History/classes/class.ilHistory.php");
			$entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);
			
			if ($entry === false)
			{
				return false;
			}

			$data = $this->parseInfoParams($entry);
			$file = $this->getDirectory($data["version"])."/".$data["filename"];
		}
		if (is_file($file))
		{
			$this->setFileSize(filesize($file));
		}
	}
	
	function sendFile($a_hist_entry_id = null)
	{	
		if (is_null($a_hist_entry_id))
		{
			$file = $this->getDirectory($this->getVersion())."/".$this->getFileName();

			// if not found lookup for file in file object's main directory for downward c	ompability
			if (@!is_file($file))
			{
				$file = $this->getDirectory()."/".$this->getFileName();
			}
		}
		else
		{
			require_once("./Services/History/classes/class.ilHistory.php");
			$entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);
			
			if ($entry === false)
			{
				echo "3";return false;
			}

			$data = $this->parseInfoParams($entry);
			$file = $this->getDirectory($data["version"])."/".$data["filename"];
			
			// if not found lookup for file in file object's main directory for downward compability
			if (@!is_file($file))
			{
				$file = $this->getDirectory()."/".$data[0];
			}

			// BEGIN WebDAV removed duplicated code
			// END WebDAV removed duplicated code
		}

		if (@is_file($file))
		{
			global $ilClientIniFile;
			
			// also returning the 'real' filename if a history file is delivered
			if ($ilClientIniFile->readVariable('file_access','download_with_uploaded_filename') != '1' && is_null($a_hist_entry_id))
			{
				ilUtil::deliverFile($file, $this->getTitle(), $this->guessFileType($file), $this->isInline());
			}
			else
			{
				ilUtil::deliverFile($file, basename($file), $this->guessFileType($file), $this->isInline());
			}
			return true;
		}

		return false;
	}

	// BEGIN WebDAV: Get file extension, determine if file is inline, guess file type.
	/**
	 * Returns the extension of the file name converted to lower-case.
	 * e.g. returns 'pdf' for 'document.pdf'.
	 */
	function getFileExtension() {
		require_once 'Modules/File/classes/class.ilObjFileAccess.php';
		return ilObjFileAccess::_getFileExtension($this->getTitle());
	}
	/**
	 * Returns true, if this file should be displayed inline in a browser
	 * window. This is especially useful for PDF documents, HTML pages,
	 * and for images which are directly supported by the browser.
	 */
	function isInline() {
		require_once 'Modules/File/classes/class.ilObjFileAccess.php';
		return ilObjFileAccess::_isFileInline($this->getTitle());
	}
	/**
	 * Returns true, if this file should be hidden in the repository view.
	 */
	function isHidden() {
		require_once 'Modules/File/classes/class.ilObjFileAccess.php';
		return ilObjFileAccess::_isFileHidden($this->getTitle());
	}
	// END WebDAV: Get file extension, determine if file is inline, guess file type.
	
	/**
	 * Guesses the file type based on the current values returned by getFileType()
	 * and getFileExtension().
	 * If getFileType() returns 'application/octet-stream', the file extension is
	 * used to guess a more accurate file type.
	 */
	function guessFileType($a_file = "") {
		
		$path = pathinfo($a_file);
		if ($path["extension"] != "")
		{
			$filename = $path["basename"];
		}
		else
		{
			$filename = "dummy.".$this->getFileExtension();
		}
		include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
		$mime = ilMimeTypeUtil::getMimeType($a_file, $filename, $this->getFileType());
		return $mime;
		
/*
		$fileType = $this->getFileType();
		if (strlen($fileType) == 0) {	
			$fileType = 'application/octet-stream';
		}

		// Firefox browser assigns 'application/x-pdf' to PDF files, but
		// it can only handle them if the have the mime-type 'application/pdf'.
		if ($fileType == 'application/x-pdf')
		{
			$fileType = 'application/pdf';
		}

		if ($fileType == 'application/octet-stream')
		{
			$fileExtension = $this->getFileExtension();
			$mimeArray = array(
				'mpeg' => 'video/mpeg',
				'mp3' => 'audio/mpeg',
				'pdf' => 'application/pdf',
				'gif' => 'image/gif',
				'jpg' => 'image/jpg',
				'png' => 'image/png',
				'htm' => 'text/html',
				'html' => 'text/html',
				'wma' => 'video/x-ms-wma',
				'wmv' => 'video/x-ms-wmv',
				'swf' => 'application/x-shockwave-flash',
			);
			if (array_key_exists($fileExtension, $mimeArray))
			{
				$fileType = $mimeArray[$fileExtension];
			}
		}
		return $fileType;
*/
	}
	
	/**
	 * Clone
	 *
	 * @access public
	 * @param object clone
	 * @param int target id
	 * @param int copy id
	 * 
	 */
	protected function doCloneObject($a_new_obj,$a_target_id,$a_copy_id = 0)
	{
		global $ilDB;

	 	$a_new_obj->createDirectory();
	 	$this->cloneMetaData($a_new_obj);
	 	
	 	// Copy all file versions
	 	ilUtil::rCopy($this->getDirectory(),$a_new_obj->getDirectory());
	 	
	 	// object created now copy other settings
		$query = "INSERT INTO file_data (file_id,file_name,file_type,file_size,version,rating,f_mode) VALUES (".
				$ilDB->quote($a_new_obj->getId() ,'integer').",".
				$ilDB->quote($this->getFileName() ,'text').",".
				$ilDB->quote($this->getFileType() ,'text').",".
				$ilDB->quote((int) $this->getFileSize() ,'integer').", ".
				$ilDB->quote($this->getVersion() ,'integer').", ".
				$ilDB->quote($this->hasRating() ,'integer').", ".
				$ilDB->quote($this->getMode() ,'text').")";
		$res = $ilDB->manipulate($query);
		 
		// copy all previews
		require_once("./Services/Preview/classes/class.ilPreview.php");
		ilPreview::copyPreviews($this->getId(), $a_new_obj->getId());

		// copy history entries
		require_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_copyEntriesForObject($this->getId(),$a_new_obj->getId());
		
		// add news notification
		$a_new_obj->addNewsNotification("file_created");

	 	return $a_new_obj;
	}
	
	protected function beforeDelete()
	{
		global $ilDB;
		
		// check, if file is used somewhere
		$usages = $this->getUsages();
		if (count($usages) == 0)
		{
			return true;
		}
		return false;
	}

	protected function doDelete()
	{
		global $ilDB;
		
		// delete file data entry
		$q = "DELETE FROM file_data WHERE file_id = ".$ilDB->quote($this->getId() ,'integer');
		$this->ilias->db->query($q);

		// delete history entries
		require_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_removeEntriesForObject($this->getId());

		// delete entire directory and its content
		if (@is_dir($this->getDirectory()))
		{
			ilUtil::delDir($this->getDirectory());
		}

		// delete meta data
		if ($this->getMode() != "filelist")
		{
			$this->deleteMetaData();
		}
		
		self::handleQuotaUpdate($this);

		// delete preview
		$this->deletePreview();
	}

	/**
	* export files of object to target directory
	* note: target directory must be the export target directory,
	* "/objects/il_<inst>_file_<file_id>/..." will be appended to this directory
	*
	* @param	string		$a_target_dir		target directory
	*/
	function export($a_target_dir)
	{
		$subdir = "il_".IL_INST_ID."_file_".$this->getId();
		ilUtil::makeDir($a_target_dir."/objects/".$subdir);

		$filedir = $this->getDirectory($this->getVersion());
		
		if (@!is_dir($filedir))
		{
			$filedir = $this->getDirectory();
		}
		
		ilUtil::rCopy($filedir, $a_target_dir."/objects/".$subdir);
	}

	/**
	* static delete all usages of
	*/
	function _deleteAllUsages($a_type, $a_id, $a_usage_hist_nr = 0, $a_usage_lang = "-")
	{
		global $ilDB;
		
		$and_hist = ($a_usage_hist_nr !== false)
			? " AND usage_hist_nr = ".$ilDB->quote($a_usage_hist_nr, "integer")
			: "";
		
		$file_ids = array();
		$set = $ilDB->query("SELECT id FROM file_usage".
			" WHERE usage_type = ".$ilDB->quote($a_type, "text").
			" AND usage_id= ".$ilDB->quote($a_id, "integer").
			" AND usage_lang= ".$ilDB->quote($a_usage_lang, "text").
			$and_hist);
		while($row = $ilDB->fetchAssoc($set))
		{
			$file_ids[] = $row["id"];
		}
		
		$ilDB->manipulate("DELETE FROM file_usage WHERE usage_type = ".
			$ilDB->quote($a_type, "text").
			" AND usage_id = ".$ilDB->quote((int) $a_id, "integer").
			" AND usage_lang= ".$ilDB->quote($a_usage_lang, "text").
			" AND usage_hist_nr = ".$ilDB->quote((int) $a_usage_hist_nr, "integer"));
		
		foreach($file_ids as $file_id)
		{
			self::handleQuotaUpdate(new self($file_id, false));	
		}
	}

	/**
	* save usage
	*/
	function _saveUsage($a_file_id, $a_type, $a_id, $a_usage_hist_nr = 0, $a_usage_lang = "-")
	{
		global $ilDB;
		
		/*
		$ilDB->manipulate("DELETE FROM file_usage WHERE usage_type = ".
			$ilDB->quote((string) $a_type, "text").
			" AND usage_id = ".$ilDB->quote((int) $a_id, "integer").
			" AND usage_lang = ".$ilDB->quote($a_usage_lang, "text").
			" AND usage_hist_nr = ".$ilDB->quote((int) $a_usage_hist_nr, "integer").
			" AND id = ".$ilDB->quote((int) $a_file_id, "integer"));

		$ilDB->manipulate("INSERT INTO file_usage (id, usage_type, usage_id, usage_hist_nr, usage_lang) VALUES".
			" (".$ilDB->quote((int) $a_file_id, "integer").",".
			$ilDB->quote((string) $a_type, "text").",".
			$ilDB->quote((int) $a_id, "integer").",".
			$ilDB->quote((int) $a_usage_hist_nr, "integer").",".
			$ilDB->quote($a_usage_lang, "text").
			")");
		*/
		
		// #15143
		$ilDB->replace("file_usage",
			array(
				"id" => array("integer", (int) $a_file_id),
				"usage_type" => array("text", (string) $a_type),
				"usage_id" => array("integer", (int) $a_id),
				"usage_hist_nr" => array("integer", (int) $a_usage_hist_nr),
				"usage_lang" => array("text", $a_usage_lang)
			),
			array()
		);
		
		self::handleQuotaUpdate(new self($a_file_id, false));		
	}

	/**
	* get all usages of file object
	*/
	function getUsages()
	{
		global $ilDB;

		// get usages in learning modules
		$q = "SELECT * FROM file_usage WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$us_set = $ilDB->query($q);
		$ret = array();
		while($us_rec = $ilDB->fetchAssoc($us_set))
		{
			$ret[] = array("type" => $us_rec["usage_type"],
				"id" => $us_rec["usage_id"],
				"lang" => $us_rec["usage_lang"],
				"hist_nr" => $us_rec["usage_hist_nr"]);
		}

		return $ret;
	}

	/**
	* get all files of an object
	*
	* @param	string		$a_type		object type (e.g. "lm:pg")
	* @param	int			$a_id		object id
	*
	* @return	array		array of file ids
	*/
	function _getFilesOfObject($a_type, $a_id, $a_usage_hist_nr = 0, $a_usage_lang = "-")
	{
		global $ilDB;

		// get usages in learning modules
		$q = "SELECT * FROM file_usage WHERE ".
			"usage_id = ".$ilDB->quote((int) $a_id, "integer")." AND ".
			"usage_type = ".$ilDB->quote((string) $a_type, "text")." AND ".
			"usage_lang = ".$ilDB->quote((string) $a_usage_lang, "text")." AND ".
			"usage_hist_nr = ".$ilDB->quote((int) $a_usage_hist_nr, "integer");
		$file_set = $ilDB->query($q);
		$ret = array();
		while($file_rec = $ilDB->fetchAssoc($file_set))
		{
			$ret[$file_rec["id"]] = $file_rec["id"];
		}

		return $ret;
	}

	// TODO: What is this function good for??
	function getXMLZip()
	{
		global $ilias;

		$zip = PATH_TO_ZIP;

		exec($zip.' '.ilUtil::escapeShellArg($this->getDirectory().'/'.$this->getFileName())." ".
			 ilUtil::escapeShellArg($this->getDirectory().'/'.'1.zip'));

		return $this->getDirectory().'/1.zip';
	}
	
	function addNewsNotification($a_lang_var)
	{
                // BEGIN WebDAV Suppress news notification for hidden files
                if ($this->isHidden()) {
                        return;
                }
                // END WebDAV Suppress news notification for hidden files
                
		global $ilUser;
		
		// Add Notification to news
		include_once("./Services/News/classes/class.ilNewsItem.php");
		include_once("./Modules/File/classes/class.ilObjFileAccess.php");
		$news_item = new ilNewsItem();
		$news_item->setContext($this->getId(), $this->getType());
		$news_item->setPriority(NEWS_NOTICE);
		$news_item->setTitle($a_lang_var);
		$news_item->setContentIsLangVar(true);
		if ($this->getDescription() != "")
		{
			$news_item->setContent(
				"<p>".
				$this->getDescription()."</p>");
		}
		$news_item->setUserId($ilUser->getId());
		$news_item->setVisibility(NEWS_USERS);
		$news_item->create();
	}
	
	/**
	 * init file storage object
	 *
	 * @access public
	 * 
	 */
	public function initFileStorage()
	{
	 	$this->file_storage = new ilFSStorageFile($this->getId());
	 	return true;
	}
	/**
	* storeUnzipedFile
	*
	* Stores Files unzipped from uploaded archive in filesystem
	*
	* @param string $a_upload_file
	* @param string	$a_filename
	*/

	function storeUnzipedFile($a_upload_file, $a_filename)
		{
			$this->setVersion($this->getVersion() + 1);

			if (@!is_dir($this->getDirectory($this->getVersion())))
			{
				ilUtil::makeDir($this->getDirectory($this->getVersion()));
			}

			$file = $this->getDirectory($this->getVersion())."/".$a_filename;
			//move_uploaded_file($a_upload_file, $file);
			rename($a_upload_file,  $file);
			
			// create preview
			$this->createPreview();
	}
	
	/**
	 * return absolute path for version
	 *
	 */
	public static function _lookupAbsolutePath ($obj_id, $a_version = null) 
	{
		$file_storage = new ilFSStorageFile($obj_id);
		$filename = ilObjFile::_lookupFileName($obj_id);
		$version_subdir = "";
		
		if (!is_numeric($a_version))
		{
			$a_version = ilObjFile::_lookupVersion ($obj_id);
		}
		$version_subdir = DIRECTORY_SEPARATOR.sprintf("%03d", $a_version);		
		return $file_storage->getAbsolutePath().$version_subdir.DIRECTORY_SEPARATOR.$filename;
	}
	
	/**
	 * Check if the file extension does still exist after an update of the title
	 * @return 
	 */
	public function checkFileExtension($new_filename,$new_title)
	{
		include_once './Modules/File/classes/class.ilObjFileAccess.php';
		$fileExtension = ilObjFileAccess::_getFileExtension($new_filename);
		$titleExtension = ilObjFileAccess::_getFileExtension($new_title);
		if ($titleExtension != $fileExtension && strlen($fileExtension) > 0)
		{
			// remove old extension
			$pi = pathinfo($this->getFileName());
			$suffix = $pi["extension"];
			if ($suffix != "")
			{ 
				if (substr($new_title,
					strlen($new_title) - strlen($suffix) - 1)
					== ".".$suffix)
				{
					$new_title = substr($new_title, 0, strlen($new_title) - strlen($suffix) - 1);
				}
			}
			$new_title .= '.'.$fileExtension;
		}
		return $new_title;
	}
	
	/**
	 * Gets the file versions for this object.
	 * 
	 * @param array $version_ids The file versions to get. If not specified all versions are returned.
	 * @return The file versions.
	 */
	public function getVersions($version_ids = null)
	{
		include_once("./Services/History/classes/class.ilHistory.php");
		$versions = ilHistory::_getEntriesForObject($this->getId(), $this->getType());
		
		if ($version_ids != null && count($version_ids) > 0)
		{
			foreach ($versions as $index => $version) 
			{
				if (!in_array($version["hist_entry_id"], $version_ids, true))
				{
					unset($versions[$index]);
				}
			}		
		}
		
		// add custom entries
		foreach ($versions as $index => $version) 
		{
			$params = $this->parseInfoParams($version);
			$versions[$index] = array_merge($version, $params);
		}
		
		// sort by version number (hist_entry_id will do for that)
		usort($versions, array($this, "compareVersions"));
		
		return $versions;
	}
	
	/**
	 * Gets a specific file version.
	 * 
	 * @param int $version_id The version id to get.
	 * @return array The specific version or false if the version was not found. 
	 */
	public function getSpecificVersion($version_id)
	{
		include_once("./Services/History/classes/class.ilHistory.php");
		$version = ilHistory::_getEntryByHistoryID($version_id);
		if ($version === false)
			return false;
		
		// ilHistory returns different keys in _getEntryByHistoryID and _getEntriesForObject
		// so this makes it the same
		$version["hist_entry_id"] = $version["id"];
		$version["user_id"] = $version["usr_id"];
		$version["date"] = $version["hdate"];
		unset($version["id"], $version["usr_id"], $version["hdate"]);
		
		// parse params
		$params = $this->parseInfoParams($version);
		return array_merge($version, $params);
	}
	
	/**
	 * Makes the specified version the current one and returns theSummary of rollbackVersion
	 * 
	 * @param int $version_id The id of the version to make the current one.
	 * @return array The new actual version. 
	 */
	public function rollback($version_id)
	{
		global $ilDB, $ilUser;
		
		$source = $this->getSpecificVersion($version_id);
		if ($source === false)
		{
			$this->ilErr->raiseError($this->lng->txt("obj_not_found"), $this->ilErr->MESSAGE);
		}
		
		// get the new version number
		$new_version_nr = $this->getVersion() + 1;
		
		// copy file 
		$source_path = $this->getDirectory($source["version"]) . "/" . $source["filename"];
		$dest_dir = $this->getDirectory($new_version_nr);
		if (@!is_dir($dest_dir))
			ilUtil::makeDir($dest_dir);

		copy($source_path, $dest_dir . "/" . $source["filename"]);
		
		// create new history entry based on the old one
		include_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_createEntry(
			$this->getId(), 
			"rollback", 
			$source["filename"] . "," . $new_version_nr . "|" . $source["version"] . "|" . $ilUser->getId());
		
		// get id of newest entry
		$new_version = $this->getSpecificVersion($ilDB->getLastInsertId());
		
		// change user back to the original uploader
		ilHistory::_changeUserId($new_version["hist_entry_id"], $source["user_id"]);
		
		// update this file with the new version
		$this->updateWithVersion($new_version);
		
		$this->addNewsNotification("file_updated");
		
		return $new_version;
	}
	
	/**
	 * Updates the file object with the specified file version.
	 * 
	 * @param array $version The version to update the file object with.
	 */
	protected function updateWithVersion($version)
	{
		// update title (checkFileExtension must be called before setFileName!)
		$this->setTitle($this->checkFileExtension($version["filename"], $this->getTitle()));
		
		$this->setVersion($version["version"]);
		$this->setFileName($version["filename"]);
		
		// evaluate mime type (reset file type before)
		$this->setFileType("");
		$this->setFileType($this->guessFileType($version["filename"]));
		
		// set filesize
		$this->determineFileSize();

		$this->update();	
		
		// refresh preview
		$this->createPreview(true);
	}
	
	/**
	 * Compares two file versions. 
	 * 
	 * @param array $v1 First file version to compare.
	 * @param array $v2 Second file version to compare.
	 * @return int Returns an integer less than, equal to, or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second.
	 */
	function compareVersions($v1, $v2)
	{
		// v2 - v1 because version should be descending
		return (int)$v2["version"] - (int)$v1["version"];
	}

	/**
	 * Parses the info parameters ("info_params") of the specified history entry.
	 * 
	 * @param array $entry The history entry.
	 * @return array Returns an array containing the "filename" and "version" contained within the "info_params".
	 */
	function parseInfoParams($entry)
	{
		$data = preg_split("/(.*),(.*)/", $entry["info_params"], 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
		// bugfix: first created file had no version number
		// this is a workaround for all files created before the bug was fixed
		if (empty($data[1]))
			$data[1] = "1";
		
		$result = array("filename" => $data[0], "version" => $data[1], "rollback_version" => "", "rollback_user_id" => "");

		// if rollback, the version contains the rollback version as well
		if ($entry["action"] == "rollback")
		{
			$tokens = explode("|", $result["version"]);
			if (count($tokens) > 1)
			{
				$result["version"] = $tokens[0];
				$result["rollback_version"] = $tokens[1];
				
				if (count($tokens) > 2)
					$result["rollback_user_id"] = $tokens[2];
			}
		}
		
		return $result;
	}
	
	protected static function handleQuotaUpdate(ilObjFile $a_file)
	{				
		include_once "Services/MediaObjects/classes/class.ilObjMediaObject.php";	
		$mob = new ilObjMediaObject();
		
		// file itself could be workspace item
		$parent_obj_ids = array($a_file->getId());
		
		foreach($a_file->getUsages() as $item)
		{										
			$parent_obj_id = $mob->getParentObjectIdForUsage($item);
			if($parent_obj_id && 
				!in_array($parent_obj_id, $parent_obj_ids))
			{					
				$parent_obj_ids[]= $parent_obj_id;
			}						
		}
		
		include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
		ilDiskQuotaHandler::handleUpdatedSourceObject($a_file->getType(), 
			$a_file->getId(),
			$a_file->getDiskUsage(), 
			$parent_obj_ids);	
	}

	/**
	 * Creates a preview for the file object.
	 * 
	 * @param bool $force true, to force the creation of the preview; false, to create the preview only if the file is newer.
	 */
	protected function createPreview($force = false)
	{
		// only normal files are supported
		if ($this->getMode() != "object")
			return;
		
		require_once("./Services/Preview/classes/class.ilPreview.php");
		ilPreview::createPreview($this, $force);
	}
	
	/**
	 * Deletes the preview of the file object.
	 */
	protected function deletePreview()
	{
		// only normal files are supported
		if ($this->getMode() != "object")
			return;
		
		require_once("./Services/Preview/classes/class.ilPreview.php");
		ilPreview::deletePreview($this->getId());
	}	
	
	public function setRating($a_value)
	{
		$this->rating = (bool)$a_value;
	}
	
	public function hasRating()
	{
		return $this->rating;
	}

} // END class.ilObjFile
?>