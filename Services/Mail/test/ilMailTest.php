<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Factory;

/**
 * Class ilMailMimeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTest extends ilMailBaseTest
{
    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        parent::setUp();
    }

    /**
     * @throws ReflectionException
     */
    public function testExternalMailDeliveryToLocalRecipientsWorksAsExpected() : void
    {
        $refineryMock = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refineryMock);

        $senderUsrId = 666;
        $loginToIdMap = [
            'phpunit1' => 1,
            'phpunit2' => 2,
            'phpunit3' => 3,
            'phpunit4' => 4,
            'phpunit5' => 5,
            'phpunit6' => 6,
            'phpunit7' => 7,
        ];
        $userInstanceById = [];
        $mailOptionsById = [];
        foreach ($loginToIdMap as $login => $usrId) {
            $user = $this
                ->getMockBuilder(ilObjUser::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getId', 'hasToAcceptTermsOfService', 'checkTimeLimit', 'getActive'])
                ->getMock();
            $user->method('getId')->willReturn($usrId);
            $user->method('getActive')->willReturn(true);
            $user->method('hasToAcceptTermsOfService')->willReturn(false);
            $user->method('checkTimeLimit')->willReturn(true);
            $userInstanceById[$usrId] = $user;

            $mailOptions = $this
                ->getMockBuilder(ilMailOptions::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getExternalEmailAddresses', 'getIncomingType'])
                ->getMock();
            $mailOptions->expects($this->any())->method('getExternalEmailAddresses')->willReturn([
                'phpunit' . $usrId . '@ilias.de',
            ]);
            $mailOptions->expects($this->any())->method('getIncomingType')->willReturn(ilMailOptions::INCOMING_EMAIL);
            $mailOptionsById[$usrId] = $mailOptions;
        }

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        ilMailMimeSenderUserById::addUserToCache($senderUsrId, $user);

        $addressTypeFactory = $this
            ->getMockBuilder(ilMailAddressTypeFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByPrefix'])
            ->getMock();
        $addressTypeFactory->expects($this->any())
            ->method('getByPrefix')
            ->willReturnCallback(function ($arg) use ($loginToIdMap) {
                return new class($arg, $loginToIdMap) implements ilMailAddressType {
                    protected $loginToIdMap = [];
                    protected $address;

                    public function __construct(ilMailAddress $address, $loginToIdMap)
                    {
                        $this->address = $address;
                        $this->loginToIdMap = array_map(static function (int $usrId) : array {
                            return [$usrId];
                        }, $loginToIdMap);
                    }

                    public function resolve() : array
                    {
                        return $this->loginToIdMap[$this->address->getMailbox()] ?? [];
                    }

                    public function validate(int $senderId) : bool
                    {
                        return true;
                    }

                    public function getErrors() : array
                    {
                        return [];
                    }

                    public function getAddress() : ilMailAddress
                    {
                        return $this->address;
                    }
                };
            });

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $nextId = 0;
        $db->expects($this->any())->method('nextId')->willReturnCallback(function () use (&$nextId) : int {
            ++$nextId;

            return $nextId;
        });

        $eventHandler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mailFileData = $this->getMockBuilder(ilFileDataMail::class)->disableOriginalConstructor()->getMock();
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock();
        $mailBox = $this->getMockBuilder(ilMailbox::class)->disableOriginalConstructor()->getMock();
        $actor = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        $mailService = new ilMail(
            $senderUsrId,
            $addressTypeFactory,
            new ilMailRfc822AddressParserFactory(),
            $eventHandler,
            $logger,
            $db,
            $lng,
            $mailFileData,
            $mailOptions,
            $mailBox,
            new ilMailMimeSenderFactory($settings),
            static function (string $login) use ($loginToIdMap) : int {
                return $loginToIdMap[$login] ?? 0;
            },
            4711,
            $actor
        );

        $oldTransport = ilMimeMail::getDefaultTransport();

        $mailTransport = $this
            ->getMockBuilder(ilMailMimeTransport::class)
            ->getMock();
        $mailTransport->expects($this->once())->method('send')->with($this->callback(function (
            ilMimeMail $mailer
        ) use ($loginToIdMap) : bool {
            $totalBcc = [];
            foreach ($mailer->getBcc() as $bcc) {
                $totalBcc = array_filter(array_map('trim', explode(',', $bcc))) + $totalBcc;
            }

            return count($totalBcc) === count($loginToIdMap);
        }))->willReturn(true);
        ilMimeMail::setDefaultTransport($mailTransport);

        $mailService->setUserInstanceById($userInstanceById);
        $mailService->setMailOptionsByUserIdMap($mailOptionsById);

        $mailService->sendMail(
            implode(',', array_slice(array_keys($loginToIdMap), 0, 3)),
            implode(',', array_slice(array_keys($loginToIdMap), 3, 2)),
            implode(',', array_slice(array_keys($loginToIdMap), 5, 2)),
            'Subject',
            'Message',
            [],
            false
        );

        ilMimeMail::setDefaultTransport($oldTransport);
    }
}
