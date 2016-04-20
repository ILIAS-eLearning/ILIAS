<?php

require_once "./Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.reportSettings.php";

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class SettingsTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		global $ilDB;
		$this->db = $ilDB;

		date_default_timezone_set("Europe/Berlin");
	}

	public function test_Create() {
		$settings = reportSettings::create()
			->setTable('table')
			->defineSetting('id')
				->withFormat('text')
					->withPostprocessing(function ($val) {return $val;})
			->defineSetting('another_id')
				->withGUI('double')
					->withPostprocessing(function ($val) {return $val;})
			->definitionFinished();
		$ids = $settings->settingsIds();
		$ref = array('id','another_id');
		$this->assertTrue(count(array_diff($ref,$ids)) === 0 && count(array_diff($ids,$ref)) === 0);
	}

	public function test_DB() {
		$db_access = new reportSettingsDBAccess($this->db);
		$settings = reportSettings::create()
			->setTable('table')
			->defineSetting('id')
				->withFormat('text')
					->withPostprocessing(function ($val) {return $val;})
			->defineSetting('another_id')
				->withFormat('float')
					->withPostprocessing(function ($val) {return $val;})
			->definitionFinished();
		$this->assertEqual(serialize(array("table" => "table",'id'=>'id_val','another_id'=>1.23)),
			$db_access->store($settings,array('id'=>$this->db->quote('id_val','text'),'another_id'=>$this->db->quote(1.23,'float')))); 
	}

	public function test_Render() {
		$form = new ilPropertyFormGUI();
		$render = new reportSettingsRender($form);
		$settings = reportSettings::create()
			->setTable('table')
			->defineSetting('id')
				->withFormat('text')
					->withPostprocessing(function ($val) {return $val;})
			->defineSetting('another_id')
				->withFormat('float')
					->withPostprocessing(function ($val) {return $val;})
			->definitionFinished();
		$this->assertEqual(serialize(array("table" => "table",'id'=>'id_val','another_id'=>1.23)),
			$render->render($settings,array('id'=>'id_val','another_id'=>1.23))); 
	}
}