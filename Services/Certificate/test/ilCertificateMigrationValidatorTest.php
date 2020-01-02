<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testCertificatesAreNotGloballyAvailableWillResultInFalse()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn(0);

        $validator = new ilCertificateMigrationValidator($settings);

        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationHelper = $this->getMockBuilder('ilCertificateMigration')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $validator->isMigrationAvailable($user, $migrationHelper);

        $this->assertFalse($result);
    }

    public function testCertificatesAlreadyMigratedWillResultInFalse()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn(1);

        $validator = new ilCertificateMigrationValidator($settings);

        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $user->method('getPref')
            ->with('cert_migr_finished')
            ->willReturn('1');

        $migrationHelper = $this->getMockBuilder('ilCertificateMigration')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $validator->isMigrationAvailable($user, $migrationHelper);

        $this->assertFalse($result);
    }

    public function testMigrationTaskIsAlreadyRunningWillResultInFalse()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn(1);

        $validator = new ilCertificateMigrationValidator($settings);

        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $user->method('getPref')
            ->with('cert_migr_finished')
            ->willReturn('0');

        $migrationHelper = $this->getMockBuilder('ilCertificateMigration')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationHelper->method('isTaskRunning')
            ->willReturn(true);

        $result = $validator->isMigrationAvailable($user, $migrationHelper);

        $this->assertFalse($result);
    }

    public function testMigrationTaskIsAlreadyFinishedResultInFalse()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn(1);

        $validator = new ilCertificateMigrationValidator($settings);

        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $user->method('getPref')
            ->with('cert_migr_finished')
            ->willReturn('0');

        $migrationHelper = $this->getMockBuilder('ilCertificateMigration')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationHelper->method('isTaskRunning')
            ->willReturn(false);

        $migrationHelper->method('isTaskFinished')
            ->willReturn(true);

        $result = $validator->isMigrationAvailable($user, $migrationHelper);

        $this->assertFalse($result);
    }

    public function testMigrationIsAvailable()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->withConsecutive(array('active'), array('persisting_cers_introduced_ts'))
            ->willReturnOnConsecutiveCalls(1, 970000000);

        $validator = new ilCertificateMigrationValidator($settings);

        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $user->method('getPref')
            ->with('cert_migr_finished')
            ->willReturn('0');

        $user->method('getCreateDate')
            ->willReturn('10 September 2000');

        $migrationHelper = $this->getMockBuilder('ilCertificateMigration')
            ->disableOriginalConstructor()
            ->getMock();

        $migrationHelper->method('isTaskRunning')
            ->willReturn(false);

        $migrationHelper->method('isTaskFinished')
            ->willReturn(false);

        $result = $validator->isMigrationAvailable($user, $migrationHelper);

        $this->assertTrue($result);
    }
}
