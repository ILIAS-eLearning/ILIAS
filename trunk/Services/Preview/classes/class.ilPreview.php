<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Preview/classes/class.ilPreviewSettings.php");
require_once("./Services/Preview/classes/class.ilFSStoragePreview.php");

/**
 * Class ilPreview
 *
 * This class provides utility methods for previews.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @package ServicesPreview
 */
class ilPreview
{
	// status values
	const RENDER_STATUS_NONE = "none";
	const RENDER_STATUS_PENDING = "pending";
	const RENDER_STATUS_CREATED = "created";
	const RENDER_STATUS_FAILED = "failed";
	
	const FILENAME_FORMAT = "preview_%02d.jpg";
	
	/**
	 * The object id.
	 * @var int
	 */
	private $obj_id = null;
	
	/**
	 * The type of the object.
	 * @var string
	 */
	private $obj_type = null;
	
	/**
	 * The file storage instance.
	 * @var ilFSStoragePreview
	 */
	private $storage = null;
	
	/**
	 * Defines whether the preview exists.
	 * @var bool
	 */
	private $exists = false;
	
	/**
	 * The timestamp when the preview was rendered.
	 * @var bool
	 */
	private $render_date = false;
	
	/**
	 * The status of the rendering process.
	 * @var string
	 */
	private $render_status = self::RENDER_STATUS_NONE;
	
	/**
	 * Creates a new ilPreview.
	 * 
	 * @param int $a_obj_id The object id.
	 * @param int $a_type The type of the object.
	 */
	public function __construct($a_obj_id, $a_type = "") 
	{
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_type;
		
		$this->init();
	}
	
	/**
	 * Creates the preview for the object with the specified id.
	 * 
	 * @param ilObject $a_obj The object to create the preview for.
	 * @param bool $a_force true, to force the creation of the preview; false, to create the preview only if needed.
	 * @return bool true, if the preview was created; otherwise, false.
	 */
	public static function createPreview($a_obj, $a_force = false)
	{
		$preview = new ilPreview($a_obj->getId(), $a_obj->getType());
		return $preview->create($a_obj, $a_force);
	}
	
	/**
	 * Deletes the preview for the object with the specified id.
	 * 
	 * @param int $a_obj_id The id of the object to create the preview for.
	 */
	public static function deletePreview($a_obj_id)
	{
		$preview = new ilPreview($a_obj_id);
		$preview->delete();		
	}
	
	/**
	 * Copies the preview images from one preview to a new preview object.
	 * 
	 * @param int $a_src_id The id of the object to copy from.
	 * @param int $a_dest_id The id of the object to copy to.
	 */
	public static function copyPreviews($a_src_id, $a_dest_id)
	{
		if (!ilPreviewSettings::isPreviewEnabled())		
			return;
		
		// get source preview
		$src = new ilPreview($a_src_id);
		$status = $src->getRenderStatus();
		
		// created? copy the previews
		if ($status == self::RENDER_STATUS_CREATED)
		{
			// create destination preview and set it's properties
			$dest = new ilPreview($a_dest_id);
			$dest->setRenderDate($src->getRenderDate());
			$dest->setRenderStatus($src->getRenderStatus());
			
			// create path
			$dest->getStorage()->create();
			
			// copy previews
			ilUtil::rCopy($src->getStoragePath(), $dest->getStoragePath());		
			
			// save copy
			$dest->doCreate();
		}
		else
		{
			// all other status need no action
			// self::RENDER_STATUS_FAILED
			// self::RENDER_STATUS_NONE
			// self::RENDER_STATUS_PENDING
		}	
	}
	
	/**
	 * Determines whether the object with the specified reference id has a preview.
	 * 
	 * @param int $a_obj_id The id of the object to check.
	 * @param string $a_type The type of the object to check.
	 * @return bool true, if the object has a preview; otherwise, false.
	 */
	public static function hasPreview($a_obj_id, $a_type = "")
	{
		if (!ilPreviewSettings::isPreviewEnabled())		
			return false;
		
		$preview = new ilPreview($a_obj_id, $a_type);
		if ($preview->exists())
			return true;
		
		// does not exist, enable on demand rendering if there's any renderer that supports our object
		require_once("./Services/Preview/classes/class.ilRendererFactory.php");
		$renderer = ilRendererFactory::getRenderer($preview);
		return $renderer != null;
	}
	
	/**
	 * Gets the render status for the object with the specified id.
	 * 
	 * @param int $a_obj_id The id of the object to get the status for.
	 * @return string The status of the rendering process.
	 */
	public static function lookupRenderStatus($a_obj_id)
	{
		$preview = new ilPreview($a_obj_id);
		return $preview->getRenderStatus();
	}
	
	/**
	 * Determines whether the preview exists or not.
	 * 
	 * @return bool true, if a preview exists for the object; otherwise, false.
	 */
	public function exists()
	{
		return $this->exists;		
	}
	
	/**
	 * Creates the preview.
	 * 
	 * @param ilObject $a_obj The object to create the preview for.
	 * @param bool $a_force true, to force the creation of the preview; false, to create the preview only if needed.
	 * @return bool true, if the preview was created; otherwise, false.
	 */
	public function create($a_obj, $a_force = false)
	{
		if (!ilPreviewSettings::isPreviewEnabled())		
			return false;
		
		// get renderer for preview
		require_once("./Services/Preview/classes/class.ilRendererFactory.php");
		$renderer = ilRendererFactory::getRenderer($this);
		
		// no renderer available?
		if ($renderer == null)
			return false;
		
		// exists, but still pending?
		if ($this->getRenderStatus() == self::RENDER_STATUS_PENDING)
			return false;
		
		// not forced? check if update really needed
		if ($this->getRenderStatus() == self::RENDER_STATUS_CREATED && !$a_force)
		{
			// check last modified against last render date
			if ($a_obj->getLastUpdateDate() <= $this->getRenderDate())
				return false;
		}
		
		// re-create the directory to store the previews
		$this->getStorage()->delete();
		$this->getStorage()->create();
		
		// let the renderer create the preview
		$renderer->render($this, $a_obj, true);
		
		// save to database
		$this->save();
		
		return true;
	}
	
	/**
	 * Deletes the preview.
	 */
	public function delete()
	{
		// does exist?
		if ($this->exists())
		{
			// delete files and database entry
			$this->getStorage()->delete();
			$this->doDelete();
			
			// reset values
			$this->exists = false;
			$this->render_date = false;
			$this->render_status = self::RENDER_STATUS_NONE;
		}
	}
	
	/**
	 * Gets an array of preview images.
	 * 
	 * @return array The preview images.
	 */
	public function getImages()
	{
		$images = array();
		
		// status must be created
		$path = $this->getStoragePath();
		if ($this->getRenderStatus() == self::RENDER_STATUS_CREATED)
		{
			// load files
			if ($handle = @opendir($path))
			{
				while (false !== ($file = readdir($handle)))
				{
					$filepath = $path . "/" .  $file;
					if (!is_file($filepath)) 
						continue;

					if ($file != '.' && $file != '..' && strpos($file, "preview_") === 0)
					{
	    				$image = array();
						$image["url"] = ilUtil::getHtmlPath($filepath);
					
						// get image size
						$size = @getimagesize($filepath);
						if ($size !== false)
						{
							$image["width"] = $size[0];
							$image["height"] = $size[1];
						}
					
						$images[$file] = $image;
					}
				}
				closedir($handle);
				
				// sort by key
				ksort($images);
			}
		}		
		
		return $images;
	}
	
	/**
	 * Saves the preview data to the database.
	 */
	public function save()
	{
		if ($this->exists)
			$this->doUpdate();
		else
			$this->doCreate();
	}
	
	/**
	 * Create entry in database.
	 */
	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->insert(
			"preview_data", 
			array(
				"obj_id" => array("integer", $this->getObjId()),
				"render_date" => array("timestamp", $this->getRenderDate()),
				"render_status" => array("text", $this->getRenderStatus())
			)
		);
		$this->exists = true;
	}
	
	/**
	 * Read data from database.
	 */
	protected function doRead()
	{
		global $ilDB;
		
		$set = $ilDB->queryF(
			"SELECT * FROM preview_data WHERE obj_id=%s", 
			array("integer"), 
			array($this->getObjId()));
		
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setRenderDate($rec["render_date"]);
			$this->setRenderStatus($rec["render_status"]);
			$this->exists = true;
		}
	}
	
	/**
	 * Update data in database.
	 */
	protected function doUpdate()
	{
		global $ilDB;
		
		$ilDB->update(
			"preview_data", 
			array(
				"render_date" => array("timestamp", $this->getRenderDate()),
				"render_status" => array("text", $this->getRenderStatus())
			),
			array("obj_id" => array("integer", $this->getObjId()))
		);
	}
	
	/**
	 * Delete data from database.
	 */
	protected function doDelete()
	{
		global $ilDB;
		
		$ilDB->manipulateF(
		    "DELETE FROM preview_data WHERE obj_id=%s",
			array("integer"), 
			array($this->getObjId()));		
	}
	
	/**
	 * Gets the id of the object the preview is for.
	 * 
	 * @return int The id of the object the preview is for.
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}
	
	/**
	 * Gets the type of the object the preview is for.
	 * 
	 * @return string The type of the object the preview is for.
	 */
	public function getObjType()
	{
		// not evaluated before or specified?
		if (empty($this->obj_type))
			$this->obj_type = ilObject::_lookupType($this->getObjId(), false);	

		return $this->obj_type;
	}
	
	/**
	 * Gets the path where the previews are stored relative to the web directory.
	 * 
	 * @return string The path where the previews are stored.
	 */
	public function getStoragePath()
	{
		return $this->getStorage()->getPath();
	}
	
	/**
	 * Gets the absolute path where the previews are stored.
	 * 
	 * @return string The path where the previews are stored.
	 */
	public function getAbsoluteStoragePath()
	{
		return ILIAS_ABSOLUTE_PATH . substr($this->getStorage()->getPath(), 1);
	}	
	
	/**
	 * Gets the absolute file path for preview images that contains a placeholder
	 * in the file name ('%02d') to be formatted with the preview number (use 'sprintf' for that).
	 * 
	 * @return string The format of the absolute file path.
	 */
	public function getFilePathFormat()
	{
		$path = ilUtil::removeTrailingPathSeparators($this->getAbsoluteStoragePath());
		return $path . "/" . self::FILENAME_FORMAT;
	}
	
	/**
	 * Gets the date when the preview was rendered.
	 * 
	 * @return datetime The date when the preview was rendered.
	 */
	public function getRenderDate()
	{
		return $this->render_date;
	}
	
	/**
	 * Sets the date when the preview was rendered.
	 * 
	 * @param datetime $a_status The date when the preview was rendered.
	 */
	public function setRenderDate($a_date)
	{
		$this->render_date = $a_date;
	}
	
	/**
	 * Gets the status of the rendering process.
	 * 
	 * @return string The status of the rendering process.
	 */
	public function getRenderStatus()
	{
		return $this->render_status;
	}
	
	/**
	 * Sets the status of the rendering process.
	 * 
	 * @param string $a_status The status to set.
	 */
	public function setRenderStatus($a_status)
	{
		$this->render_status = $a_status;
	}
	
	/**
	 * Gets the storage object for the preview.
	 * 
	 * @return ilFSStoragePreview The storage object.
	 */
	public function getStorage()
	{
		if ($this->storage == null)
			$this->storage = new ilFSStoragePreview($this->obj_id);
		
		return $this->storage;
	}
	
	/**
	 * Initializes the preview object.
	 */
	private function init()
	{
		// read entry
		$this->doRead();
	}
}
?>