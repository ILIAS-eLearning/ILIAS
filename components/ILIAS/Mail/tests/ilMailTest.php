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

use ILIAS\Refinery\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Mail\Autoresponder\AutoresponderService;
use ILIAS\LegalDocuments\Conductor;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\Result\Ok;
use ILIAS\Mail\Service\MailSignatureService;

/**
 * Class ilMailMimeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTest extends ilMailBaseTestCase
{
    private MockObject&ilDBInterface $mock_database;
    private MockObject&ilMailAddressTypeFactory $mock_address_type_factory;
    private MockObject&ilLogger $mock_log;
    private MockObject&ilMailRfc822AddressParserFactory $mock_parser_factory;
    private MockObject&ilLanguage $mock_language;

    /**
     * @throws ReflectionException
     */
    public function testExternalMailDeliveryToLocalRecipientsWorksAsExpected(): void
    {
        $refineryMock = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refineryMock);

        $legal_documents = $this->createMock(Conductor::class);
        $this->setGlobalVariable('legalDocuments', $legal_documents);

        $this->setGlobalVariable('ilIliasIniFile', $this->createMock(ilIniFile::class));
        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilClientIniFile', $this->createMock(ilIniFile::class));
        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));
        $this->setGlobalVariable('ilCtrl', $this->createMock(ilCtrl::class));

        $webDir = 'public/data';
        define("ILIAS_WEB_DIR", $webDir);

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

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects(self::exactly(count($loginToIdMap)))->method('applyTo')->willReturn(new Ok(null));
        $legal_documents->expects(self::exactly(count($loginToIdMap)))->method('userCanReadInternalMail')->willReturn($transformation);

        $userInstanceById = [];
        $mailOptionsById = [];
        foreach ($loginToIdMap as $usrId) {
            $user = $this
                ->getMockBuilder(ilObjUser::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getId', 'checkTimeLimit', 'getActive'])
                ->getMock();
            $user->method('getId')->willReturn($usrId);
            $user->method('getActive')->willReturn(true);
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
            ->willReturnCallback(function ($arg) use ($loginToIdMap): object {
                return new class ($arg, $loginToIdMap) implements ilMailAddressType {
                    protected array $loginToIdMap = [];

                    public function __construct(protected ilMailAddress $address, $loginToIdMap)
                    {
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
        $settings->method('get')->willReturn('');
        $this->setGlobalVariable('ilSetting', $settings);

        $mailFileData = $this->getMockBuilder(ilFileDataMail::class)->disableOriginalConstructor()->getMock();
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock();
        $mailBox = $this->getMockBuilder(ilMailbox::class)->disableOriginalConstructor()->getMock();
        $actor = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $mustache_factory = $this->getMockBuilder(ilMustacheFactory::class)->getMock();

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
            new ilMailMimeSenderFactory($settings, $mustache_factory),
            static function (string $login) use ($loginToIdMap): int {
                return $loginToIdMap[$login] ?? 0;
            },
            $this->createMock(AutoresponderService::class),
            0,
            4711,
            $actor,
            new ilMailTemplatePlaceholderResolver(new Mustache_Engine())
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

        $mail_data = new MailDeliveryData(
            implode(',', array_slice(array_keys($loginToIdMap), 0, 3)),
            implode(',', array_slice(array_keys($loginToIdMap), 3, 2)),
            implode(',', array_slice(array_keys($loginToIdMap), 5, 2)),
            'Subject',
            'Message',
            [],
            false
        );
        $mailService->sendMail($mail_data);

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

        $this->mock_language->expects(self::once())->method('txt')->with('not_available')->willReturn('not_available');

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
        $this->mock_database->expects(self::once())->method('setLimit')->with(1, 0);
        $instance->getPreviousMail($mailId);
    }

    public static function provideGetPreviousMail(): array
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
        $this->mock_database->expects(self::once())->method('setLimit')->with(1, 0);
        $instance->getNextMail($mailId);
    }

    public function testGetMailsOfFolder(): void
    {
        $filter = ['status' => 'yes'];
        $rowData = ['mail_id' => 8908];
        $one = $rowData + [
            'attachments' => [],
            'tpl_ctx_params' => [],
            'm_subject' => '',
            'm_message' => '',
            'rcp_to' => '',
            'rcp_cc' => '',
            'rcp_bcc' => '',
        ];
        $expected = [$one, $one];
        $folderId = 89;
        $userId = 901;
        $instance = $this->create(234, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects(self::never())->method('setLimit');
        $this->mock_database->expects(self::exactly(3))->method('fetchAssoc')->with($mockStatement)->willReturnOnConsecutiveCalls($rowData, $rowData, null);
        $this->mock_database->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer', 'integer'], [$userId, $folderId]));

        $this->mock_database->expects(self::once())->method('quote')->with($filter['status'], 'text')->willReturn($filter['status']);

        $this->assertEquals($expected, $instance->getMailsOfFolder($folderId, $filter));
    }

    public function testCountMailsOfFolder(): void
    {
        $userId = 46;
        $folderId = 68;
        $numRows = 89;
        $instance = $this->create(345, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer', 'integer'], [$userId, $folderId]));
        $this->mock_database->expects(self::once())->method('numRows')->with($mockStatement)->willReturn($numRows);

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
        $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects(self::once())->method('in')->with('mail_id', $mailIds, false, 'integer')->willReturn('');
        $this->mock_database->expects(self::once())->method('manipulateF')->willReturnCallback($this->queryCallback(0, ['text', 'integer'], ['read', $userId]));

        $instance->markRead($mailIds);
    }

    public function testMarkUnread(): void
    {
        $mailIds = [1, 2, 3, 4, 5, 6];
        $userId = 987;
        $instance = $this->create(567, $userId);
        $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects(self::once())->method('in')->with('mail_id', $mailIds, false, 'integer')->willReturn('');
        $this->mock_database->expects(self::once())->method('manipulateF')->willReturnCallback($this->queryCallback(0, ['text', 'integer'], ['unread', $userId]));

        $instance->markUnread($mailIds);
    }

    public function testMoveMailsToFolder(): void
    {
        $mailIds = [1, 2, 3, 4, 5, 6];
        $folderId = 890;
        $userId = 987;
        $instance = $this->create(567, $userId);
        $this->mock_database->expects(self::once())->method('in')->with('mail_id', $mailIds, false, 'integer')->willReturn('');
        $this->mock_database->expects(self::once())->method('manipulateF')->willReturnCallback($this->queryCallback(1, ['integer', 'integer', 'integer'], [$folderId, $userId, $userId]));

        $this->assertTrue($instance->moveMailsToFolder($mailIds, $folderId));
    }

    public function testMoveMailsToFolderFalse(): void
    {
        $mailIds = [];
        $instance = $this->create();
        $this->mock_database->expects(self::never())->method('in');
        $this->mock_database->expects(self::never())->method('manipulateF');

        $this->assertFalse($instance->moveMailsToFolder($mailIds, 892));
    }

    public function testGetNewDraftId(): void
    {
        $nextId = 789;
        $userId = 5678;
        $folderId = 47;
        $instance = $this->create(4749, $userId);

        $this->mock_database->expects(self::once())->method('nextId')->with('mail')->willReturn($nextId);
        $this->mock_database->expects(self::once())->method('insert')->with('mail', [
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

        $this->mock_database->expects(self::once())->method('update')->with('mail', [
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

    public function testPersistingToStage(): void
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

        $this->mock_database->expects(self::once())->method('replace')->with('mail_saved', [
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

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mock_database->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer'], [$userId]));
        $this->mock_database->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn([
            'rcp_to' => 'phpunit'
        ]);

        $instance->persistToStage(
            78_979_078,
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

    public function testRetrievalFromStage(): void
    {
        $userId = 789;
        $instance = $this->create(67, $userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mock_database->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer'], [$userId]));
        $this->mock_database->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn([
            'rcp_to' => 'phpunit'
        ]);

        $mail_data = $instance->retrieveFromStage();

        $this->assertIsArray($mail_data);
        $this->assertEquals('phpunit', $mail_data['rcp_to']);
    }

    public function testValidateRecipients($errors = []): void
    {
        $to = 'jkhk';
        $cc = 'hjhjkl';
        $bcc = 'jklhjk';

        $instance = $this->create();
        $consecutive_debug = [
            'Started parsing of recipient string: ' . $to,
            'Parsed addresses: hello',
            'Started parsing of recipient string: ' . $cc,
            'Parsed addresses: hello',
            'Started parsing of recipient string: ' . $bcc,
            'Parsed addresses: hello'
        ];
        $this->mock_log->expects(self::exactly(6))->method('debug')->with(
            $this->callback(function ($value) use (&$consecutive_debug) {
                $this->assertSame(array_shift($consecutive_debug), $value);
                return true;
            }),
        );

        $mockAddress = $this->getMockBuilder(ilMailAddress::class)->disableOriginalConstructor()->getMock();
        $mockAddress->expects(self::exactly(3))->method('__toString')->willReturn('hello');
        $mockParser = $this->getMockBuilder(ilMailRecipientParser::class)->disableOriginalConstructor()->getMock();
        $mockParser->expects(self::exactly(3))->method('parse')->willReturn([$mockAddress]);
        $consecutive_get = [$to, $cc, $bcc];
        $this->mock_parser_factory->expects(self::exactly(3))->method('getParser')->with(
            $this->callback(function ($value) use (&$consecutive_get) {
                $this->assertSame(array_shift($consecutive_get), $value);
                return true;
            }),
        )->willReturn($mockParser);

        $mockAddressType = $this->getMockBuilder(ilMailAddressType::class)->disableOriginalConstructor()->getMock();
        $mockAddressType->expects(self::exactly(3))->method('validate')->willReturn(empty($errors));
        $mockAddressType->expects(self::exactly(empty($errors) ? 0 : 3))->method('getErrors')->willReturn($errors);
        $this->mock_address_type_factory->expects(self::exactly(3))->method('getByPrefix')->with($mockAddress)->willReturn($mockAddressType);

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
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $settings->method('get')->with('mail_system_sys_from_name')->willReturn($expected);
        $this->setGlobalVariable('ilSetting', $settings);


        $this->assertSame($expected, ilMail::_getIliasMailerName());
    }

    public function testSaveAttachments(): void
    {
        $userId = 89;
        $attachments = ['aaa', 'bb', 'cc', 'rrr'];
        $instance = $this->create(789, $userId);

        $this->mock_database->expects(self::once())->method('update')->with(
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
        $this->mock_database->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn($rowData);
        $this->mock_database->expects(self::once())->method('queryF')->willReturnCallback($this->queryCallback($mockStatement, ['integer', 'integer'], [$userId, $someMailId]));

        return $instance;
    }

    private function create(int $refId = 234, int $userId = 123): ilMail
    {
        return new ilMail(
            $userId,
            ($this->mock_address_type_factory = $this->getMockBuilder(ilMailAddressTypeFactory::class)->disableOriginalConstructor()->getMock()),
            ($this->mock_parser_factory = $this->getMockBuilder(ilMailRfc822AddressParserFactory::class)->disableOriginalConstructor()->getMock()),
            $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->getMock(),
            ($this->mock_log = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock()),
            ($this->mock_database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock()),
            ($this->mock_language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock()),
            $this->getMockBuilder(ilFileDataMail::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailbox::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailMimeSenderFactory::class)->disableOriginalConstructor()->getMock(),
            static function (string $login): int {
                return 780;
            },
            $this->createMock(AutoresponderService::class),
            0,
            $refId,
            $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailTemplatePlaceholderResolver::class)->disableOriginalConstructor()->getMock(),
            null,
            null,
            $this->getMockBuilder(MailSignatureService::class)->disableOriginalConstructor()->getMock(),
        );
    }
}
