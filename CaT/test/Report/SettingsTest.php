<?php

require_once "./Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingFactory.php";
require_once "./Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/class.ilReportMasterPlugin.php";
/* Copyright (c) 2016 Denis Klöpfer, Extended GPL, see docs/LICENSE */

/**
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */

class SettingsTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		global $ilDB;
		$this->db = $ilDB;
		$this->s_f = new settingFactory($this->db);
		$this->master_plugin = new ilReportMasterPlugin();
		date_default_timezone_set("Europe/Berlin");
	}

	public function test_Create() {
		$settings = $this->s_f->reportSettings('rep_master_data')
							->addSetting($this->s_f->settingBool('is_online',"is_online_name"))
							->addSetting($this->s_f->settingString('video_link',"pdf_link_name")
											   ->setFromForm(function ($string) {
													if(preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1) {
														return $string;
													}
													return 'https://'.$string;
												}
											)
										);
		$name = $settings->setting('is_online')->name();
		$redid = call_user_func($settings->setting('video_link')->fromForm(),'www.google.de');
		$this->assertEquals($name, "is_online_name");
		$this->assertEquals($redid, 'https://www.google.de');
	}

	/**
	 * @expectedException reportSettingsException
	 */
	public function test_Create_WrongTables() {
		$settings = $this->s_f->reportSettings('rep_master_data2')
						->addSetting($this->s_f->settingBool('is_online',"is_online_name"))
						->addSetting($this->s_f->settingString('video_link',"pdf_link_name")
											   ->setFromForm(function ($string) {
													if(preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1) {
														return $string;
													}
													return 'https://'.$string;
												}
											)
										);

		$this->asserFalse('should have failed');
	}

	public function test_forms() {

	}
}