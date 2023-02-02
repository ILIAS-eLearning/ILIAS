<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeSettingsRepositoryTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;
    protected static $created;

    protected function setUp(): void
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        global $DIC;
        if (!$DIC) {
            try {
                include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
                ilUnitUtil::performInitialisation();
            } catch (Exception $e) {
            }
        }
        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->tps = $this->createMock(ilOrgUnitObjectTypePositionSetting::class);
        $this->tps->method('getActivationDefault')
            ->willReturn(true);
    }

    public function test_init()
    {
        $repo = new ilStudyProgrammeSettingsDBRepository(
            $this->db,
            $this->tps
        );
        $this->assertInstanceOf(ilStudyProgrammeSettingsRepository::class, $repo);
        return $repo;
    }

    /**
     * @depends test_init
     */
    public function testPRGRepoCreate($repo)
    {
        $set = $repo->createFor(-1);
        $this->assertEquals($set->getSubtypeId(), ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
        $this->assertEquals($set->getStatus(), ilStudyProgrammeSettings::STATUS_DRAFT);
        $this->assertEquals($set->getLPMode(), ilStudyProgrammeSettings::MODE_UNDEFINED);
        $this->assertEquals($set->getPoints(), ilStudyProgrammeSettings::DEFAULT_POINTS);
        $this->assertEquals($set->getDeadlinePeriod(), 0);
        $this->assertNull($set->getDeadlineDate());
        $this->assertEquals($set->getValidityOfQualificationPeriod(), ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
        $this->assertNull($set->getValidityOfQualificationDate());
        $this->assertEquals($set->getRestartPeriod(), ilStudyProgrammeSettings::NO_RESTART);
    }

    /**
     * @depends test_create
     */
    public function testPRGRepoEditAndUpdate()
    {
        $repo = new ilStudyProgrammeSettingsDBRepository(
            $this->db,
            $this->tps
        );
        $set = $repo->get(-1);
        $this->assertEquals($set->getSubtypeId(), ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
        $this->assertEquals($set->getStatus(), ilStudyProgrammeSettings::STATUS_DRAFT);
        $this->assertEquals($set->getLPMode(), ilStudyProgrammeSettings::MODE_UNDEFINED);
        $this->assertEquals($set->getPoints(), ilStudyProgrammeSettings::DEFAULT_POINTS);
        $this->assertEquals($set->getDeadlinePeriod(), 0);
        $this->assertNull($set->getDeadlineDate());
        $this->assertEquals($set->getValidityOfQualificationPeriod(), ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
        $this->assertNull($set->getValidityOfQualificationDate());
        $this->assertEquals($set->getRestartPeriod(), ilStudyProgrammeSettings::NO_RESTART);

        $repo = new ilStudyProgrammeSettingsDBRepository(
            $this->db,
            $this->tps
        );
        ilStudyProgrammeSettingsDBRepository::clearCache();
        $set = $repo->get(-1);
        $this->assertEquals($set->getSubtypeId(), ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
        $this->assertEquals($set->getStatus(), ilStudyProgrammeSettings::STATUS_DRAFT);
        $this->assertEquals($set->getLPMode(), ilStudyProgrammeSettings::MODE_UNDEFINED);
        $this->assertEquals($set->getPoints(), ilStudyProgrammeSettings::DEFAULT_POINTS);
        $this->assertEquals($set->getDeadlinePeriod(), 0);
        $this->assertNull($set->getDeadlineDate());
        $this->assertEquals($set->getValidityOfQualificationPeriod(), ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
        $this->assertNull($set->getValidityOfQualificationDate());
        $this->assertEquals($set->getRestartPeriod(), ilStudyProgrammeSettings::NO_RESTART);

        $set->setSubtypeId(123)
            ->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)
            ->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)
            ->setPoints(10)
            ->setDeadlinePeriod(10)
            ->setValidityOfQualificationPeriod(20)
            ->setRestartPeriod(30);
        $repo->update($set);
        ilStudyProgrammeSettingsDBRepository::clearCache();
        $set = $repo->get(-1);
        $this->assertEquals($set->getSubtypeId(), 123);
        $this->assertEquals($set->getStatus(), ilStudyProgrammeSettings::STATUS_ACTIVE);
        $this->assertEquals($set->getLPMode(), ilStudyProgrammeSettings::MODE_POINTS);
        $this->assertEquals($set->getPoints(), 10);
        $this->assertEquals($set->getDeadlinePeriod(), 10);
        $this->assertNull($set->getDeadlineDate());
        $this->assertEquals($set->getValidityOfQualificationPeriod(), 20);
        $this->assertNull($set->getValidityOfQualificationDate());
        $this->assertEquals($set->getRestartPeriod(), 30);

        $set->setSubtypeId(123)
            ->setDeadlineDate(new DateTime())
            ->setValidityOfQualificationDate(DateTime::createFromFormat('Ymd', '20200101'))
            ->setRestartPeriod(ilStudyProgrammeSettings::NO_RESTART);
        $repo->update($set);
        ilStudyProgrammeSettingsDBRepository::clearCache();
        $set = $repo->get(-1);
        $this->assertEquals($set->getDeadlinePeriod(), 0);
        $this->assertEquals($set->getDeadlineDate()->format('Ymd'), (new DateTime())->format('Ymd'));
        $this->assertEquals($set->getValidityOfQualificationDate()->format('Ymd'), '20200101');
        $this->assertEquals($set->getValidityOfQualificationPeriod(), ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
        $this->assertEquals($set->getRestartPeriod(), ilStudyProgrammeSettings::NO_RESTART);

        $repo = new ilStudyProgrammeSettingsDBRepository(
            $this->db,
            $this->tps
        );
        ilStudyProgrammeSettingsDBRepository::clearCache();
        $set = $repo->get(-1);
        $this->assertEquals($set->getSubtypeId(), 123);
        $this->assertEquals($set->getStatus(), ilStudyProgrammeSettings::STATUS_ACTIVE);
        $this->assertEquals($set->getLPMode(), ilStudyProgrammeSettings::MODE_POINTS);
        $this->assertEquals($set->getPoints(), 10);
    }

    /**
     * @depends test_edit_and_update
     */
    public function testPRGRepoDelete()
    {
        $this->expectException(\LogicException::class);
        $repo = new ilStudyProgrammeSettingsDBRepository(
            $this->db,
            $this->tps
        );
        $set = $repo->get(-1);
        $this->assertEquals($set->getSubtypeId(), 123);
        $this->assertEquals($set->getStatus(), ilStudyProgrammeSettings::STATUS_ACTIVE);
        $this->assertEquals($set->getLPMode(), ilStudyProgrammeSettings::MODE_POINTS);
        $this->assertEquals($set->getPoints(), 10);
        $repo->delete($set);
        $repo->get(-1);
    }
}
