<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeTypeTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

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
		$this->db = $DIC['ilDB'];
		$this->filesystem = $DIC->filesystem()->web();
		$this->user = $DIC['ilUser'];
		$this->plugin_admin = $DIC['ilPluginAdmin'];
		$this->lng = $DIC['lng'];
		$this->settings_repo = new ilStudyProgrammeSettingsDBRepository($this->db);
		$this->type_repo = new ilStudyProgrammeTypeDBRepository(
			$this->db,
			$this->settings_repo,
			$this->filesystem,
			$this->user,
			$this->plugin_admin,
			$this->lng
		);
	}

	public function test_init_and_id()
	{
		$t = new ilStudyProgrammeType(
			1,
			$this->type_repo,
			$this->filesystem,
			$this->plugin_admin,
			$this->lng,
			$this->user
		);
		$this->assertEquals(1,$t->getId());
		return $t;
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_owner($t)
	{
		$this->assertNull($t->getOwner());
		$t->setOwner(6);
		$this->assertEquals(6,$t->getOwner());
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_set_icon($t)
	{
		$this->assertNull($t->getIcon());
		$t->setIcon('some_svg_icon.svg');
		$this->assertEquals('some_svg_icon.svg',$t->getIcon());
	}
	/**
	 * @depends test_init_and_id
	 * @depends test_set_icon
	 */
	public function test_icon_path()
	{
		$t = $this->test_init_and_id();
		$t->setIcon('some_svg_icon.svg');
		$this->assertRegexp('#^'.ilStudyProgrammeType::WEB_DATA_FOLDER.'\\/type\\_'.'#',$t->getIconPath());
		$this->assertRegexp('#^'.ilStudyProgrammeType::WEB_DATA_FOLDER.'\\/type\\_'.'#',$t->getIconPath(true));
		$this->assertRegexp('#some_svg_icon.svg$#',$t->getIconPath(true));
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilStudyProgrammeTypeException
	 */
	public function test_icon_failure($t)
	{
		$t->setIcon('some_non_svg_icon.png');
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_default_lang($t)
	{
		$this->assertEquals('',$t->getDefaultLang());
		$t->setDefaultLang('de');
		$this->assertEquals('de',$t->getDefaultLang());
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_create_date($t)
	{
		$this->assertNull($t->getCreateDate());
		$t->setCreateDate(new ilDateTime('2018-01-02 01:02:03',IL_CAL_DATETIME));
		$this->assertEquals($t->getCreateDate()->get(IL_CAL_DATETIME),'2018-01-02 01:02:03');
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_repository($t)
	{
		$this->assertInstanceOf(ilStudyProgrammeTypeRepository::class,$t->getRepository());
	}

}