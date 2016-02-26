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
	protected $no_mail_standard_function_names;
	protected $no_mail_standard_template_id;

	public function __construct($a_crs_id) {
		global $ilDB, $ilCtrl;
		$this->db = &$ilDB;

		$this->crs_id = $a_crs_id;
		$this->attachments_path = null;
		$this->template_api = null;

		$this->no_mail_standard_function_names = array("Trainer", "Pool Trainingsersteller");
		$this->no_mail_standard_template_id = -2;

		$this->read();
	}

	// Returns the id of the template set for the function (e.g. Trainer,
	// Mitglied, ...).
	// Return -1 if none is set.
	public function getTemplateFor($a_local_role_name) {
		if(array_key_exists($a_local_role_name, $this->settings)) {
			return $this->settings[$a_local_role_name]["template_id"];
		}

		/*IF there is no template for searched function_name 
		* AND function_name euqals tutor standard function name
		* return tutor standard template id
		*/
		if(in_array($a_local_role_name, $this->no_mail_standard_function_names)) {
			return $this->no_mail_standard_template_id;
		}

		return -1;
	}

	// Get names of attached files.
	public function getAttachmentNamesFor($a_local_role_name) {
		if (array_key_exists($a_local_role_name, $this->settings)) {
			return $this->settings[$a_local_role_name]["attachments"];
		}

		return array();
	}

	// Get names of attached files.
	public function getAttachmentsFor($a_local_role_name) {
		return $this->attachmentNamesToCompleteArray(
						$this->getAttachmentNamesFor($a_local_role_name));
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

	public function setBasicSettings() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_utils = gevCourseUtils::getInstance($this->crs_id);
		$functions = $crs_utils->getFunctionsForInvitationMails();

		foreach ($functions as $function) {
			$this->settings[$function]["template_id"] = $this->getTemplateFor($function);
		}
	}

	public function setSettingsFor($a_function_name, $a_template_id, $a_attachments) {
		//TODO: check validity of function_name here?		
		if (!array_key_exists($a_function_name, $this->settings)) {
			$this->settings[$a_function_name] = array();
		}
		$this->settings[$a_function_name]["template_id"] = $a_template_id;
		$this->settings[$a_function_name]["attachments"] = $a_attachments;
	}

	public function addCustomAttachments($function_name, array $attachments) {
		if (!array_key_exists($function_name, $this->settings)) {
			$this->settings[$function_name] = array();
		}

		$current_attachments = $this->settings[$function_name]["attachments"];
		if(!$current_attachments) {
			$current_attachments = array();
		}
		
		$new = array_unique($current_attachments + $attachments);
		sort($new);
		$this->settings[$function_name]["attachments"] = $new;
	}

	public function removeCustomAttachment($function_name, $attachments) {
		if (!array_key_exists($function_name, $this->settings)) {
			return;
		}

		$current_attachments = $this->settings[$function_name]["attachments"];
		if(!$current_attachments) {
			return;
		}
		$this->settings[$function_name]["attachments"] = array_diff($current_attachments, $attachments);
	}

	/**
	 * Returns a dictionary with $template_id => $title.
	 */
	public function getInvitationMailTemplates() {
		require_once("./Services/MailTemplates/classes/class.ilMailTemplateManagementAPI.php");
		require_once("./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php");

		if ($this->template_api === null) {
			$this->template_api = new ilMailTemplateManagementAPI();
		}

		// TODO: "Einladungsmail" is magic. Better use some setting...
		$templates = $this->template_api->getTemplateCategoriesByType(self::$template_type);

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

		if(empty($this->settings)) {
			$this->setBasicSettings();
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
		//Speichert die aktuellen Settings
		$new_settings = $this->settings;
		//Liest die alten erneut ein
		$this->read();
		//Unlocked die alten Anhänge
		$this->unlockAttachments();
		//Setz wieder wieder die aktuellen Settings
		$this->settings = $new_settings;
		//Locked die neuen Anhänge
		$this->lockAttachments();

		foreach ($this->settings as $function_name => $settings) {
			if($function_name == "standard" && !array_key_exists("template_id",$settings)) {
				$settings["template_id"] = -1;
				
			}

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