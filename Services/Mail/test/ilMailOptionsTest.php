<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailOptionsTest
 * @author Niels Theen <ntheen@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOptionsTest extends ilMailBaseTest
{
    /**
     * @throws ReflectionException
     */
    public function testConstructor()
    {
        $userId = 1;

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();
        $queryMock = $this->getMockBuilder(ilDBStatement::class)
            ->getMock();

        $object = $this->getMockBuilder(stdClass::class)->getMock();
        $object->cronjob_notification = false;
        $object->signature = 'smth';
        $object->linebreak = false;
        $object->incoming_type = 0;
        $object->mail_address_option = 0;
        $object->email = 'test@test.com';
        $object->second_email = 'ilias@ilias.com';


        $database->expects($this->once())->method('fetchObject')->willReturn($object);
        $database->expects($this->once())->method('queryF')->willReturn($queryMock);
        $database->method('replace')->willReturn(0);

        $this->setGlobalVariable('ilDB', $database);

        $settings = $this->getMockBuilder(\ilSetting::class)->disableOriginalConstructor()->setMethods(array(
            'set',
            'get'
        ))->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mailOptions = new ilMailOptions($userId);
        $this->assertEquals($object->signature, $mailOptions->getSignature());
        $this->assertEquals($object->incoming_type, $mailOptions->getIncomingType());
        $this->assertEquals($object->linebreak, $mailOptions->getLinebreak());
        $this->assertEquals($object->cronjob_notification, $mailOptions->getCronjobNotification());
    }
}
