<?php

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

declare(strict_types=1);

use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Language\Language;
use ILIAS\User\Profile\Fields\Custom\Custom;
use PHPUnit\Framework\MockObject\Stub;

class ilCertificateTemplatePreviewActionTest extends ilCertificateBaseTestCase
{
    public function testA(): void
    {
        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilCertificatePlaceholderValues&Stub $placeholderValuesObject */
        $placeholderValuesObject = $this->createStub(ilCertificatePlaceholderValues::class);

        $placeholderValuesObject->method('getPlaceholderValuesForPreview')
            ->willReturn([
                'CERTIFICATE_ID' => 'randomUniqueString',
                'USER_LOGIN' => 'SomeLogin',
                'USER_FULLNAME' => 'SomeFullName',
                'USER_FIRSTNAME' => 'SomeFirstName'
            ]);

        /** @var IRSS&Stub $irss */
        $irss = $this->createStub(IRSS::class);

        /** @var ilObjUser&Stub $user */
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')
            ->willReturn(100);

        $pdfFileNameFactory = $this->getMockBuilder(ilCertificatePdfFileNameFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdfFileNameFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn('test');

        /** @var Language&Stub $lng */
        $lng = $this->createStub(Language::class);

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->expects($this->once())
            ->method('deliverData');

        /** @var ilCertificateUserDefinedFieldsHelper&Stub $userDefinedFieldsHelper */
        $userDefinedFieldsHelper = $this->createStub(ilCertificateUserDefinedFieldsHelper::class);

        /** @var Custom&Stub $field */
        $field = $this->createStub(Custom::class);
        $field->method('getidentifier')
            ->willReturn('70ddbac5-c162-474d-9365-e374975fd021');
        $field->method('getLabel')
            ->willReturn('Some Field Name');

        $userDefinedFieldsHelper->method('getFields')
            ->willReturn(
                [
                    $field
                ]
            );

        /** @var ilCertificateRpcClientFactoryHelper&Stub $rpcClientFactoryHelper */
        $rpcClientFactoryHelper = $this->createStub(ilCertificateRpcClientFactoryHelper::class);

        /** @var stdClass&Stub $mock */
        $mock = $this->createStub(stdClass::class);

        $mock->scalar = '<xml> Some XML content </xml>';

        $rpcClientFactoryHelper->method('ilFO2PDF')
            ->willReturn($mock);



        $previewAction = new ilCertificateTemplatePreviewAction(
            $templateRepository,
            $placeholderValuesObject,
            $irss,
            'some/where/',
            $user,
            $lng,
            $utilHelper,
            $userDefinedFieldsHelper,
            $rpcClientFactoryHelper,
            $pdfFileNameFactory
        );

        $previewAction->createPreviewPdf(100);
    }
}
