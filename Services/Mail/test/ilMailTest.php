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

use ILIAS\Refinery\Factory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilMailMimeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTest extends ilMailBaseTest
{
    /** @var MockObject&ilDBInterface */
    private ilDBInterface $mockDatabase;
    /** @var MockObject&ilMailAddressTypeFactory */
    private $mockAddressTypeFactory;
    /** @var MockObject&ilLogger */
    private $mockLog;
    /** @var MockObject&ilMailRfc822AddressParserFactory */
    private $mockParserFactory;
    /** @var MockObject&ilLanguage */
    private $mockLanguage;

    /**
     * @throws ReflectionException
     */
    public function testExternalMailDeliveryToLocalRecipientsWorksAsExpected(): void
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
            $mailOptions->method('getExternalEmailAddresses')->willReturn([
                'phpunit' . $usrId . '@ilias.de',
            ]);
            $mailOptions->method('getIncomingType')->willReturn(ilMailOptions::INCOMING_EMAIL);
            $mailOptionsById[$usrId] = $mailOptions;
        }

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        ilMailMimeSenderUserById::addUserToCache($senderUsrId, $user);

        $addressTypeFactory = $this
            ->getMockBuilder(ilMailAddressTypeFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByPrefix'])
            ->getMock();
        $addressTypeFactory
            ->method('getByPrefix')
            ->willReturnCallback(function ($arg) use ($loginToIdMap) {
                return new class ($arg, $loginToIdMap) implements ilMailAddressType {
                    protected array $loginToIdMap = [];
                    protected ilMailAddress $address;

                    public function __construct(ilMailAddress $address, $loginToIdMap)
                    {
                        $this->address = $address;
                        $this->loginToIdMap = array_map(static function (int $usrId): array {
                            return [$usrId];
                        }, $loginToIdMap);
                    }

                    public function resolve(): array
                    {
                        return $this->loginToIdMap[$this->address->getMailbox()] ?? [];
                    }

                    public function validate(int $senderId): bool
                    {
                        return true;
                    }

                    public function getErrors(): array
                    {
                        return [];
                    }

                    public function getAddress(): ilMailAddress
                    {
                        return $this->address;
                    }
                };
            });

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $nextId = 0;
        $db->method('nextId')->willReturnCallback(function () use (&$nextId): int {
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
            static function (string $login) use ($loginToIdMap): int {
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
        ) use ($loginToIdMap): bool {
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

    public function testGetMailObjectReferenceId(): void
    {
        $refId = 364;
        $instance = $this->create($refId);

        $this->assertSame($refId, $instance->getMailObjectReferenceId());
    }

    public function testFormatNamesForOutput(): void
    {
        $instance = $this->create();

        $this->mockLanguage->expects(self::once())->method('txt')->with('not_available')->willReturn('not_available');

        $this->assertSame('not_available', $instance->formatNamesForOutput(''));
        $this->assertSame('', $instance->formatNamesForOutput(','));
    }

    /**
     * @dataProvider provideGetPreviousMail
     */
    public function testGetPreviousMail(array $rowData): void
    {
        $mailId = 3454;
        $instance = $this->createAndExpectDatabaseCall($mailId, $rowData);
        $this->mockDatabase->expects(self::once())->method('setLimit')->with(1, 0);
        $instance->getPreviousMail($mailId);
    }

    public function provideGetPreviousMail(): array
    {
        return [
            [[]],
            [[
                'attachments' => '',
                'folder_id' => '',
                'mail_id' => '',
                'sender_id' => '',
                'tpl_ctx_params' => '[]',
                'use_placeholders' => '',
                'user_id' => ''
            ]],
            [[
                'folder_id' => '',
                'mail_id' => '',
                'sender_id' => '',
                'use_placeholders' => '',
                'user_id' => ''
            ]],
        ];
    }

    public function testGetNextMail(): void
    {
        $mailId = 8484;
        $instance = $this->createAndExpectDatabaseCall($mailId, []);
        $this->mockDatabase->expects(self::once())->method('setLimit')->with(1, 0);
        $instance->getNextMail($mailId);
    }

    public function testGetMailsOfFolder(): void
    {
        $filter = ['status' => 'yes'];
        $rowData = ['mail_id' => 8908];
        $one = $rowData + ['attachments' => [], 'tpl_ctx_params' => []];
        $expected = [$one, $one];
        $folderId = 89;
        $userId = 901;
        $instance = $this->create(234, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mockDatabase->expects(self::never())->method('setLimit');
        $this->mockDatabase->expects(self::exactly(3))->method('fetchAssoc')->with($mockStatement)->willReturnOnConsecutiveCalls($rowData, $rowData, null);
        $this->mockDatabase->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer', 'integer'], [$userId, $folderId]));

        $this->mockDatabase->expects(self::once())->method('quote')->with($filter['status'], 'text')->willReturn($filter['status']);

        $this->assertEquals($expected, $instance->getMailsOfFolder($folderId, $filter));
    }

    public function testCountMailsOfFolder(): void
    {
        $userId = 46;
        $folderId = 68;
        $numRows = 89;
        $instance = $this->create(345, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mockDatabase->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer', 'integer'], [$userId, $folderId]));
        $this->mockDatabase->expects(self::once())->method('numRows')->with($mockStatement)->willReturn($numRows);

        $this->assertSame($numRows, $instance->countMailsOfFolder($folderId));
    }

    public function testGetMail(): void
    {
        $mailId = 7890;
        $instance = $this->createAndExpectDatabaseCall($mailId, []);
        $instance->getMail($mailId);
    }

    public function testMarkRead(): void
    {
        $mailIds = [1, 2, 3, 4, 5, 6];
        $userId = 987;
        $instance = $this->create(567, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mockDatabase->expects(self::once())->method('in')->with('mail_id', $mailIds, false, 'integer')->willReturn('');
        $this->mockDatabase->expects(self::once())->method('manipulateF')->willReturnCallback($this->queryCallback(0, ['text', 'integer'], ['read', $userId]));

        $instance->markRead($mailIds);
    }

    public function testMarkUnread(): void
    {
        $mailIds = [1, 2, 3, 4, 5, 6];
        $userId = 987;
        $instance = $this->create(567, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mockDatabase->expects(self::once())->method('in')->with('mail_id', $mailIds, false, 'integer')->willReturn('');
        $this->mockDatabase->expects(self::once())->method('manipulateF')->willReturnCallback($this->queryCallback(0, ['text', 'integer'], ['unread', $userId]));

        $instance->markUnread($mailIds);
    }

    public function testMoveMailsToFolder(): void
    {
        $mailIds = [1, 2, 3, 4, 5, 6];
        $folderId = 890;
        $userId = 987;
        $instance = $this->create(567, $userId);
        $this->mockDatabase->expects(self::once())->method('in')->with('mail_id', $mailIds, false, 'integer')->willReturn('');
        $this->mockDatabase->expects(self::once())->method('manipulateF')->willReturnCallback($this->queryCallback(1, ['integer', 'integer', 'integer'], [$folderId, $userId, $userId]));

        $this->assertTrue($instance->moveMailsToFolder($mailIds, $folderId));
    }

    public function testMoveMailsToFolderFalse(): void
    {
        $mailIds = [];
        $instance = $this->create();
        $this->mockDatabase->expects(self::never())->method('in');
        $this->mockDatabase->expects(self::never())->method('manipulateF');

        $this->assertFalse($instance->moveMailsToFolder($mailIds, 892));
    }

    public function testGetNewDraftId(): void
    {
        $nextId = 789;
        $userId = 5678;
        $folderId = 47;
        $instance = $this->create(4749, $userId);

        $this->mockDatabase->expects(self::once())->method('nextId')->with('mail')->willReturn($nextId);
        $this->mockDatabase->expects(self::once())->method('insert')->with('mail', [
            'mail_id' => ['integer', $nextId],
            'user_id' => ['integer', $userId],
            'folder_id' => ['integer', $folderId],
            'sender_id' => ['integer', $userId],
        ]);

        $this->assertSame($nextId, $instance->getNewDraftId($folderId));
    }

    public function testUpdateDraft(): void
    {
        $folderId = 7890;
        $instance = $this->create();
        $to = 'abc';
        $cc = 'bcde';
        $bcc = 'jkl';
        $subject = 'jlh';
        $message = 'some message';
        $usePlaceholders = true;
        $contextId = '87';
        $params = [];
        $draftId = 78;

        $this->mockDatabase->expects(self::once())->method('update')->with('mail', [
            'folder_id' => ['integer', $folderId],
            'attachments' => ['clob', serialize([])],
            'send_time' => ['timestamp', date('Y-m-d H:i:s')],
            'rcp_to' => ['clob', $to],
            'rcp_cc' => ['clob', $cc],
            'rcp_bcc' => ['clob', $bcc],
            'm_status' => ['text', 'read'],
            'm_subject' => ['text', $subject],
            'm_message' => ['clob', $message],
            'use_placeholders' => ['integer', (int) $usePlaceholders],
            'tpl_ctx_id' => ['text', $contextId],
            'tpl_ctx_params' => ['blob', json_encode($params, JSON_THROW_ON_ERROR)],
        ], [
            'mail_id' => ['integer', $draftId],
        ]);

        $this->assertSame($draftId, $instance->updateDraft($folderId, [], $to, $cc, $bcc, $subject, $message, $draftId, $usePlaceholders, $contextId, $params));
    }

    public function testSavePostData(): void
    {
        $userId = 897;
        $attachments = [];
        $rcpTo = 'jlh';
        $rcpCc = 'jhkjh';
        $rcpBcc = 'ououi';
        $subject = 'hbansn';
        $message = 'message';
        $usePlaceholders = false;
        $contextId = '9080';
        $params = [];

        $instance = $this->create(789, $userId);

        $this->mockDatabase->expects(self::once())->method('replace')->with('mail_saved', [
            'user_id' => ['integer', $userId],
        ], [
            'attachments' => ['clob', serialize($attachments)],
            'rcp_to' => ['clob', $rcpTo],
            'rcp_cc' => ['clob', $rcpCc],
            'rcp_bcc' => ['clob', $rcpBcc],
            'm_subject' => ['text', $subject],
            'm_message' => ['clob', $message],
            'use_placeholders' => ['integer', (int) $usePlaceholders],
            'tpl_ctx_id' => ['text', $contextId],
            'tpl_ctx_params' => ['blob', json_encode($params, JSON_THROW_ON_ERROR)],
        ]);

        $instance->savePostData(
            78979078,
            $attachments,
            $rcpTo,
            $rcpCc,
            $rcpBcc,
            $subject,
            $message,
            $usePlaceholders,
            $contextId,
            $params,
        );
    }

    public function testGetSavedData(): void
    {
        $userId = 789;
        $instance = $this->create(67, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn([]);
        $this->mockDatabase->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer'], [$userId]));

        $this->assertNull($instance->getSavedData());
    }

    public function testValidateRecipients($errors = []): void
    {
        $to = 'jkhk';
        $cc = 'hjhjkl';
        $bcc = 'jklhjk';

        $instance = $this->create();
        $this->mockLog->expects(self::exactly(6))->method('debug')->withConsecutive(
            ['Started parsing of recipient string: ' . $to],
            ['Parsed addresses: hello'],
            ['Started parsing of recipient string: ' . $cc],
            ['Parsed addresses: hello'],
            ['Started parsing of recipient string: ' . $bcc],
            ['Parsed addresses: hello']
        );

        $mockAddress = $this->getMockBuilder(ilMailAddress::class)->disableOriginalConstructor()->getMock();
        $mockAddress->expects(self::exactly(3))->method('__toString')->willReturn('hello');
        $mockParser = $this->getMockBuilder(ilMailRecipientParser::class)->disableOriginalConstructor()->getMock();
        $mockParser->expects(self::exactly(3))->method('parse')->willReturn([$mockAddress]);
        $this->mockParserFactory->expects(self::exactly(3))->method('getParser')->withConsecutive([$to], [$cc], [$bcc])->willReturn($mockParser);

        $mockAddressType = $this->getMockBuilder(ilMailAddressType::class)->disableOriginalConstructor()->getMock();
        $mockAddressType->expects(self::exactly(3))->method('validate')->willReturn(empty($errors));
        $mockAddressType->expects(self::exactly(empty($errors) ? 0 : 3))->method('getErrors')->willReturn($errors);
        $this->mockAddressTypeFactory->expects(self::exactly(3))->method('getByPrefix')->with($mockAddress)->willReturn($mockAddressType);

        $this->assertSame([], $instance->validateRecipients($to, $cc, $bcc));
    }

    public function provideValidateRecipients(): array
    {
        return [
            [[]],
            [['some error']]
        ];
    }

    public function testGetIliasMailerName(): void
    {
        $expected = 'Phasellus lacus';
        $mockSystem = $this->getMockBuilder(ilMailMimeSenderSystem::class)->disableOriginalConstructor()->getMock();
        $mockSystem->expects(self::once())->method('getFromName')->willReturn($expected);
        $mockFactory = $this->getMockBuilder(ilMailMimeSenderFactory::class)->disableOriginalConstructor()->getMock();
        $mockFactory->expects(self::once())->method('system')->willReturn($mockSystem);
        $this->setGlobalVariable('mail.mime.sender.factory', $mockFactory);

        $this->assertSame($expected, ilMail::_getIliasMailerName());
    }

    public function testSaveAttachments(): void
    {
        $userId = 89;
        $attachments = ['aaa', 'bb', 'cc', 'rrr'];
        $instance = $this->create(789, $userId);

        $this->mockDatabase->expects(self::once())->method('update')->with(
            'mail_saved',
            [
                'attachments' => ['clob', serialize($attachments)],
            ],
            [
                'user_id' => ['integer', $userId],
            ]
        );

        $instance->saveAttachments($attachments);
    }

    private function queryCallback($returnValue, array $expectedTypes, array $expectedValues): Closure
    {
        return function (string $query, array $types, array $values) use ($expectedTypes, $expectedValues, $returnValue) {
            $this->assertEquals($expectedTypes, $types);
            $this->assertEquals($expectedValues, $values);

            return $returnValue;
        };
    }

    private function createAndExpectDatabaseCall(int $someMailId, array $rowData): ilMail
    {
        $userId = 900;
        $instance = $this->create(234, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn($rowData);
        $this->mockDatabase->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer', 'integer'], [$userId, $someMailId]));

        return $instance;
    }

    private function create(int $refId = 234, int $userId = 123): ilMail
    {
        $instance = new ilMail(
            $userId,
            ($this->mockAddressTypeFactory = $this->getMockBuilder(ilMailAddressTypeFactory::class)->disableOriginalConstructor()->getMock()),
            ($this->mockParserFactory = $this->getMockBuilder(ilMailRfc822AddressParserFactory::class)->disableOriginalConstructor()->getMock()),
            $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->getMock(),
            ($this->mockLog = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock()),
            ($this->mockDatabase = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock()),
            ($this->mockLanguage = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock()),
            $this->getMockBuilder(ilFileDataMail::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailbox::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailMimeSenderFactory::class)->disableOriginalConstructor()->getMock(),
            static function (string $login): int {
                return 780;
            },
            $refId,
            $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock()
        );

        return $instance;
    }
}
