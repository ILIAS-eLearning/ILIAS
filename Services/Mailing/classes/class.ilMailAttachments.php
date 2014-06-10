<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilMailingAttachments.
*
* Manage mailing attachments for an object. Will put those attachments to
* the ilias data directory outside the webspace, into the subfolder
* ilMailAttachments, with the final subfolder determined by object id and type.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

require_once("Services/FileSystemStorage/classes/class.ilFileSystemStorage.php");

class ilMailAttachments extends ilFileSystemStorage {
	protected $obj_id;

	// used to cache lock state for files.
	private $is_locked_cache;

	/**
	 * Construct the ilMailAttachments object.
	 *
	 * @param integer $a_obj_id Id of the object this object manages attachments
	 * 					        for.
	 */
	public function __construct($a_obj_id) {
		global $ilDB;

		$this->db = &$ilDB;

		$this->obj_prefix = ilObject::_lookupType($a_obj_id);
		parent::__construct(self::STORAGE_DATA, false, $a_obj_id);
		$this->is_locked_cache = array();
		$this->obj_id = $a_obj_id;
	}

	/**
	 * Get a list of filenames of all available attachments.
	 *
	 * @return Array List containing the filenames.
	 */
	public function getList() {
		$ret = array();

		if(@file_exists($this->getAbsolutePath())) {
			$arr = array_diff(@scandir($this->getAbsolutePath()), array(".", ".."));
			foreach ( $arr as $attachment) {
				$ret[] = $attachment;
			}

		}
		return $ret;
	}

	/**
	 * Check weather there is an attachment with that name.
	 *
	 * @param string $a_filename The name of the file to check.
	 * @return bool True if attachment exists, false if not.
	 */
	public function isAttachment($a_filename) {
		return (@file_exists($this->getAbsolutePath() . "/" . $a_filename) === true);
	}

	/**
	 * Remove attachment with name.
	 *
	 * Throws if attachment is locked, so check that in advance.
	 *
	 * @param string $a_filename The name of the attachment file to remove.
	 */
	public function removeAttachment($a_filename) {
		if ($this->isLocked($a_filename)) {
			throw new Exception("'".$a_filename."' is locked at object ".$this->obj_id."and therefore can't be removed.");
		}

		$this->deleteFile($this->getAbsolutePath()."/".$a_filename);
	}

	/**
	 * Add attachment.
	 *
	 * Overrides already existing attachments with the same name.
	 *
	 * @param string $a_filename The name of the attachment to be used.
	 * @param string $a_tmp_path The complete path to the file to be used.
	 */
	public function addAttachment($a_filename, $a_tmp_path) {
		$this->create();

		$this->copyFile($a_tmp_path, $this->getAbsolutePath()."/".$a_filename);
	}

	/**
	 * Get the complete path to an attachment.
	 *
	 * @param string $a_filename The name of the file to get path for.
	 * @return string The path to that file.
	 */
	public function getPathTo($a_filename) {
		return $this->getAbsolutePath() . "/" . $a_filename;
	}


	/**
	 * Implemented for ilFileSystemStorage. Returns type of object that owns
	 * the attachments managed by this object.
	 */
	protected function getPathPostfix() {
		return $this->obj_prefix;
	}

	/**
	 * Implemented for ilFileSystemStorage. Returns "ilMailAttachments".
	 */
	protected function getPathPrefix() {
		return "ilMailAttachments";
	}

	/**
	 * Get a list with information about all managed attachments.#
	 *
	 * @return Array Array containing dictionaries with keys name, size
	 *				 (in Byte) and the timestamp where the file was modified
	 * 				 the last time.
	 */
	public function getInfoList() {
		$attachments = $this->getList();
		$ret = array();

		foreach ($attachments as $att) {
			$path = $this->getAbsolutePath(). "/" . $att;

			$ret[] = array( "name" => $att
						  , "size" => @filesize($path)
						  , "last_modified" => @filemtime($path)
						  );
		}

		return $ret;
	}

	/**
	 * Lock an attachment file to prevent removal.
	 *
	 * For every call to lock there is a counter that will be incremented.
	 * The counter will be decremented on calls to unlock. File is locked
	 * if that counter is larger than 0.
	 *
	 * @param string $a_filename The file to lock.
	 */
	public function lock($a_filename) {
		if (!$this->isAttachment($a_filename)) {
			throw new Exception("'".$a_filename."' is no available attachment for object ".$this->obj_id);
		}

		$query = "INSERT INTO mail_attachment_locks (obj_id, filename, lock_count)
				  VALUES ".
				"(" .$this->db->quote($this->obj_id, "integer").", "
					.$this->db->quote($a_filename, "text").", "
					.$this->db->quote(1, "integer").
				")".
				"ON DUPLICATE KEY UPDATE lock_count = lock_count + 1";

		$this->db->manipulate($query);

		$this->is_locked_cache[$a_filename] = true;
	}

	/**
	 * Unlock an attachment, to allow removal (if all locks are removed).
	 *
	 * Will throw if file is no attachment or not locked. So check that in
	 * advance!
	 *
	 * @param string $a_filename The file to unlock.
	 */
	public function unlock($a_filename) {
		if (!$this->isAttachment($a_filename)) {
			throw new Exception($a_filename." is no available attachment for object ".$this->obj_id);
		}

		if (!$this->isLocked($a_filename)) {
			throw new Exception("'".$a_filename."' is not locked at object ".$this->obj_id);
		}

		$this->db->manipulate("UPDATE mail_attachment_locks SET lock_count = lock_count - 1
							   WHERE obj_id = ".$this->db->quote($this->obj_id, "integer")."
							     AND filename = ".$this->db->quote($a_filename, "text"));

		if (array_key_exists($a_filename, $this->is_locked_cache)) {
			unset($this->is_locked_cache[$a_filename]);
		}
	}

	/**
	 * Check weather file is locked.
	 *
	 * @param string $a_filename File to check lock-state for.
	 * @return bool true if file is locked, false otherwise.
	 */
	public function isLocked($a_filename) {
		if (array_key_exists($a_filename, $this->is_locked_cache)) {
			return $this->is_locked_cache[$a_filename];
		}

		$result = $this->db->query("SELECT lock_count FROM mail_attachment_locks
									WHERE obj_id = ".$this->db->quote($this->obj_id, "integer")."
									  AND filename = ".$this->db->quote($a_filename, "text"));

		if ($record = $this->db->fetchAssoc($result)) {
			$this->is_locked_cache[$a_filename] = $record["lock_count"] > 0;
		}
		else {
			$this->is_locked_cache[$a_filename] = false;
		}

		return $this->is_locked_cache[$a_filename];
	}

	/**
	 * Convenience function to copy the managed files to another manager.
	 *
	 * @param integer $a_obj_id The id of the object to copy the files to.
	 */
	public function copyTo($a_obj_id) {
		$other = new ilMailAttachments($a_obj_id);

		foreach ($this->getList() as $att) {
			$other->addAttachment($att, $this->getAbsolutePath() . "/" . $att);
		}
	}
}

?>