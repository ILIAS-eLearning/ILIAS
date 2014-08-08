<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevCrsInvitationMailSettings
*$
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevCrsInvitationMailSettings {
	protected $crs_id;
	protected $settings;
	protected $db;
	protected $attachments_path;
	protected $attachments;
	protected $template_api;

	private static $template_type = "CrsInv";

	public function __construct($a_crs_id) {
		global $ilDB, $ilCtrl;
		$this->db = &$ilDB;

		$this->crs_id = $a_crs_id;
		$this->attachments_path = null;
		$this->template_api = null;

		$this->read();
	}

	// Returns the id of the template set for the function.
	// Return -1 if none is set.
	public function getTemplateFor($a_function_name) {
		if(array_key_exists($a_function_name, $this->settings)) {
			return $this->settings[$a_function_name]["template_id"];
		}

		return -1;
	}

	// Get names of attached files.
	public function getAttachmentNamesFor($a_function_name) {
		if (array_key_exists($a_function_name, $this->settings)) {
			return $this->settings[$a_function_name]["attachments"];
		}

		return array();
	}

	// Get names of attached files.
	public function getAttachmentsFor($a_function_name) {
		return $this->attachmentNamesToCompleteArray(
						$this->getAttachmentNamesFor($a_function_name));
	}

	protected function getAttachments() {
		if ($this->attachments === null) {
			require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");
			$this->attachments = new gevCrsMailAttachments($this->crs_id);
		}

		return $this->attachments;
	}

	protected function attachmentNamesToCompleteArray($a_attachments) {
		$attachments = array();

		foreach ($a_attachments as $att) {
			$attachments[$att] = array("name" => $att
									  , "path" => $this->getAttachments()->getPathTo($att)
									  );
		}

		return $attachments;
	}

	public function setSettingsFor($a_function_name, $a_template_id, $a_attachments) {
		//TODO: check validity of function_name here?

		if (!array_key_exists($a_function_name, $this->settings)) {
			$this->settings[$a_function_name] = array();
		}
		$this->settings[$a_function_name]["template_id"] = $a_template_id;
		$this->settings[$a_function_name]["attachments"] = $a_attachments;
	}

	public function getInvitationMailTemplates($a_default_option) {
		require_once("./Services/MailTemplates/classes/class.ilMailTemplateManagementAPI.php");
		require_once("./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php");

		if ($this->template_api === null) {
			$this->template_api = new ilMailTemplateManagementAPI();
		}

		// TODO: "Einladungsmail" is magic. Better use some setting...
		$templates = $this->template_api->getTemplateCategoriesByType(self::$template_type);

		$arr = array( -1 => $a_default_option);

		foreach ($templates as $template) {
			$settings = new ilMailTemplateSettingsEntity();
			$settings->setIlDB($this->db);
			$settings->loadByCategoryAndTemplate($template["category"], self::$template_type);
			$arr[$settings->getTemplateTypeId()] = $template["category"];
		}

		return $arr;
	}

	protected function read() {
		$this->settings = array();

		$result = $this->db->query("SELECT function_name, template_id, attachments
									FROM gev_crs_invset
									WHERE crs_id = ".$this->db->quote($this->crs_id));

		while ($record = $this->db->fetchAssoc($result)) {
			$this->settings[$record["function_name"]] = array(
					"template_id" => $record["template_id"],
					"attachments" => unserialize($record["attachments"])
				);
		}
	}

	protected function lockAttachments() {
		$attachments = $this->getAttachments();

		foreach ($this->settings as $function_name => $settings) {
			foreach ($this->settings[$function_name]["attachments"] as $att) {
				$attachments->lock($att);
			}
		}
	}

	protected function unlockAttachments() {
		$attachments = $this->getAttachments();

		foreach ($this->settings as $function_name => $settings) {
			foreach ($this->settings[$function_name]["attachments"] as $att) {
				$attachments->unlock($att);
			}
		}
	}

	public function save() {
		$new_settings = $this->settings;
		$this->read();

		$this->unlockAttachments();
		$this->settings = $new_settings;
		$this->lockAttachments();

		foreach ($this->settings as $function_name => $settings) {
			$att = serialize($settings["attachments"]);
			$query = "INSERT INTO gev_crs_invset (crs_id, function_name, template_id, attachments)
					  VALUES ".
					"(".$this->db->quote($this->crs_id, "integer").", "
					   .$this->db->quote($function_name, "text").","
					   .$this->db->quote($settings["template_id"], "integer").","
					   .$this->db->quote($att, "text").
					")
					ON DUPLICATE KEY UPDATE template_id = ".$this->db->quote($settings["template_id"], "integer").",
											attachments = ".$this->db->quote($att, "text");

			$this->db->manipulate($query);
		}
	}

	public function copyTo($a_crs_id) {
		$other = new gevCrsInvitationMailSettings($a_crs_id);

		foreach ($this->settings as $function_name => $settings) {
			$other->setSettingsFor($function_name, $settings["template_id"], $settings["attachments"]);
		}

		$other->save();
	}
}


?>