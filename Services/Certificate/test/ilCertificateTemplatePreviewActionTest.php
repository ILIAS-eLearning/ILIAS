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
class ilCertificateTemplatePreviewActionTest extends ilCertificateBaseTestCase
{
    public function testA() : void
    {
        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $placeholderValuesObject = $this->getMockBuilder(ilCertificatePlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderValuesObject->method('getPlaceholderValuesForPreview')
            ->willReturn([
                'USER_LOGIN' => 'SomeLogin',
                'USER_FULLNAME' => 'SomeFullName',
                'USER_FIRSTNAME' => 'SomeFirstName'
            ]);

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfFileNameFactory = $this->getMockBuilder(ilCertificatePdfFileNameFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdfFileNameFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn('test');

        $user->method('getId')
            ->willReturn(100);

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper
            ->expects($this->once())
            ->method('deliverData');

        $mathJaxHelper = $this->getMockBuilder(ilCertificateMathJaxHelper::class)
            ->getMock();

        $mathJaxHelper->method('fillXlsFoContent')
            ->willReturn('<xml> Some filled XML content </xml>');

        $userDefinedFieldsHelper = $this->getMockBuilder(ilCertificateUserDefinedFieldsHelper::class)
            ->getMock();

        $definitionsMock = $this->getMockBuilder(ilUserDefinedFields::class)
            ->disableOriginalConstructor()
            ->getMock();

        $definitionsMock->method('getDefinitions')
            ->willReturn(
                [
                    'f_1' => [
                        'certificate' => true,
                        'field_id' => 100,
                        'field_name' => 'Some Field Name',
                    ]
                ]
            );

        $userDefinedFieldsHelper->method('createInstance')
            ->willReturn($definitionsMock);

        $rpcClientFactoryHelper = $this->getMockBuilder(ilCertificateRpcClientFactoryHelper::class)
            ->getMock();

        $mock = $this->getMockBuilder(stdClass::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->scalar = '<xml> Some XML content </xml>';

        $rpcClientFactoryHelper->method('ilFO2PDF')
            ->willReturn($mock);



        $previewAction = new ilCertificateTemplatePreviewAction(
            $templateRepository,
            $placeholderValuesObject,
            $logger,
            $user,
            $utilHelper,
            $mathJaxHelper,
            $userDefinedFieldsHelper,
            $rpcClientFactoryHelper,
            'some/where/',
            $pdfFileNameFactory
        );

        $previewAction->createPreviewPdf(100);
    }
}
