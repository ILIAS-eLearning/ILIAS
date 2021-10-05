<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDownloadValidatorTest extends ilCertificateBaseTestCase
{
    public function testValidationSucceedsAndReturnsTrue() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();


        $userCertificateRepository->method('fetchActiveCertificate');

        $accessValidator = new ilCertificateUserCertificateAccessValidator($userCertificateRepository);

        $activeValidator = $this->getMockBuilder(ilCertificateActiveValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator->method('validate')
            ->willReturn(true);

        $validator = new ilCertificateDownloadValidator($accessValidator, $activeValidator);

        $result = $validator->isCertificateDownloadable(100, 100);

        $this->assertTrue($result);
    }

    public function testValidationReturnedFalseBecauseCertificateAreNotGloballyActivated() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository
            ->expects($this->never())
            ->method('fetchActiveCertificate');

        $accessValidator = new ilCertificateUserCertificateAccessValidator($userCertificateRepository);

        $activeValidator = $this->getMockBuilder(ilCertificateActiveValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator
            ->method('validate')
            ->willReturn(false);

        $validator = new ilCertificateDownloadValidator($accessValidator, $activeValidator);

        $result = $validator->isCertificateDownloadable(100, 100);

        $this->assertFalse($result);
    }

    public function testValidationReturnedFalseBecauseJavaServerIsNotActive() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository
            ->expects($this->once())
            ->method('fetchActiveCertificate')
            ->willThrowException(new ilRpcClientException('Client not active'));

        $accessValidator = new ilCertificateUserCertificateAccessValidator($userCertificateRepository);

        $activeValidator = $this->getMockBuilder(ilCertificateActiveValidator::class)
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
