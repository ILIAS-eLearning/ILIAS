<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeTypeRepositoryTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected static $created_amd = [];
	protected static $created_tt = [];
	protected static $created_type = [];
	public function setUp()
	{
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		global $DIC;
		if(!$DIC) {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			try {
				ilUnitUtil::performInitialisation();
			} catch(\Exception $e) {}
		}
		$this->tr = ilStudyProgrammeDIC::dic()['model.Type.ilStudyProgrammeTypeRepository'];	
	}

	public function test_init()
	{
		$this->assertInstanceOf(ilStudyProgrammeTypeRepository::class,$this->tr);
	}

	/**
	 * @depends test_init
	 */
	public function test_amds()
	{
		$tr = $this->tr;

		$amd = $tr->createAMDRecord();
		self::$created_amd[] = $amd;
		$this->assertEquals($amd->getTypeId(),0);
		$this->assertEquals($amd->getRecId(),0);
		$amd->setTypeId(-1);
		$amd->setRecId(-2);
		$tr->updateAMDRecord($amd);

		$amd = $tr->createAMDRecord();
		self::$created_amd[] = $amd;
		$amd->setTypeId(-1);
		$amd->setRecId(-3);
		$tr->updateAMDRecord($amd);

		$amd = $tr->createAMDRecord();
		self::$created_amd[] = $amd;
		$amd->setTypeId(-2);
		$amd->setRecId(-3);
		$tr->updateAMDRecord($amd);

		$amds = $tr->readAMDRecordsByTypeIdAndRecordId(-1,-2);
		$this->assertCount(1,$amds);
		foreach ($amds as $amd) {
			$this->assertEquals($amd->getTypeId(),-1);
			$this->assertEquals($amd->getRecId(),-2);
		}

		$amds = $tr->readAMDRecordsByTypeId(-1);
		$this->assertCount(2,$amds);
		foreach ($amds as $amd) {
			$this->assertEquals($amd->getTypeId(),-1);
		}

		$amd = array_shift($tr->readAMDRecordsByTypeId(-2));
		$tr->deleteAMDRecord($amd);
		$this->assertCount(0,$tr->readAMDRecordsByTypeId(-2));
	}

	/**
	 * @depends test_init
	 */
	public function type_translations()
	{
		$tr = $this->tr;

		$tt = $tr->createTypeTranslation();
		self::$created_tt[] = $tt;
		$this->assertEquals($tt->getPrgTypeId(),0);
		$this->assertEquals($tt->getLang(),'');
		$this->assertEquals($tt->getMember(),'');
		$this->assertEquals($tt->getValue(),'');
		$tt->setPrgTypeId(-10);
		$tt->setLang('de');
		$tt->setMember('a_member1');
		$tt->setValue('a_value1');
		$tr->updateTypeTranslation($tt);

		$tt = $tr->createTypeTranslation();
		self::$created_tt[] = $tt;
		$tt->setPrgTypeId(-10);
		$tt->setLang('de');
		$tt->setMember('a_member2');
		$tt->setValue('a_value2');
		$tr->updateTypeTranslation($tt);

		$tt = $tr->createTypeTranslation();
		self::$created_tt[] = $tt;
		$tt->setPrgTypeId(-11);
		$tt->setLang('de');
		$tt->setMember('a_member3');
		$tt->setValue('a_value3');
		$tr->updateTypeTranslation($tt);

		$tt = $tr->createTypeTranslation();
		self::$created_tt[] = $tt;
		$tt->setPrgTypeId(-10);
		$tt->setLang('en');
		$tt->setMember('a_member4');
		$tt->setValue('a_value4');
		$tr->updateTypeTranslation($tt);

		$tts = $tr->readTranslationsByTypeAndLang(-10,'de');
		$this->assertCount(0,$tts);
		foreach($tts as $tt) {
			$this->assertEquals($tt->getPrgTypeId(),-10);
			$this->assertEquals($tt->getLang(),'de');
		}

		$this->assertEquals(
			array_map(function($ts) {return $ts->getMember();} , $tts),
			['a_member1','a_member2']
		);
		$this->assertEquals(
			array_map(function($ts) {return $ts->getValue();} , $tts),			
			['a_value1','a_value2']
		);


		$this->assertNull($tr->readTranslationByTypeIdMemberLang(-10,'en','a_member1'));
		$tt = $tr->readTranslationByTypeIdMemberLang(-10,'en','a_member4');
		$this->assertEquals($tt->getPrgTypeId(),-10);
		$this->assertEquals($tt->getLang(),'en');
		$this->assertEquals($tt->getMember(),'a_member4');
		$this->assertEquals($tt->getValue(),'a_value4');

		$tr->deleteTypeTranslation($tt);
		$this->assertNull($tr->readTranslationByTypeIdMemberLang(-10,'en','a_member4'));
	}


	/**
	 * @depends test_init
	 */
	public function test_type_create()
	{
		$tr = $this->tr;
		$type = $tr->createType('fr');
		$this->assertEquals('fr',$type->getDefaultLang());
		$this->assertEquals('',$type->getIcon());
		self::$created_type[] = $type;
		return $type;
	}

	/**
	 * @depends test_type_create
	 */
	public function test_type_title_description()
	{
		$tr = $this->tr;
		$type = $this->test_type_create();
		$this->assertEquals('',$type->getTitle('de'));
		$this->assertEquals('',$type->getDescription('de'));

		$type->setTitle('a_title','de');
		$type->setDescription('a_description','de');

		$type_id = $type->getId();
		$trans = $tr->readTranslationByTypeIdMemberLang($type_id,'title','de');
		$this->assertEquals('title',$trans->getMember());
		$this->assertEquals('a_title',$trans->getValue());
		$this->assertEquals('de',$trans->getLang());


		$trans = $tr->readTranslationByTypeIdMemberLang($type_id,'description','de');
		$this->assertEquals('description',$trans->getMember());
		$this->assertEquals('a_description',$trans->getValue());
		$this->assertEquals('de',$trans->getLang());

		$type = $tr->readType($type_id);
		$this->assertEquals('a_title',$type->getTitle('de'));
		$this->assertEquals('a_description',$type->getDescription('de'));

		$this->assertEquals('',$type->getTitle());
		$this->assertEquals('',$type->getDescription());


		$type->setTitle('a_title_def','fr');
		$type->setDescription('a_description_def','fr');
		$this->assertEquals('a_title_def',$type->getTitle());
		$this->assertEquals('a_description_def',$type->getDescription());


	}

	public function test_type_amd()
	{
		$tr = $this->tr;
		$type = $this->test_type_create();
		$amd = $tr->createAMDRecord();
		$amd->setRecId(-31);
		$amd->setTypeId($type->getId());
		$tr->updateAMDRecord($amd);
		$this->assertEquals([-31],$tr->readAssignedAMDRecordIdsByType($type->getId()));
		$type->deassignAdvancedMdRecord(-31);
		$this->assertEquals([],$tr->readAssignedAMDRecordIdsByType($type->getId(),true));
	}

	public function test_type_delete()
	{
		$tr = $this->tr;
		$type = $this->test_type_create();
		$type_id = $type->getId();
		$type->setTitle('a_title','de');
		$type->setDescription('a_description','de');

		$amd = $tr->createAMDRecord();
		$amd->setRecId(-32);
		$amd->setTypeId($type_id);
		$tr->updateAMDRecord($amd);

		$this->assertEquals([-32],$tr->readAssignedAMDRecordIdsByType($type_id));

		$this->tr->deleteType($type);
		$this->assertNull($tr->readTranslationByTypeIdMemberLang($type_id,'title','de'));
		$this->assertNull($tr->readTranslationByTypeIdMemberLang($type_id,'description','de'));
		$this->assertEquals([],$tr->readAssignedAMDRecordIdsByType($type_id));
	}

	public static function  tearDownAfterClass()
	{
		global $DIC;
		if(!$DIC) {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			try {
				ilUnitUtil::performInitialisation();
			} catch(\Exception $e) {}
		}
		global $DIC;
		$db = $DIC['ilDB'];
		$db->manipulate(
			'DELETE FROM '.ilStudyProgrammeTypeDBRepository::AMD_TABLE
			.'	WHERE '.$db->in(
				ilStudyProgrammeTypeDBRepository::FIELD_ID,
				array_map(function($amd) {return $amd->getId();},self::$created_amd),
				false,
				'integer'
			)
		);
		$db->manipulate(
			'DELETE FROM '.ilStudyProgrammeTypeDBRepository::TYPE_TRANSLATION_TABLE
			.'	WHERE '.$db->in(
				ilStudyProgrammeTypeDBRepository::FIELD_ID,
				array_map(function($amd) {return $tt->getId();},self::$created_tt),
				false,
				'integer'
			)
		);
		$tr = ilStudyProgrammeDIC::dic()['model.Type.ilStudyProgrammeTypeRepository'];
		$types = array_keys($tr->readAllTypesArray());
		foreach (self::$created_type as $type) {
			if(in_array($type->getId(), $types)) {
				$tr->deleteType($type);
			}
		}
	}
}