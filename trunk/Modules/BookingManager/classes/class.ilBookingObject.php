<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * a bookable ressource
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingObject
{
	protected $id;			// int
	protected $pool_id;		// int
	protected $title;		// string
	protected $description; // string
	protected $nr_of_items; // int
	protected $schedule_id; // int
	protected $info_file; // string
	protected $post_text; // string
	protected $post_file; // string

	/**
	 * Constructor
	 *
	 * if id is given will read dataset from db
	 *
	 * @param	int	$a_id
	 */
	function __construct($a_id = NULL)
	{
		$this->id = (int)$a_id;
		$this->read();
	}
	
	/**
	 * Get id
	 * @return int 
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set object title
	 * @param	string	$a_title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get object title
	 * @return	string
	 */
	function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Set object description
	 * @param	string	$a_value
	 */
	function setDescription($a_value)
	{
		$this->description = $a_value;
	}

	/**
	 * Get object description
	 * @return	string
	 */
	function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set booking pool id
	 * @param	int	$a_pool_id
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = (int)$a_pool_id;
	}

	/**
	 * Get booking pool id
	 * @return	int
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Set booking schedule id
	 * @param	int	$a_schedule_id
	 */
	function setScheduleId($a_schedule_id)
	{
		$this->schedule_id = (int)$a_schedule_id;
	}

	/**
	 * Get booking schedule id
	 * @return	int
	 */
	function getScheduleId()
	{
		return $this->schedule_id;
	}
	
	/**
	 * Set number of items
	 * @param	int	$a_value
	 */
	function setNrOfItems($a_value)
	{
		$this->nr_of_items = (int)$a_value;
	}

	/**
	 * Get number of items
	 * @return	int
	 */
	function getNrOfItems()
	{
		return $this->nr_of_items;
	}
	
	/**
	 * Set info file
	 * @param	string	$a_value
	 */
	function setFile($a_value)
	{
		$this->info_file = $a_value;
	}

	/**
	 * Get info file
	 * @return	string
	 */
	function getFile()
	{
		return $this->info_file;
	}
	
	/**
	 * Get path to info file
	 */
	function getFileFullPath()
	{		
		if($this->id && $this->info_file)
		{
			$path = $this->initStorage($this->id, "file");			
			return $path.$this->info_file;			
		}
	}	
	
	/**
	 * Upload new info file
	 * 
	 * @param array $a_upload
	 * @return bool
	 */
	function uploadFile(array $a_upload)
	{
		if(!$this->id)
		{
			return false;
		}
		
		$this->deleteFile();
	
		$path = $this->initStorage($this->id, "file");
		$original = $a_upload["name"];
		
		if(@move_uploaded_file($a_upload["tmp_name"], $path.$original))
		{
			chmod($path.$original, 0770);

			$this->setFile($original);
			return true;
		}
		return false;
	}	
	
	/**
	 * remove existing info file
	 */
	public function deleteFile()
	{
		if($this->id)
		{
			$path = $this->getFileFullPath();
			if($path)
			{			
				@unlink($path);
				$this->setFile(null);
			}
		}
	}
	
	/**
	 * Set post text
	 * @param	string	$a_value
	 */
	function setPostText($a_value)
	{
		$this->post_text = $a_value;
	}

	/**
	 * Get post text
	 * @return	string
	 */
	function getPostText()
	{
		return $this->post_text;
	}
	
	/**
	 * Set post file
	 * @param	string	$a_value
	 */
	function setPostFile($a_value)
	{
		$this->post_file = $a_value;
	}

	/**
	 * Get post file
	 * @return	string
	 */
	function getPostFile()
	{
		return $this->post_file;
	}
		
	/**
	 * Get path to post file
	 */
	function getPostFileFullPath()
	{		
		if($this->id && $this->post_file)
		{
			$path = $this->initStorage($this->id, "post");			
			return $path.$this->post_file;			
		}
	}	
	
	/**
	 * Upload new post file
	 * 
	 * @param array $a_upload
	 * @return bool
	 */
	function uploadPostFile(array $a_upload)
	{
		if(!$this->id)
		{
			return false;
		}
		
		$this->deletePostFile();
	
		$path = $this->initStorage($this->id, "post");
		$original = $a_upload["name"];
		
		if(@move_uploaded_file($a_upload["tmp_name"], $path.$original))
		{
			chmod($path.$original, 0770);

			$this->setPostFile($original);
			return true;
		}
		return false;
	}	
	
	/**
	 * remove existing post file
	 */
	public function deletePostFile()
	{
		if($this->id)
		{
			$path = $this->getPostFileFullPath();
			if($path)
			{			
				@unlink($path);
				$this->setPostFile(null);
			}
		}
	}
	
	/**
	 * remove existing files
	 */
	public function deleteFiles()
	{
		if($this->id)
		{
			include_once "Modules/BookingManager/classes/class.ilFSStorageBooking.php";
			$storage = new ilFSStorageBooking($this->id);
			$storage->delete();
			
			$this->setFile(null);
			$this->setPostFile(null);
		}
	}

	/**
	 * Init file system storage
	 * 
	 * @param type $a_id
	 * @param type $a_subdir
	 * @return string 
	 */
	public static function initStorage($a_id, $a_subdir = null)
	{		
		include_once "Modules/BookingManager/classes/class.ilFSStorageBooking.php";
		$storage = new ilFSStorageBooking($a_id);
		$storage->create();
		
		$path = $storage->getAbsolutePath()."/";
		
		if($a_subdir)
		{
			$path .= $a_subdir."/";
			
			if(!is_dir($path))
			{
				mkdir($path);
			}
		}
				
		return $path;
	}	

	/**
	 * Get dataset from db
	 */
	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT *'.
				' FROM booking_object'.
				' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setTitle($row['title']);
			$this->setDescription($row['description']);
			$this->setPoolId($row['pool_id']);
			$this->setScheduleId($row['schedule_id']);
			$this->setNrOfItems($row['nr_items']);
			$this->setFile($row['info_file']);
			$this->setPostText($row['post_text']);
			$this->setPostFile($row['post_file']);
		}
	}
	
	/**
	 * Parse properties for sql statements
	 * @return array 
	 */
	protected function getDBFields()
	{
		$fields = array(
			'title' => array('text', $this->getTitle()),
			'description' => array('text', $this->getDescription()),
			'schedule_id' => array('text', $this->getScheduleId()),
			'nr_items' => array('text', $this->getNrOfItems()),			
			'info_file' => array('text', $this->getFile()),			
			'post_text' => array('text', $this->getPostText()),			
			'post_file' => array('text', $this->getPostFile())			
		);
		
		return $fields;		
	}

	/**
	 * Create new entry in db
	 * @return	bool
	 */
	function save()
	{
		global $ilDB;

		if($this->id)
		{
			return false;
		}
		
		$this->id = $ilDB->nextId('booking_object');
		
		$fields = $this->getDBFields();
		$fields['booking_object_id'] = array('integer', $this->id);
		$fields['pool_id'] = array('integer', $this->getPoolId());

		return $ilDB->insert('booking_object', $fields);
	}

	/**
	 * Update entry in db
	 * @return	bool
	 */
	function update()
	{
		global $ilDB;

		if(!$this->id)
		{
			return false;
		}
		
		$fields = $this->getDBFields();
						
		return $ilDB->update('booking_object', $fields, 
			array('booking_object_id'=>array('integer', $this->id)));
	}

	/**
	 * Get list of booking objects for given type	 
	 * @param	int	$a_pool_id
	 * @return	array
	 */
	static function getList($a_pool_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT *'.
			' FROM booking_object'.
			' WHERE pool_id = '.$ilDB->quote($a_pool_id, 'integer').
			' ORDER BY title');
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Delete single entry
	 * @return bool
	 */
	function delete()
	{
		global $ilDB;

		if($this->id)
		{
			$this->deleteFiles();
			
			return $ilDB->manipulate('DELETE FROM booking_object'.
				' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
		}
	}
	
	/**
	 * Get nr of available items 	 
	 * @param array $a_obj_ids
	 * @return array 
	 */
	static function getNrOfItemsForObjects(array $a_obj_ids)
	{
		global $ilDB;
		
		$map = array();
		
		$set = $ilDB->query("SELECT booking_object_id,nr_items".
			" FROM booking_object".
			" WHERE ".$ilDB->in("booking_object_id", $a_obj_ids, "", "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$map[$row["booking_object_id"]] = $row["nr_items"];
		}
		
		return $map;
	}	
	
	public function doClone($a_pool_id, $a_schedule_map = null)
	{
		$new_obj = new self();
		$new_obj->setPoolId($a_pool_id);
		$new_obj->setTitle($this->getTitle());
		$new_obj->setDescription($this->getDescription());
		$new_obj->setNrOfItems($this->getNrOfItems());	
		$new_obj->setFile($this->getFile());
		$new_obj->setPostText($this->getPostText());
		$new_obj->setPostFile($this->getPostFile());
		
		if($a_schedule_map)
		{
			$schedule_id = $this->getScheduleId();
			if($schedule_id)
			{
				$new_obj->setScheduleId($a_schedule_map[$schedule_id]);
			}
		}
		
		$new_obj->save();	
				
		// files
		$source = $this->initStorage($this->getId());
		$target = $new_obj->initStorage($new_obj->getId());		
		ilUtil::rCopy($source, $target);		
	}
}

?>