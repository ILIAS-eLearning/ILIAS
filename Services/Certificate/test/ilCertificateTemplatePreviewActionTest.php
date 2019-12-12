<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplatePreviewActionTest extends PHPUnit_Framework_TestCase
{
    public function testA()
    {
        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderValuesObject = $this->getMockBuilder('ilCertificatePlaceholderValues')
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderValuesObject->method('getPlaceholderValuesForPreview')
            ->willReturn(array(
                'USER_LOGIN'         => 'SomeLogin',
                'USER_FULLNAME'      => 'SomeFullName',
                'USER_FIRSTNAME'     => 'SomeFirstName'
            ));

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $user->method('getId')
            ->willReturn(100);

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper
            ->expects($this->once())
            ->method('deliverData');

        $mathJaxHelper = $this->getMockBuilder('ilCertificateMathJaxHelper')
            ->getMock();

        $mathJaxHelper->method('fillXlsFoContent')
            ->willReturn('<xml> Some filled XML content </xml>');

        $userDefinedFieldsHelper = $this->getMockBuilder('ilCertificateUserDefinedFieldsHelper')
            ->getMock();

        $definitionsMock = $this->getMockBuilder('ilUserDefinedFields')
            ->disableOriginalConstructor()
            ->getMock();

        $definitionsMock->method('getDefinitions')
            ->willReturn(
                array(
                    'f_1' => array(
                        'certificate' => true,
                        'field_id' => 100,
                        'field_name' => 'Some Field Name',
                    )
                )
            );

        $userDefinedFieldsHelper->method('createInstance')
            ->willReturn($definitionsMock);

        $rpcClientFactoryHelper = $this->getMockBuilder('ilCertificateRpcClientFactoryHelper')
            ->getMock();

        $mock = $this->getMockBuilder('StdClass')
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
            'some/where/'
        );

        $previewAction->createPreviewPdf(100);
    }
}
