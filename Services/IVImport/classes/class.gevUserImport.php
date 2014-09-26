<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("./Services/User/classes/class.ilObjUser.php");
require_once("./Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("./Services/GEV/Utils/classes/class.gevRoleUtils.php");
require_once("./Services/GEV/Utils/classes/class.gevSettings.php");
require_once("./Services/GEV/Utils/classes/class.gevUserUtils.php");


/**
 * Class gevUserImport
 *
 * Helper class to import Generali users.
 *
 * @author: Fabian Kochem <fabian.kochem@concepts-and-training.de>
 *
 */
class gevUserImport {
	private $mysql, $ilDB;
	static $instance = null;

	protected function __construct($mysql, $ilDB) {
		$this->mysql = $mysql;
		$this->ilDB = $ilDB;
	}

	public function getInstance($mysql, $ilDB) {
		if (self::$instance !== null) {
			return self::$instance;
		}

		self::$instance = new self($mysql, $ilDB);
		return self::$instance;
	}


	public function registerEVG($stelle, $email) {
		$username = $email;

		if ($this->token_exists_for_email($email)) {
			$token = $this->get_token_for_stelle($stelle);
			if ($token !== null) {
				$this->send_confirmation_email($token);
				return false;
			} else {
				$username = $this->add_counter_to_username($username);
			}
		}

		$shadow_user = $this->get_shadow_user($stelle, $email);
		if ($shadow_user === false) {
			return 'User not found in shadow database.';
		}
		else {
			$iv_data = $this->get_additional_user_data($stelle);
		}
		// WATCH OUT! the email might have been modified in get_shadow_user
		// since #608 was implemented.

		$stellen_data = $this->get_stelle($stelle);
		if ($stellen_data === false) {
			return 'Stelle not found in shadow database';
		}

		if ($stellen_data['agent'] == 1) {
			return 'Stelle is agent.';
		}

		$token = $this->generate_confirmation_token();
		$this->save_token($token, $username, $stelle, $shadow_user["email"]
						 , $iv_data["firstname"], $iv_data["lastname"], $iv_data["gender"]);
		$this->send_confirmation_email($token, $username, $shadow_user["email"]);

		return false;
	}

	public function activate($token) {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		$token_data = $this->get_token_data($token);

		if ($token_data === false) {
			return 'Token not found.';
		}

		if ($this->token_was_used($token)) {
			return 'Token has been used already.';
		}

		$username = $token_data['username'];
		$stelle = $token_data['stelle'];
		$email = $token_data['email'];

		$shadow_user = $this->get_shadow_user($stelle, $email);

		if ($shadow_user === false) {
			return 'Shadow user not found.';
		}

		$ilias_user = $this->create_ilias_user($username, $shadow_user, $token);
		if ($ilias_user === false) {
			return 'User already exists.';
		}
		// make root be the owner of the new user.
		$ilias_user->setOwner(6);

		// user already agreed at registration
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		$ilias_user->setAgreeDate($now->get(IL_CAL_DATETIME));

		$ilias_user->update();
		$this->set_gev_attributes($ilias_user, $shadow_user);
		$this->update_global_role($ilias_user, $shadow_user);
		$this->update_orgunit_role($ilias_user, $shadow_user);

		$ilias_user_id = $ilias_user->getId();
		$this->set_ilias_user_id($shadow_user['sql_adp_id'], $ilias_user_id);

		$this->set_token_used_field($token);
		$this->log_user_in($username, $token);
		return false;
	}


	public function update_imported_shadow_users() {
		global $ilLog;

		$shadow_users = $this->get_imported_shadow_users();
		if (!$shadow_users) {
			return;
		}

		foreach($shadow_users as $ilias_id => $shadow_user) {
			try {
				if (ilObjUser::_lookupFullname($ilias_id) === null) {
					$ilLog->write("Shadow User Update: Couldn't find user ".$ilias_id." in ILIAS database.");
					continue;
				}

				$user = new ilObjUser($ilias_id);
				$this->set_ilias_user_attributes($user, $shadow_user);
				$user->update();
				$this->set_gev_attributes($user, $shadow_user);
				$user->update();
				$this->update_global_role($user, $shadow_user);
				$this->update_orgunit_role($user, $shadow_user);
			} catch (Exception $e) {
				$ilLog->write("Shadow User Update: Error while processing ".$ilias_id.":");
				$ilLog->write($e);
				$ilLog->write(var_export($e->getTraceAsString(), true));
			}
		}
	}

	private function get_imported_shadow_users() {
		$sql = "
			SELECT
				*,
				`ivimport_adp`.`id` AS `adp_id`
			FROM
				`ivimport_adp`
			INNER JOIN
				`ivimport_stelle`
			ON
				( `ivimport_stelle`.`stellennummer` = `ivimport_adp`.`stelle` )
			WHERE
				`ivimport_adp`.`ilias_id` IS NOT NULL
		";
		$result = mysql_query($sql, $this->mysql);

		if ((!$result) || (mysql_num_rows($result) === 0)) {
			return false;
		}

		$ret = array();
		while ($row = mysql_fetch_assoc($result)) {
			$ret[$row['ilias_id']] = $row;
		}

		return $ret;
	}


	private function get_shadow_user($stellennummer, $email) {
		// For some user there should be a tolerance against
		// the domain used for their email address (see ticket #608).
		// We therefore need to check the username used in the email
		// and check the domain afterwards.
		$email_spl = split("@", $email);
		$email_pre = $email_spl[0]."@";

		$sql = "
			SELECT
				*
			FROM
				`ivimport_adp`
			INNER JOIN
				`ivimport_stelle`
			ON
				( `ivimport_stelle`.`sql_adp_id` = `ivimport_adp`.`id` )
			WHERE
				`ivimport_stelle`.`stellennummer`=" . $this->ilDB->quote($stellennummer, "text") . "
			AND
				`ivimport_adp`.`email` LIKE " . $this->ilDB->quote($email_pre."%", "text") . "
			";

		$result = mysql_query($sql, $this->mysql);
		if ((!$result) || (mysql_num_rows($result) !== 1)) {
			return false;
		}

		$row = mysql_fetch_assoc($result);

		// checking of the email tolerance (#608)
		$vermittlerstatus = $row['vermittlerstatus'];
		$email_db = $row['email'];
		$role = gevSettings::$VMS_ROLE_MAPPING[$vermittlerstatus][0];
		$be_tolerant = in_array($role, gevSettings::$EMAIL_TOLERANCE_ROLES);
		if (!$be_tolerant && $email != $email_db) {
			return false;
		}
		if ($be_tolerant) {
			$email_db_spl = split("@", $email_db);
			$valid_domains = array( "service.generali.com"
								  , "service.generali.de"
								  , "generali.com"
								  , "generali.de"
								  );

			if (!in_array($email_spl[1], $valid_domains) ||
				!in_array($email_db_spl[1], $valid_domains)) {
				return false;
			}
			$row["email"] = $email_db_spl[0]."@generali.com";
		}

		return $row;
	}

	private function get_additional_user_data($stellennummer) {
		$sql = "
			SELECT
				`nachname` lastname,
				`vorname` firstname,
				IF (`geschlecht`='M','m','f') gender
			FROM `ivimport_adp`
			INNER JOIN
				`ivimport_stelle`
			ON
				( `ivimport_stelle`.`sql_adp_id` = `ivimport_adp`.`id` )
			WHERE
				`ivimport_stelle`.`stellennummer`=" . $this->ilDB->quote($stellennummer, "text") . "
		";
		$result = mysql_query($sql, $this->mysql);

		if ((!$result) || (mysql_num_rows($result) !== 1)) {
			return array();
		}
		return mysql_fetch_assoc($result);
	}

	public function get_stelle($stellennummer) {
		$sql = "
			SELECT
				*
			FROM
				`ivimport_stelle`
			WHERE
				`stellennummer` = " . $this->ilDB->quote($stellennummer, "text") . "
		";
		$result = mysql_query($sql, $this->mysql);

		if ((!$result) || (mysql_num_rows($result) !== 1)) {
			return false;
		}
		return mysql_fetch_assoc($result);
	}

	private function create_ilias_user($username, $shadow_user, $token) {
		if (ilObjUser::_lookupId($username)) {
			return false;
		}

		$user = new ilObjUser();
		$user->setLogin($username);
		$user->setPasswd($token);

		$this->set_ilias_user_attributes($user, $shadow_user);

		$user->create();
		$user->saveAsNew();
		$user->setOwner(6);
		$user->update();
		return $user;

	}

	private function set_ilias_user_attributes(&$user, $shadow_user) {
		$user->setLastname($shadow_user['nachname']);
		$user->setFirstname($shadow_user['vorname']);
		$user->setEmail($shadow_user['email']);

		if ($shadow_user['geschlecht'] == 'W') {
			$user->setGender('f');
		} else {
			$user->setGender('m');
		}

		$user->setActive(true);
		$user->setTimeLimitUnlimited(true);

		$user->setBirthday($shadow_user['geburtsdatum']);
		$user->setCity($shadow_user['ort']);
		$user->setZipcode($shadow_user['plz']);
		$user->setStreet($shadow_user['strasse']);
		$user->setPhoneOffice($shadow_user['telefon']);
		$user->setFax($shadow_user['fax']);

		$country = null;
		switch ($shadow_user['land']) {
			case 'A':
				$country = 'AT';
				break;

			case 'B':
				$country = 'BE';
				break;

			case 'CH':
				$country = 'CH';
				break;

			case 'D':
				$country = 'DE';
				break;

			case 'F':
				$country = 'FR';
				break;

			case 'GB':
				$country = 'GB';
				break;

			case 'I':
				$country = 'IT';
				break;

			case 'NL':
				$country = 'NL';
				break;

			case 'L':
				$country = 'LU';
				break;

			default:
				break;

		}
		if ($country !== null) {
			$user->setSelectedCountry($country);
		}

		return $user;
	}

	private function set_gev_attributes(&$user, $shadow_user) {
		$utils = gevUserUtils::getInstance($user->getId());

		$utils->setJobNumber($shadow_user['stellennummer']);
		$utils->setADPNumber($shadow_user['adp']);
		$utils->setIHKNumber($shadow_user['ihk']);
		$utils->setADTitle($shadow_user['ad_title']);
		$utils->setAgentKey($shadow_user['vms']);
		$utils->setCompanyTitle($shadow_user['gesellschaftstitel']);
		//$utils->setStatus();
		$utils->setHPE($shadow_user['hpe']);

		$entry_date = $shadow_user['beginn'];
		if ($entry_date) {
			$utils->setEntryDate(new ilDate($entry_date, IL_CAL_DATE));
		}
		$exit_date = $shadow_user['ende'];
		if ($exit_date) {
			$utils->setExitDate(new ilDate($exit_date, IL_CAL_DATE));
		}

		return $user;
	}

	private function update_global_role(&$user, $shadow_user) {
		$user_id = $user->getId();
		$vermittlerstatus = $shadow_user['vermittlerstatus'];
		$role_title = gevSettings::$VMS_ROLE_MAPPING[$vermittlerstatus][0];
		$utils = gevRoleUtils::getInstance();
		$utils->assignUserToGlobalRole($user_id, $role_title);

		$sql = "
			SELECT
				`global_role_title`
			FROM
				`ivimport_roleassignment`
			WHERE
				`ilias_id` = " . $this->ilDB->quote($user_id, "integer") . "
			AND
				`global_role_title` IS NOT NULL
		";
		$result = mysql_query($sql, $this->mysql);
		if (($result) && (mysql_num_rows($result) === 1)) {
			$row = mysql_fetch_assoc($result);
			$saved_role_title = $row['global_role_title'];
			if ($saved_role_title != $role_title) {
				//echo "deassign ".$user_id." ".$saved_role_title;
				$utils->deassignUserFromGlobalRole($user_id, $saved_role_title);
			}
		}

		$sql = "
			INSERT INTO
				`ivimport_roleassignment`
			(
				`ilias_id`,
				`global_role_title`
			)
			VALUES (
				" . $this->ilDB->quote($user_id, "integer") . ",
				" . $this->ilDB->quote($role_title, "text") . "
			)
			ON DUPLICATE KEY UPDATE
				`global_role_title`=VALUES(`global_role_title`)
		";
		$result = mysql_query($sql, $this->mysql);
		if (!$result) {
			throw new Exception("Could not write global role into shadow db.");
		}
	}

	private function update_orgunit_role(&$user, $shadow_user) {
		$user_id = $user->getId();
		$vermittlerstatus = $shadow_user['vermittlerstatus'];
		$role_title = gevSettings::$VMS_ROLE_MAPPING[$vermittlerstatus][1];
		$orgunit_import_id = $shadow_user['org_unit'];
		$orgunit_id = ilObjOrgUnit::_lookupObjIdByImportId($orgunit_import_id);
		if (!$orgunit_id) {
			throw new Exception("Could not determine obj_id for org unit with import id '".$orgunit_import_id."'");
		}
		$new_utils = gevOrgUnitUtils::getInstance($orgunit_id);
		$new_utils->getOrgUnitInstance();
		$new_utils->assignUser($user_id, $role_title);

		$sql = "
			SELECT
				`orgunit_id`,
				`orgunit_role_title`
			FROM
				`ivimport_roleassignment`
			WHERE
				`ilias_id` = " . $this->ilDB->quote($user_id, "integer") . "
			AND
				`orgunit_id` IS NOT NULL
			AND
				`orgunit_role_title` IS NOT NULL
		";

		$result = mysql_query($sql, $this->mysql);
		if (($result) && (mysql_num_rows($result) === 1)) {
			$row = mysql_fetch_assoc($result);
			$saved_orgunit_id = $row['orgunit_id'];
			$saved_role_title = $row['orgunit_role_title'];

			if (($saved_orgunit_id != $orgunit_id) || ($saved_role_title != $role_title)) {
				//echo "deassign          ". $user_id . " " . $saved_role_title . "\n";
				$old_utils = gevOrgUnitUtils::getInstance($saved_orgunit_id);
				$old_utils->getOrgUnitInstance();
				$old_utils->deassignUser($user_id, $saved_role_title);
			}
		}

		$sql = "
			INSERT INTO
				`ivimport_roleassignment`
			(
				`ilias_id`,
				`orgunit_id`,
				`orgunit_role_title`
			)
			VALUES (
				" . $this->ilDB->quote($user_id, "integer") . ",
				" . $this->ilDB->quote($orgunit_id, "integer") . ",
				" . $this->ilDB->quote($role_title, "text") . "
			)
			ON DUPLICATE KEY UPDATE
				`orgunit_id`=VALUES(`orgunit_id`),
				`orgunit_role_title`=VALUES(`orgunit_role_title`)
		";
		$result = mysql_query($sql, $this->mysql);
		if (!$result) {
			throw new Exception("Could not write global role into shadow db.");
		}
	}


	private function send_confirmation_email($token) {
		require_once("Services/GEV/Mailing/classes/class.gevRegistrationMails.php");
		require_once("Services/Utilities/classes/class.ilUtil.php");
		$link = ilUtil::_getHttpPath()."/gev_activate_user.php?token=".$token;
		$reg_mails = new gevRegistrationMails($link, $token);
		$reg_mails->getAutoMail("evg_activation")->send();
		$this->set_email_sent_field($token);
	}

	private function set_email_sent_field($token) {
		$sql = "
			UPDATE
				`gev_user_reg_tokens`
			SET
				`email_sent`=NOW()
			WHERE
				`token`=" . $this->ilDB->quote($token, "text") . ";
		";

		$result = $this->ilDB->query($sql);
		return $this->ilDB->numRows($result) === 1;
	}

	public function set_token_used_field($token) {
		$sql = "
			UPDATE
				`gev_user_reg_tokens`
			SET
				`token_used`=NOW()
			WHERE
				`token`=" . $this->ilDB->quote($token, "text") . ";
		";

		$result = $this->ilDB->query($sql);
		return $this->ilDB->numRows($result) === 1;
	}

	private function add_counter_to_username($username) {
		$wildcard = $username . "%";
		$sql = "
			SELECT
				`username`
			FROM
				`gev_user_reg_tokens`
			WHERE
				`username` LIKE " . $this->ilDB->quote($wildcard, "text") . "

			UNION SELECT `login` username
			FROM
				`usr_data`
			WHERE
				`login` LIKE " . $this->ilDB->quote($wildcard, "text") . "
		";

		$result = $this->ilDB->query($sql);

		$highest_count = 1;

		while ($row = $this->ilDB->fetchAssoc($result)) {
			$username = $row['username'];
			if (preg_match("/(.*[^0-9])([0-9]*)$/", $username, $match)) {
				$email = $match[1];
				$counter = $match[2];

				if (($counter) && ($counter >= $highest_count)) {
					$highest_count = $counter + 1;
				}
			}
		}

		return $email . $highest_count;
	}

	private function set_ilias_user_id($shadow_user_id, $ilias_user_id) {
		$sql = "
			UPDATE
				`ivimport_adp`
			SET
				`ilias_id`=" . $this->ilDB->quote($ilias_user_id, "integer") . "
			WHERE
				`id`=" . $this->ilDB->quote($shadow_user_id, "integer") . "
		";

		global $ilLog;
		$ilLog->write($sql);

		return mysql_query($sql, $this->mysql) === 1;
	}


	private function generate_confirmation_token($max_attempts=10) {
		$found_token = false;
		$attempt = 0;

		while (!$found_token) {
			$token = md5(rand());
			if ($this->token_is_usable($token)) {
				$found_token = true;
			}

			if ($attempt > $max_attempts) {
				die('Number of maximum attempts has been reached.');
			}
			$attempt++;
		}

		return $token;
	}

	private function token_is_usable($token) {
		$sql = "
			SELECT
				*
			FROM
				`gev_user_reg_tokens`
			WHERE
				`token`=" . $this->ilDB->quote($token, "text") . ";
		";

		$result = $this->ilDB->query($sql);
		return $this->ilDB->numRows($result) === 0;
	}

	private function token_was_used($token) {
		$sql = "
			SELECT
				`token_used`
			FROM
				`gev_user_reg_tokens`
			WHERE
				`token`=" . $this->ilDB->quote($token, "text") . "
			AND
				`token_used` IS NOT NULL;
		";

		$result = $this->ilDB->query($sql);
		return $this->ilDB->numRows($result) > 0;
	}

	private function token_exists_for_email($email) {
		$sql = "
			SELECT
				*
			FROM
				`gev_user_reg_tokens`
			WHERE
				`email`=" . $this->ilDB->quote($email, "text") . ";
		";

		$result = $this->ilDB->query($sql);
		return $this->ilDB->numRows($result) > 0;
	}

	private function token_exists_for_stelle($stelle) {
		$sql = "
			SELECT
				*
			FROM
				`gev_user_reg_tokens`
			WHERE
				`stelle`=" . $this->ilDB->quote($stelle, "text") . ";
		";

		$result = $this->ilDB->query($sql);
		return $this->ilDB->numRows($result) > 0;
	}

	private function get_token_for_stelle($stelle) {
		$sql = "
			SELECT
				token
			FROM
				`gev_user_reg_tokens`
			WHERE
				`stelle`=" . $this->ilDB->quote($stelle, "text") . ";
		";

		$result = $this->ilDB->query($sql);
		if ($rec = $this->ilDB->fetchAssoc($result)) {
			return $rec["token"];
		}
		return null;
	}

	private function get_token_data($token) {
		$sql = "
			SELECT
				*
			FROM
				`gev_user_reg_tokens`
			WHERE
				`token`=" . $this->ilDB->quote($token, "text") . "
		";

		$result = $this->ilDB->query($sql);

		if ((!$result) || ($this->ilDB->numRows($result) !== 1)) {
			return false;
		}

		while ($row = $this->ilDB->fetchAssoc($result)) {
			return $row;
		}
	}

	private function save_token($token, $username, $stelle, $email, $firstname, $lastname, $gender) {
		$sql = "
			INSERT INTO
				`gev_user_reg_tokens`
			(
				`token` ,
				`stelle` ,
				`username` ,
				`email` ,
				`firstname` ,
				`lastname` ,
				`gender`
			)
			VALUES (
				" . $this->ilDB->quote($token, "text") . ",
				" . $this->ilDB->quote($stelle, "text") . ",
				" . $this->ilDB->quote($username, "text") . ",
				" . $this->ilDB->quote($email, "text") . ",
				" . $this->ilDB->quote($firstname, "text") . ",
				" . $this->ilDB->quote($lastname, "text") . ",
				" . $this->ilDB->quote($gender, "text"). "
			);
		";

		return $this->ilDB->query($sql) === 1;
	}

	private function log_user_in($username, $password) {
		$_POST["username"] = $username;
		$_POST["password"] = $password;
		$_POST["cmd"]["showLogin"] = "Anmelden";
		$_GET["lang"] = "de";
		$_GET["cmd"] = "post";
		$_GET["cmdClass"] = "ilstartupgui";
		$_GET["cmd"] = "post";
		$_GET["baseClass"] = "ilStartupGUI";

		require_once("ilias.php");
	}
}

?>
