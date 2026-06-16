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
use PHPUnit\Framework\Attributes\DataProvider;
use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;
use ILIAS\Mail\TemplateEngine\Mustache\MustacheTemplateEngineFactory;

class ilMailTest extends ilMailBaseTestCase
{
    private MockObject&ilDBInterface $mock_database;
    private MockObject&ilMailAddressTypeFactory $mock_address_type_factory;
    private MockObject&ilLogger $mock_log;
    private MockObject&ilMailRfc822AddressParserFactory $mock_parser_factory;
    private MockObject&ilLanguage $mock_language;

    public function testExternalMailDeliveryWorksAsExpected(): void
    {
        $refinery = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refinery);

        $legal_documents = $this->createMock(Conductor::class);
        $this->setGlobalVariable('legalDocuments', $legal_documents);

        $this->setGlobalVariable('ilIliasIniFile', $this->createMock(ilIniFile::class));
        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilClientIniFile', $this->createMock(ilIniFile::class));
        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));
        $this->setGlobalVariable('ilCtrl', $this->createMock(ilCtrl::class));

        $web_dir = 'public/data';
        define('ILIAS_WEB_DIR', $web_dir);

        $sender_usr_id = 666;
        $active_users_login_to_id_map = [
            'phpunit1' => 1,
            'phpunit2' => 2,
            'phpunit3' => 3,
            'phpunit4' => 4,
            'phpunit5' => 5,
            'phpunit6' => 6,
            'phpunit7' => 7,
        ];
        $expired_users_login_to_id_map = [
            'phpunit8' => 8,
        ];
        $inactive_users_login_to_id_map = [
            'phpunit9' => 9,
        ];
        $inactive_and_expired_users_login_to_id_map = [
            'phpunit10' => 10,
        ];
        $all_users_login_to_id_map = array_merge(
            $active_users_login_to_id_map,
            $expired_users_login_to_id_map,
            $inactive_users_login_to_id_map,
            $inactive_and_expired_users_login_to_id_map
        );

        $transformation = $this->createMock(Transformation::class);
        $transformation->method('applyTo')->willReturn(
            new Ok(null)
        );
        $legal_documents->expects($this->exactly(count($active_users_login_to_id_map)))->method(
            'userCanReadInternalMail'
        )->willReturn($transformation);

        $usr_instances_by_id = [];
        $mail_options_by_id = [];

        $user_groups = [
            'Active And Not Expired' => [
                $active_users_login_to_id_map,
                true,
                true
            ],
            'Active But Expired' => [
                $expired_users_login_to_id_map,
                true,
                false
            ],
            'Inactive And Not Expired' => [
                $inactive_users_login_to_id_map,
                false,
                true
            ],
            'Inactive And Expired' => [
                $inactive_and_expired_users_login_to_id_map,
                false,
                false
            ],
        ];

        foreach ($user_groups as $user_group) {
            foreach ($user_group[0] as $usr_id) {
                $user = $this
                    ->getMockBuilder(ilObjUser::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['getId', 'checkTimeLimit', 'getActive'])
                    ->getMock();
                $user->method('getId')->willReturn($usr_id);
                $user->method('getActive')->willReturn($user_group[1]);
                $user->method('checkTimeLimit')->willReturn($user_group[2]);
                $usr_instances_by_id[$usr_id] = $user;

                $mail_options = $this
                    ->getMockBuilder(ilMailOptions::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['getExternalEmailAddresses', 'getIncomingType'])
                    ->getMock();
                $mail_options->method('getExternalEmailAddresses')->willReturn([
                    'phpunit' . $usr_id . '@ilias.de',
                ]);
                $mail_options->method('getIncomingType')->willReturn(ilMailOptions::INCOMING_EMAIL);
                $mail_options_by_id[$usr_id] = $mail_options;
            }
        }

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        ilMailMimeSenderUserById::addUserToCache($sender_usr_id, $user);

        $address_type_factory = $this
            ->getMockBuilder(ilMailAddressTypeFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByPrefix'])
            ->getMock();
        $address_type_factory
            ->method('getByPrefix')
            ->willReturnCallback(function ($arg) use ($all_users_login_to_id_map): object {
                return new class ($arg, $all_users_login_to_id_map) implements ilMailAddressType {
                    protected array $login_to_id_map = [];

                    public function __construct(protected ilMailAddress $address, $login_to_id_map)
                    {
                        $this->login_to_id_map = array_map(static function (int $usr_id): array {
                            return [$usr_id];
                        }, $login_to_id_map);
                    }

                    public function resolve(): array
                    {
                        return $this->login_to_id_map[$this->address->getMailbox()] ?? [];
                    }

                    public function validate(int $sender_id): bool
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

        $db = $this->createMock(ilDBInterface::class);
        $next_id = 0;
        $db->method('nextId')->willReturnCallback(function () use (&$next_id): int {
            ++$next_id;

            return $next_id;
        });

        $event_handler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $settings->method('get')->willReturn('');
        $this->setGlobalVariable('ilSetting', $settings);

        $mail_file_data = $this->getMockBuilder(ilFileDataMail::class)->disableOriginalConstructor()->getMock();
        $mail_options = $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock();
        $mail_box = $this->getMockBuilder(ilMailbox::class)->disableOriginalConstructor()->getMock();
        $actor = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $template_engine_factory = $this->createMock(TemplateEngineFactoryInterface::class);

        $mail_service = new ilMail(
            $sender_usr_id,
            $address_type_factory,
            new ilMailRfc822AddressParserFactory(),
            $event_handler,
            $logger,
            $db,
            $lng,
            $mail_file_data,
            $mail_options,
            $mail_box,
            new ilMailMimeSenderFactory($settings, $template_engine_factory),
            static fn(string $login): int => $all_users_login_to_id_map[$login] ?? 0,
            $this->createMock(AutoresponderService::class),
            0,
            4711,
            $actor,
            new ilMailTemplatePlaceholderResolver(
                new class () implements \ILIAS\Mail\TemplateEngine\TemplateEngineInterface {
                    public function render(string $template, object|array $context): string
                    {
                        return 'phpunit';
                    }
                }
            )
        );

        $old_transport = ilMimeMail::getDefaultTransport();

        $mail_transport = $this
            ->getMockBuilder(ilMailMimeTransport::class)
            ->getMock();
        $mail_transport->expects($this->once())->method('send')->with($this->callback(function (
            ilMimeMail $mailer
        ) use ($active_users_login_to_id_map): bool {
            $total_bcc = [];
            foreach ($mailer->getBcc() as $bcc) {
                $total_bcc = array_filter(array_map('trim', explode(',', $bcc))) + $total_bcc;
            }

            return count($total_bcc) === count($active_users_login_to_id_map);
        }))->willReturn(true);
        ilMimeMail::setDefaultTransport($mail_transport);

        $mail_service->setUserInstanceById($usr_instances_by_id);
        $mail_service->setMailOptionsByUserIdMap($mail_options_by_id);

        $mail_data = new MailDeliveryData(
            implode(
                ',',
                array_merge(
                    array_slice(array_keys($active_users_login_to_id_map), 0, 3),
                    $expired_users_login_to_id_map,
                    $inactive_users_login_to_id_map,
                    $inactive_and_expired_users_login_to_id_map
                )
            ),
            implode(',', array_slice(array_keys($active_users_login_to_id_map), 3, 2)),
            implode(',', array_slice(array_keys($active_users_login_to_id_map), 5, 2)),
            'Subject',
            'Message',
            [],
            false
        );
        $mail_service->sendMail($mail_data);

        ilMimeMail::setDefaultTransport($old_transport);
    }

    public function testGetMailObjectReferenceId(): void
    {
        $ref_id = 364;
        $instance = $this->create($ref_id);

        $this->assertSame($ref_id, $instance->getMailObjectReferenceId());
    }

    public function testFormatNamesForOutput(): void
    {
        $instance = $this->create();

        $this->mock_language->expects($this->once())->method('txt')->with('not_available')->willReturn('not_available');

        $this->assertSame('not_available', $instance->formatNamesForOutput(''));
        $this->assertSame('', $instance->formatNamesForOutput(','));
    }

    #[DataProvider('provideGetPreviousMail')]
    public function testGetPreviousMail(array $row_data): void
    {
        $mail_id = 3454;
        $instance = $this->createAndExpectDatabaseCall($mail_id, $row_data);
        $this->mock_database->expects($this->once())->method('setLimit')->with(1, 0);
        $instance->getPreviousMail($mail_id);
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
                'user_id' => 0
            ]],
            [[
                'folder_id' => '',
                'mail_id' => '',
                'sender_id' => '',
                'use_placeholders' => '',
                'user_id' => 0
             ]],
        ];
    }

    public function testGetNextMail(): void
    {
        $mail_id = 8484;
        $instance = $this->createAndExpectDatabaseCall($mail_id, []);
        $this->mock_database->expects($this->once())->method('setLimit')->with(1, 0);
        $instance->getNextMail($mail_id);
    }

    public function testGetMailsOfFolder(): void
    {
        $filter = ['status' => 'yes'];
        $row_data = ['mail_id' => 8908];
        $one = $row_data + [
            'attachments' => null,
            'tpl_ctx_params' => [],
            'm_subject' => '',
            'm_message' => '',
            'rcp_to' => '',
            'rcp_cc' => '',
            'rcp_bcc' => '',
        ];
        $expected = [$one, $one];
        $folder_id = 89;
        $usr_id = 901;
        $instance = $this->create(234, $usr_id);
        $mock_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects($this->never())->method('setLimit');
        $this->mock_database->expects($this->exactly(3))->method('fetchAssoc')->with($mock_statement)->willReturnOnConsecutiveCalls($row_data, $row_data, null);
        $this->mock_database->expects($this->once())->method('queryF')->willReturnCallback($this->queryCallback($mock_statement, ['integer', 'integer'], [$usr_id, $folder_id]));

        $this->mock_database->expects($this->once())->method('quote')->with($filter['status'], 'text')->willReturn($filter['status']);

        $this->assertEquals($expected, $instance->getMailsOfFolder($folder_id, $filter));
    }

    public function testCountMailsOfFolder(): void
    {
        $usr_id = 46;
        $folder_id = 68;
        $num_rows = 89;
        $instance = $this->create(345, $usr_id);
        $mock_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects($this->once())->method('queryF')->willReturnCallback($this->queryCallback($mock_statement, ['integer', 'integer'], [$usr_id, $folder_id]));
        $this->mock_database->expects($this->once())->method('numRows')->with($mock_statement)->willReturn($num_rows);

        $this->assertSame($num_rows, $instance->countMailsOfFolder($folder_id));
    }

    public function testGetMail(): void
    {
        $mail_id = 7890;
        $instance = $this->createAndExpectDatabaseCall($mail_id, []);
        $instance->getMail($mail_id);
    }

    public function testMarkRead(): void
    {
        $mail_ids = [1, 2, 3, 4, 5, 6];
        $usr_id = 987;
        $instance = $this->create(567, $usr_id);
        $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects($this->once())->method('in')->with('mail_id', $mail_ids, false, 'integer')->willReturn('');
        $this->mock_database->expects($this->once())->method('manipulateF')->willReturnCallback($this->queryCallback(0, ['text', 'integer'], ['read', $usr_id]));

        $instance->markRead($mail_ids);
    }

    public function testMarkUnread(): void
    {
        $mail_ids = [1, 2, 3, 4, 5, 6];
        $usr_id = 987;
        $instance = $this->create(567, $usr_id);
        $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects($this->once())->method('in')->with('mail_id', $mail_ids, false, 'integer')->willReturn('');
        $this->mock_database->expects($this->once())->method('manipulateF')->willReturnCallback($this->queryCallback(0, ['text', 'integer'], ['unread', $usr_id]));

        $instance->markUnread($mail_ids);
    }

    public function testMoveMailsToFolder(): void
    {
        $mail_ids = [1, 2, 3, 4, 5, 6];
        $folder_id = 890;
        $usr_id = 987;
        $instance = $this->create(567, $usr_id);
        $this->mock_database->expects($this->once())->method('in')->with('mail_id', $mail_ids, false, 'integer')->willReturn('');
        $this->mock_database->expects($this->once())->method('manipulateF')->willReturnCallback($this->queryCallback(1, ['integer', 'integer', 'integer'], [$folder_id, $usr_id, $usr_id]));

        $this->assertTrue($instance->moveMailsToFolder($mail_ids, $folder_id));
    }

    public function testMoveMailsToFolderFalse(): void
    {
        $mail_ids = [];
        $instance = $this->create();
        $this->mock_database->expects($this->never())->method('in');
        $this->mock_database->expects($this->never())->method('manipulateF');

        $this->assertFalse($instance->moveMailsToFolder($mail_ids, 892));
    }

    public function testGetNewDraftId(): void
    {
        $next_id = 789;
        $usr_id = 5678;
        $folder_id = 47;
        $instance = $this->create(4749, $usr_id);

        $this->mock_database->expects($this->once())->method('nextId')->with('mail')->willReturn($next_id);
        $this->mock_database->expects($this->once())->method('insert')->with('mail', [
            'mail_id' => ['integer', $next_id],
            'user_id' => ['integer', $usr_id],
            'folder_id' => ['integer', $folder_id],
            'sender_id' => ['integer', $usr_id],
        ]);

        $this->assertSame($next_id, $instance->getNewDraftId($folder_id));
    }

    public function testUpdateDraft(): void
    {
        $send_time = '2022-01-01 00:00:00';
        $tz = new DateTimeZone('Europe/Berlin');
        $date_time = new DateTimeImmutable($send_time, $tz);

        $folder_id = 7890;
        $instance = $this->create();
        $to = 'abc';
        $cc = 'bcde';
        $bcc = 'jkl';
        $subject = 'jlh';
        $message = 'some message';
        $use_placeholders = true;
        $context_id = '87';
        $params = [];
        $draft_id = 78;

        $this->mock_database->expects($this->once())->method('update')->with('mail', [
            'folder_id' => ['integer', $folder_id],
            'attachments' => ['clob', serialize([])],
            'send_time' => ['timestamp', date('Y-m-d H:i:s')],
            'rcp_to' => ['clob', $to],
            'rcp_cc' => ['clob', $cc],
            'rcp_bcc' => ['clob', $bcc],
            'm_status' => ['text', 'read'],
            'm_subject' => ['text', $subject],
            'm_message' => ['clob', $message],
            'use_placeholders' => ['integer', (int) $use_placeholders],
            'tpl_ctx_id' => ['text', $context_id],
            'tpl_ctx_params' => ['blob', json_encode($params, JSON_THROW_ON_ERROR)],
            'schedule_datetime' => ['timestamp', $date_time->format('Y-m-d H:i:s')],
            'schedule_timezone' => ['text', $tz->getName()],
        ], [
            'mail_id' => ['integer', $draft_id],
        ]);

        $this->assertSame(
            $draft_id,
            $instance->updateDraft(
                $folder_id,
                [],
                $to,
                $cc,
                $bcc,
                $subject,
                $message,
                $draft_id,
                $date_time,
                $use_placeholders,
                $context_id,
                $params,
            )
        );
    }

    public function testPersistingToStage(): void
    {
        $usr_id = 897;
        $attachments = null;
        $rcp_to = 'jlh';
        $rcp_cc = 'jhkjh';
        $rcp_bcc = 'ououi';
        $subject = 'hbansn';
        $message = 'message';
        $use_placeholders = false;
        $context_id = '9080';
        $params = [];

        $instance = $this->create(789, $usr_id);

        $this->mock_database->expects($this->once())->method('replace')->with('mail_saved', [
            'user_id' => ['integer', $usr_id],
        ], [
            'attachments' => ['text', $attachments],
            'rcp_to' => ['clob', $rcp_to],
            'rcp_cc' => ['clob', $rcp_cc],
            'rcp_bcc' => ['clob', $rcp_bcc],
            'm_subject' => ['text', $subject],
            'm_message' => ['clob', $message],
            'use_placeholders' => ['integer', (int) $use_placeholders],
            'tpl_ctx_id' => ['text', $context_id],
            'tpl_ctx_params' => ['blob', json_encode($params, JSON_THROW_ON_ERROR)],
        ]);

        $mock_statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mock_database->expects($this->once())->method('queryF')->willReturnCallback($this->queryCallback($mock_statement, ['integer'], [$usr_id]));
        $this->mock_database->expects($this->once())->method('fetchAssoc')->with($mock_statement)->willReturn([
            'rcp_to' => 'phpunit'
        ]);

        $instance->persistToStage(
            $usr_id,
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            $subject,
            $message,
            $attachments,
            $use_placeholders,
            $context_id,
            $params,
        );
    }

    public function testRetrievalFromStage(): void
    {
        $usr_id = 789;
        $instance = $this->create(67, $usr_id);
        $mock_statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mock_database->expects($this->once())->method('queryF')->willReturnCallback($this->queryCallback($mock_statement, ['integer'], [$usr_id]));
        $this->mock_database->expects($this->once())->method('fetchAssoc')->with($mock_statement)->willReturn([
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
        $this->mock_log->expects($this->exactly(6))->method('debug')->with(
            $this->callback(function ($value) use (&$consecutive_debug) {
                $this->assertSame(array_shift($consecutive_debug), $value);
                return true;
            }),
        );

        $mock_address = $this->getMockBuilder(ilMailAddress::class)->disableOriginalConstructor()->getMock();
        $mock_address->expects($this->exactly(3))->method('__toString')->willReturn('hello');
        $mock_parser = $this->getMockBuilder(ilMailRecipientParser::class)->disableOriginalConstructor()->getMock();
        $mock_parser->expects($this->exactly(3))->method('parse')->willReturn([$mock_address]);
        $consecutive_get = [$to, $cc, $bcc];
        $this->mock_parser_factory->expects($this->exactly(3))->method('getParser')->with(
            $this->callback(function ($value) use (&$consecutive_get) {
                $this->assertSame(array_shift($consecutive_get), $value);
                return true;
            }),
        )->willReturn($mock_parser);

        $mock_addressType = $this->getMockBuilder(ilMailAddressType::class)->disableOriginalConstructor()->getMock();
        $mock_addressType->expects($this->exactly(3))->method('validate')->willReturn(empty($errors));
        $mock_addressType->expects($this->exactly(empty($errors) ? 0 : 3))->method('getErrors')->willReturn($errors);
        $this->mock_address_type_factory->expects($this->exactly(3))->method('getByPrefix')->with($mock_address)->willReturn($mock_addressType);

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
        $settings
            ->expects($this->once())
            ->method('get')
            ->with('mail_system_sys_from_name')
            ->willReturn($expected);
        $this->setGlobalVariable('ilSetting', $settings);


        $this->assertSame($expected, ilMail::_getIliasMailerName());
    }

    public function testSaveAttachments(): void
    {
        $usr_id = 89;
        $attachments = new \ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification('657497dc-5079-4f95-b19d-aecdaf81ff1a');
        $instance = $this->create(789, $usr_id);

        $this->mock_database->expects($this->once())->method('update')->with(
            'mail_saved',
            [
                'attachments' => ['text', $attachments->serialize()],
            ],
            [
                'user_id' => ['integer', $usr_id],
            ]
        );

        $instance->saveAttachments($attachments);
    }

    private function queryCallback($return_value, array $expected_types, array $expected_values): Closure
    {
        return function (string $query, array $types, array $values) use ($expected_types, $expected_values, $return_value) {
            $this->assertEquals($expected_types, $types);
            $this->assertEquals($expected_values, $values);

            return $return_value;
        };
    }

    private function createAndExpectDatabaseCall(int $some_mail_id, array $row_data): ilMail
    {
        $usr_id = 900;
        $instance = $this->create(234, $usr_id);
        $mock_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->mock_database->expects($this->once())->method('fetchAssoc')->with($mock_statement)->willReturn($row_data);
        $this->mock_database->expects($this->once())->method('queryF')->willReturnCallback($this->queryCallback($mock_statement, ['integer', 'integer'], [$usr_id, $some_mail_id]));

        return $instance;
    }

    private function create(int $ref_id = 234, int $usr_id = 123): ilMail
    {
        $refinery = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refinery);

        $instance = new ilMail(
            $usr_id,
            ($this->mock_address_type_factory = $this->getMockBuilder(ilMailAddressTypeFactory::class)->disableOriginalConstructor()->getMock()),
            ($this->mock_parser_factory = $this->getMockBuilder(ilMailRfc822AddressParserFactory::class)->disableOriginalConstructor()->getMock()),
            $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->getMock(),
            ($this->mock_log = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock()),
            ($this->mock_database = $this->createMock(ilDBInterface::class)),
            ($this->mock_language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock()),
            $this->getMockBuilder(ilFileDataMail::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailbox::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailMimeSenderFactory::class)->disableOriginalConstructor()->getMock(),
            static fn(string $login): int => 780,
            $this->createMock(AutoresponderService::class),
            0,
            $ref_id,
            $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilMailTemplatePlaceholderResolver::class)->disableOriginalConstructor()->getMock(),
            null,
            null,
            $this->getMockBuilder(MailSignatureService::class)->disableOriginalConstructor()->getMock(),
        );

        return $instance;
    }
}
