<?php declare(strict_types=1);

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
