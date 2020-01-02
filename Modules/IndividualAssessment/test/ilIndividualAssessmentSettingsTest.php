<?php

require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentSettings.php';
require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentSettingsStorageDB.php';
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';

/**
 * @backupGlobals disabled
 * @group needsInstalledILIAS
 */
class ilIndividualAssessmentSettingsTest extends PHPUnit_Framework_TestCase
{
    public static $iass;
    public static $iass_id;
    public static $storage;
    public static $db;

    public static function setUpBeforeClass()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
        self::$iass = new ilObjIndividualAssessment;
        self::$iass ->setTitle("iass_test");
        self::$iass ->setDescription("iass_test_desc");
        self::$iass ->create();
        self::$iass ->createReference();
        self::$iass ->putInTree(ROOT_FOLDER_ID);
        self::$iass_id = self::$iass->getId();
        global $ilDB;
        self::$storage = new ilIndividualAssessmentSettingsStorageDB($ilDB);
    }


    public function test_create_settings()
    {
        $settings = self::$storage->loadSettings(self::$iass);
        $content = $settings->content();
        $record_template = $settings->recordTemplate();
        $this->assertEquals($content, ilIndividualAssessmentSettings::DEF_CONTENT);
        $this->assertEquals($record_template, ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
        return $settings;
    }

    /**
     * @depends test_create_settings
     */
    public function test_settings_change($settings)
    {
        $settings = $settings->setContent('some_content')->setRecordTemplate('some_template');
        $this->assertEquals($settings->content(), 'some_content');
        $this->assertEquals($settings->recordTemplate(), 'some_template');
        self::$storage->updateSettings($settings);
    }

    /**
     * @depends test_settings_change
     */
    public function test_settings_load()
    {
        $settings = self::$storage->loadSettings(self::$iass);
        $this->assertEquals($settings->content(), 'some_content');
        $this->assertEquals($settings->recordTemplate(), 'some_template');
        return $settings;
    }

    /**
     * @depends test_settings_load
     */
    public function test_settings_update()
    {
        $iass = new ilObjIndividualAssessment(self::$iass_id, false);
        $settings = $iass->getSettings();
        $this->assertEquals($settings->content(), 'some_content');
        $this->assertEquals($settings->recordTemplate(), 'some_template');
        $settings = $settings->setContent('some_content2')->setRecordTemplate('some_template2');
        $iass->update();
        $iass = new ilObjIndividualAssessment(self::$iass_id, false);
        $settings = $iass->getSettings();
        $this->assertEquals($settings->content(), 'some_content2');
        $this->assertEquals($settings->recordTemplate(), 'some_template2');
        return $settings;
    }


    /**
     * @depends test_settings_update
     */
    public function test_settings_delete($settings)
    {
        self::$iass->delete();
        $settings = self::$storage->loadSettings(self::$iass);
        $this->assertEquals($settings->content(), '');
        $this->assertEquals($settings->recordTemplate(), '');
    }
}
