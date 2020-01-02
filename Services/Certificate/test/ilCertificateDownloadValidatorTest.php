<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDownloadValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidationSucceedsAndReturnsTrue()
    {
        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();


        $userCertificateRepository->method('fetchActiveCertificate');

        $accessValidator = new ilCertificateUserCertificateAccessValidator($userCertificateRepository);

        $activeValidator = $this->getMockBuilder('ilCertificateActiveValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator->method('validate')
            ->willReturn(true);

        $validator = new ilCertificateDownloadValidator($accessValidator, $activeValidator);

        $result = $validator->isCertificateDownloadable(100, 100);

        $this->assertTrue($result);
    }

    public function testValidationReturnedFalseBecauseCertificateAreNotGloballyActivated()
    {
        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository
            ->expects($this->never())
            ->method('fetchActiveCertificate');

        $accessValidator = new ilCertificateUserCertificateAccessValidator($userCertificateRepository);

        $activeValidator = $this->getMockBuilder('ilCertificateActiveValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator
            ->method('validate')
            ->willReturn(false);

        $validator = new ilCertificateDownloadValidator($accessValidator, $activeValidator);

        $result = $validator->isCertificateDownloadable(100, 100);

        $this->assertFalse($result);
    }

    public function testValidationReturnedFalseBecauseJavaServerIsNotActive()
    {
        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository
            ->expects($this->once())
            ->method('fetchActiveCertificate')
            ->willThrowException(new ilRpcClientException('Client not active'));

        $accessValidator = new ilCertificateUserCertificateAccessValidator($userCertificateRepository);

        $activeValidator = $this->getMockBuilder('ilCertificateActiveValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator
            ->method('validate')
            ->willReturn(true);

        $validator = new ilCertificateDownloadValidator($accessValidator, $activeValidator);

        $result = $validator->isCertificateDownloadable(100, 100);

        $this->assertFalse($result);
    }
}
