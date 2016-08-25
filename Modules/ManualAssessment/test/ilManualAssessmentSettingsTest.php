<?php

require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettings.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettingsStorageDB.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';

/**
 * @backupGlobals disabled
 */
class ilManualAssessmentSettingsTest extends PHPUnit_Framework_TestCase {
	public static $mass;
	public static $mass_id;
	public static $storage;
	public static $db;

	public static function setUpBeforeClass() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		self::$mass = new ilObjManualAssessment;
		self::$mass ->setTitle("mass_test");
		self::$mass ->setDescription("mass_test_desc");
		self::$mass ->create();
		self::$mass ->createReference();
		self::$mass ->putInTree(ROOT_FOLDER_ID);
		self::$mass_id = self::$mass->getId();
		global $ilDB;
		self::$storage = new ilManualAssessmentSettingsStorageDB($ilDB);
	}


	public function test_create_settings() {
		$settings = self::$storage->loadSettings(self::$mass);
		$content = $settings->content();
		$record_template = $settings->recordTemplate();
		$this->assertEquals($content, ilManualAssessmentSettings::DEF_CONTENT);
		$this->assertEquals($record_template, ilManualAssessmentSettings::DEF_RECORD_TEMPLATE);
		return $settings;
	}

	/**
	 * @depends test_create_settings
	 */
	public function test_settings_change($settings) {
		$settings = $settings->setContent('some_content')->setRecordTemplate('some_template');
		$this->assertEquals($settings->content(),'some_content');
		$this->assertEquals($settings->recordTemplate(),'some_template');
		self::$storage->updateSettings($settings);
	}

	/**
	 * @depends test_settings_change
	 */ 
	public function test_settings_load() {
		$settings = self::$storage->loadSettings(self::$mass);
		$this->assertEquals($settings->content(),'some_content');
		$this->assertEquals($settings->recordTemplate(),'some_template');
		return $settings;
	}

	/**
	 * @depends test_settings_load
	 */ 
	public function test_settings_update() {
		$mass = new ilObjManualAssessment(self::$mass_id,false);
		$settings = $mass->getSettings();
		$this->assertEquals($settings->content(),'some_content');
		$this->assertEquals($settings->recordTemplate(),'some_template');
		$settings = $settings->setContent('some_content2')->setRecordTemplate('some_template2');
		$mass->update();
		$mass = new ilObjManualAssessment(self::$mass_id,false);
		$settings = $mass->getSettings();
		$this->assertEquals($settings->content(),'some_content2');
		$this->assertEquals($settings->recordTemplate(),'some_template2');
		return $settings;
	}


	/**
	 * @depends test_settings_update
	 */ 
	public function test_settings_delete($settings) {
		self::$mass->delete();
		$settings = self::$storage->loadSettings(self::$mass);
		$this->assertEquals($settings->content(),'');
		$this->assertEquals($settings->recordTemplate(),'');
	}
}