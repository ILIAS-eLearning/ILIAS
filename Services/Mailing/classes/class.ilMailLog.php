<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* Class ilMailLog.
*
* Used to log mails send at an ilias object. Emploies an ilMailStorage
* object to store attachments.
*
* Every mail is logged with an occasion, indicating the cause for sending
* the mail.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class ilMailLog {
	protected $obj_id;
	private $storage;

	/**
	 * Construct the maillog.
	 *
	 * @param integer $a_obj_id The id of the object the maillog logs
	 * 							mails for.
	 */
	public function __construct($a_obj_id) {
		$this->obj_id = $a_obj_id;
		$this->storage = null;

		global $ilDB;

		$this->db = &$ilDB;
	}

	/**
	 * Get the storage object for this log.
	 *
	 * Creates the storage if that was not done yet.
	 */
	protected function getStorage() {
		require_once("./Services/Mailing/classes/class.ilMailLogStorage.php");

		if ($this->storage === null) {
			$this->storage = new ilMailLogStorage($this->obj_id);
		}

		return $this->storage;
	}

	/**
	 * How many entries are contained in the log?
	 *
	 * @return integer The number of entries in the log.
	 */
	public function countEntries() {
		$result = $this->db->query("SELECT COUNT(*) as cnt
									FROM mail_log
									WHERE obj_id = ".$this->db->quote($this->obj_id, "integer"));

		$record = $this->db->fetchAssoc($result);
		return $record["cnt"];
	}

	/**
	 *Get entries from log.
     *
	 * An entry contains an id of the logged message, the moment the
	 * message was sent as timestamp, the occasion that led to sending
	 * of the mail and a recipient of the mail.
	 *
	 * To get more information use getEntry.
	 *
	 * @param integer $a_offset Number of entries to drop at the front of
	 *							the list of all entries.
	 * @param integer $a_limit Number of entries that should be retreived.
	 * @param string $a_order_by Field to order the result by. Must be one
	 *							 of id, moment, occasion, recipient or null.
	 * 							 If set to null, the id will be used.
	 * @param string $a_order_direction Either ASC or DESC.
	 * @return Array A list of log entries according to params.
	 */
	public function getEntries($a_offset, $a_limit, $a_order_by, $a_order_direction) {
		if ($a_order_direction === "ASC" || $a_order_direction === "asc") {
			$order = "ASC";
		}
		else if ($a_order_direction === "DESC" || $a_order_direction === "desc") {
			$order = "DESC";
		}
		else {
			throw new Exception("Unknown order: ".$a_order_direction);
		}

		if ($a_order_by === null) {
			$a_order_by = "id";
		}

		if (!in_array($a_order_by, array("moment", "occasion", "to", "id"))) {
			throw new Exception("Unknown field to order by: ".$a_order_by);
		}
		if ($a_order_by == "to") {
			$a_order_by = "mail_to";
		}

		$result = $this->db->query("SELECT id, moment, occasion, mail_to 'to'
									FROM mail_log
									WHERE obj_id = ".$this->db->quote($this->obj_id, "integer")."
									ORDER BY ".$a_order_by." ".$order."
									LIMIT ".$this->db->quote($a_limit, "integer")."
									OFFSET ".$this->db->quote($a_offset, "integer"));

		$ret = array();

		while ($record = $this->db->fetchAssoc($result)) {
			$record["moment"] = new ilDateTime($record["moment"], IL_CAL_UNIX);
			$ret[] = $record;
		}

		return $ret;
	}

	/**
	 * Get one complete logging entry.
	 *
	 * An entry is a dictionary containing fields id, moment, occasion,
	 * subject, message, attachments, recipient, cc and bcc.
	 * cc and bcc are comma-separated lists of the cc/bcc-recipients.
	 * attachments is an array with dictionaries containing keys name, path
	 * and the hash of the file as it is created by the storage.
	 *
	 * @param integer $a_id The id of the log entry to retreive.
	 * @return Array|null Returns described array or null if there is no log
	 * 					  entry with the given id for the object the log is
	 *					  responsible for.
	 */
	public function getEntry($a_id) {
		$result = $this->db->query("SELECT id, moment, occasion, subject, message, attachments, mail_from 'from', mail_to 'to', cc, bcc
									FROM mail_log
									WHERE id = ".$this->db->quote($a_id, "integer")."
									  AND obj_id = ".$this->db->quote($this->obj_id, "integer"));

		if ($record = $this->db->fetchAssoc($result)) {
			$attachments = $record["attachments"];
			$record["attachments"] = array();

			foreach (unserialize($attachments) as $name => $hash) {
				$path = $this->getStorage()->getAbsolutePath() . "/" . $hash;

				$record["attachments"][] = array( "name" => $name
												, "path" => $path
												, "hash" => $hash
												);
			}

			$record["moment"] = new ilDateTime($record["moment"], IL_CAL_UNIX);

			return $record;
		}

		return null;
	}

	/**
	 * Log a mail send at occasion.
	 *
	 * The mail is a dictionary containing fields from, to, cc, bcc,
	 * subject, message and attachments.
	 *
	 * Will replace < and > in from, to, cc and bcc by html entities.
	 *
	 * @param Array $a_mail Array containing mail data.
	 * @param string $a_occasion The occasion that led to sending of the mail.
	 */
	public function log($a_mail, $a_occasion) {
		$storage = $this->getStorage();

		$attachments = array();
		foreach ($a_mail["attachments"] as $attachment) {
			$name = basename($attachment["path"]);
			$attachments[$attachment["name"]] = $storage->addFile($attachment["path"]);
		}
		$attachments = serialize($attachments);

		$from = str_replace("<", "&lt;", str_replace(">", "&gt;", $a_mail["from"]));
		$to = str_replace("<", "&lt;", str_replace(">", "&gt;", $a_mail["to"]));
		$cc = str_replace("<", "&lt;", str_replace(">", "&gt;",implode(", ", $a_mail["cc"])));
		$bcc = str_replace("<", "&lt;", str_replace(">", "&gt;",implode(", ", $a_mail["bcc"])));

		$id = $this->db->nextID("mail_log");

		$query = "INSERT INTO mail_log (id, obj_id, moment, occasion, mail_from, mail_to, cc, bcc, subject, message, attachments)
				  VALUES ".
				"(" .$this->db->quote($id, "integer").", "
					.$this->db->quote($this->obj_id, "integer").", "
					.$this->db->quote(time(), "integer").", "
					.$this->db->quote($a_occasion, "text").", "
					.$this->db->quote($from, "text").", "
					.$this->db->quote($to, "text").", "
					.$this->db->quote($cc, "text").", "
					.$this->db->quote($bcc, "text").", "
					.$this->db->quote($a_mail["subject"], "text").", "
					.$this->db->quote((strlen($a_mail["message_html"]) > 0) ? $a_mail["message_html"] : $a_mail["message_plain"], "text").", "
					.$this->db->quote($attachments, "text").
				")";

		$this->db->manipulate($query);
	}

	/**
	 * Get a path to the file with the hash.
	 *
	 * @param string $a_hash The hash to get the path for.
	 * @return string The path to that file.
	 */
	public function getPath($a_hash) {
		return $this->getStorage()->getAbsolutePath() . "/" . $a_hash;
	}
}

?>