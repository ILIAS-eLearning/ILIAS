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

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\Mail\Autoresponder\AutoresponderService;
use ILIAS\LegalDocuments\Conductor;
use ILIAS\Mail\Recipient;
use ILIAS\Mail\Service\MailSignatureService;

class ilMail
{
    public const string ILIAS_HOST = 'ilias';
    public const string PROP_CONTEXT_SUBJECT_PREFIX = 'subject_prefix';

    private MailSignatureService $signature_service;
    public int $user_id;
    private string $table_mail;
    private string $table_mail_saved;
    /** @var array<string, mixed>|null */
    protected ?array $mail_data = [];
    private bool $save_in_sentbox;
    private bool $append_installation_signature = false;
    private bool $append_user_signature = false;

    private ?string $context_id = null;
    private array $context_parameters = [];

    /** @var array<int, ilMailOptions> */
    private array $mail_options_by_usr_id_map = [];

    /** @var array<int, null|ilObjUser> */
    private array $user_instances_by_id_map = [];
    private int $max_recipient_character_length = 998;
    private readonly Conductor $legal_documents;

    public function __construct(
        private int $a_user_id,
        private ?ilMailAddressTypeFactory $mail_address_type_factory = null,
        private ilMailRfc822AddressParserFactory $mail_address_parser_factory = new ilMailRfc822AddressParserFactory(),
        private ?ilAppEventHandler $event_handler = null,
        private ?ilLogger $logger = null,
        private ?ilDBInterface $db = null,
        private ?ilLanguage $lng = null,
        private ?ilFileDataMail $mail_file_data = null,
        protected ?ilMailOptions $mail_options = null,
        private ?ilMailbox $mailbox = null,
        private ?ilMailMimeSenderFactory $sender_factory = null,
        private ?Closure $usr_id_by_login_callable = null,
        private ?AutoresponderService $auto_responder_service = null,
        private ?int $mail_admin_node_ref_id = null,
        private ?int $mail_obj_ref_id = null,
        private ?ilObjUser $actor = null,
        private ?ilMailTemplatePlaceholderResolver $placeholder_resolver = null,
        private ?ilMailTemplatePlaceholderToEmptyResolver $placeholder_to_empty_resolver = null,
        ?Conductor $legal_documents = null,
        ?MailSignatureService $signature_service = null,
    ) {
        global $DIC;
        $this->logger = $logger ?? ilLoggerFactory::getLogger('mail');
        $this->mail_address_type_factory = $mail_address_type_factory ?? new ilMailAddressTypeFactory(null, $logger);
        $this->event_handler = $event_handler ?? $DIC->event();
        $this->db = $db ?? $DIC->database();
        $this->lng = $lng ?? $DIC->language();
        $this->actor = $actor ?? $DIC->user();
        $this->mail_file_data = $mail_file_data ?? new ilFileDataMail($a_user_id);
        $this->mail_options = $mail_options ?? new ilMailOptions($a_user_id);
        $this->mailbox = $mailbox ?? new ilMailbox($a_user_id);

        $this->sender_factory = $sender_factory ?? $DIC->mail()->mime()->senderFactory();
        $this->usr_id_by_login_callable = $usr_id_by_login_callable ?? (static fn(string $login): int => (int) ilObjUser::_lookupId($login));
        $this->auto_responder_service = $auto_responder_service ?? $DIC->mail()->autoresponder();
        $this->user_id = $a_user_id;
        if ($this->mail_obj_ref_id === null) {
            $this->readMailObjectReferenceId();
        }
        $this->lng->loadLanguageModule('mail');
        $this->table_mail = 'mail';
        $this->table_mail_saved = 'mail_saved';
        $this->setSaveInSentbox(false);
        $this->placeholder_resolver = $placeholder_resolver ?? $DIC->mail()->placeholderResolver();
        $this->placeholder_to_empty_resolver = $placeholder_to_empty_resolver ?? $DIC->mail()->placeholderToEmptyResolver();
        $this->legal_documents = $legal_documents ?? $DIC['legalDocuments'];
        $this->signature_service = $signature_service ?? $DIC->mail()->signature();
    }

    public function autoresponder(): AutoresponderService
    {
        return $this->auto_responder_service;
    }

    public function withContextId(string $context_id): self
    {
        $clone = clone $this;

        $clone->context_id = $context_id;

        return $clone;
    }

    public function withContextParameters(array $parameters): self
    {
        $clone = clone $this;

        $clone->context_parameters = $parameters;

        return $clone;
    }

    private function isSystemMail(): bool
    {
        return $this->user_id === ANONYMOUS_USER_ID;
    }

    public function existsRecipient(string $new_recipient, string $existing_recipients): bool
    {
        $new_addresses = new ilMailAddressListImpl($this->parseAddresses($new_recipient));
        $addresses = new ilMailAddressListImpl($this->parseAddresses($existing_recipients));
        $list = new ilMailDiffAddressList($new_addresses, $addresses);

        $diffed_addresses = $list->value();

        return $diffed_addresses === [];
    }

    public function setSaveInSentbox(bool $save_in_sentbox): void
    {
        $this->save_in_sentbox = $save_in_sentbox;
    }

    public function getSaveInSentbox(): bool
    {
        return $this->save_in_sentbox;
    }

    private function readMailObjectReferenceId(): void
    {
        $this->mail_obj_ref_id = ilMailGlobalServices::getMailObjectRefId();
    }

    public function getMailObjectReferenceId(): int
    {
        return $this->mail_obj_ref_id;
    }

    public function formatNamesForOutput(string $recipients): string
    {
        $recipients = trim($recipients);
        if ($recipients === '') {
            return $this->lng->txt('not_available');
        }

        $names = [];

        $recipients = array_filter(array_map('trim', explode(',', $recipients)));
        foreach ($recipients as $recipient) {
            $usr_id = ilObjUser::_lookupId($recipient);
            if (is_int($usr_id) && $usr_id > 0) {
                $pp = ilObjUser::_lookupPref($usr_id, 'public_profile');
                if ($pp === 'g' || ($pp === 'y' && !$this->actor->isAnonymous())) {
                    $user = $this->getUserInstanceById($usr_id);
                    if ($user) {
                        $names[] = $user->getFullname() . ' [' . $recipient . ']';
                        continue;
                    }
                }
            }

            $names[] = $recipient;
        }

        return implode(', ', $names);
    }

    public function getPreviousMail(int $mail_id): ?array
    {
        $this->db->setLimit(1, 0);

        $query = implode(' ', [
            "SELECT b.* FROM $this->table_mail a",
            "INNER JOIN $this->table_mail b ON b.folder_id = a.folder_id",
            'AND b.user_id = a.user_id AND b.send_time > a.send_time',
            'WHERE a.user_id = %s AND a.mail_id = %s ORDER BY b.send_time ASC',
        ]);
        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$this->user_id, $mail_id]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    public function getNextMail(int $mail_id): ?array
    {
        $this->db->setLimit(1, 0);

        $query = implode(' ', [
            "SELECT b.* FROM $this->table_mail a",
            "INNER JOIN $this->table_mail b ON b.folder_id = a.folder_id",
            'AND b.user_id = a.user_id AND b.send_time < a.send_time',
            'WHERE a.user_id = %s AND a.mail_id = %s ORDER BY b.send_time DESC',
        ]);
        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$this->user_id, $mail_id]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    public function getMailsOfFolder(int $a_folder_id, array $filter = []): array
    {
        $mails = [];

        $query =
            'SELECT sender_id, m_subject, mail_id, m_status, send_time, import_name ' .
            "FROM $this->table_mail " .
            'LEFT JOIN object_data ON obj_id = sender_id ' .
            'WHERE user_id = %s AND folder_id = %s ' .
            'AND ((sender_id > 0 AND sender_id IS NOT NULL AND obj_id IS NOT NULL) ' .
            'OR (sender_id = 0 OR sender_id IS NULL))';

        if (isset($filter['status']) && $filter['status'] !== '') {
            $query .= ' AND m_status = ' . $this->db->quote($filter['status'], 'text');
        }

        $query .= ' ORDER BY send_time DESC';

        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$this->user_id, $a_folder_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $mails[] = $this->fetchMailData($row);
        }

        return array_filter($mails);
    }

    public function countMailsOfFolder(int $folder_id): int
    {
        $res = $this->db->queryF(
            "SELECT COUNT(*) FROM $this->table_mail WHERE user_id = %s AND folder_id = %s",
            ['integer', 'integer'],
            [$this->user_id, $folder_id]
        );

        return $this->db->numRows($res);
    }

    public function deleteMailsOfFolder(int $folder_id): void
    {
        $mails = $this->getMailsOfFolder($folder_id);
        foreach ($mails as $mail_data) {
            $this->deleteMails([$mail_data['mail_id']]);
        }
    }

    public function getMail(int $mail_id): ?array
    {
        $res = $this->db->queryF(
            "SELECT * FROM $this->table_mail WHERE user_id = %s AND mail_id = %s",
            ['integer', 'integer'],
            [$this->user_id, $mail_id]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    /**
     * @param list<int> $mail_ids
     */
    public function markRead(array $mail_ids): void
    {
        $values = [];
        $types = [];

        $query = "UPDATE $this->table_mail SET m_status = %s WHERE user_id = %s ";
        $types[] = 'text';
        $types[] = 'integer';
        $values[] = 'read';
        $values[] = $this->user_id;

        if ($mail_ids !== []) {
            $query .= ' AND ' . $this->db->in('mail_id', $mail_ids, false, 'integer');
        }

        $this->db->manipulateF($query, $types, $values);
    }

    /**
     * @param list<int> $mail_ids
     */
    public function markUnread(array $mail_ids): void
    {
        $values = [];
        $types = [];

        $query = "UPDATE $this->table_mail SET m_status = %s WHERE user_id = %s ";
        $types[] = 'text';
        $types[] = 'integer';
        $values[] = 'unread';
        $values[] = $this->user_id;

        if ($mail_ids !== []) {
            $query .= ' AND ' . $this->db->in('mail_id', $mail_ids, false, 'integer');
        }

        $this->db->manipulateF($query, $types, $values);
    }

    /**
     * @param list<int> $mail_ids
     */
    public function moveMailsToFolder(array $mail_ids, int $folder_id): bool
    {
        $values = [];
        $types = [];

        $mail_ids = array_filter(array_map(intval(...), $mail_ids));

        if ([] === $mail_ids) {
            return false;
        }

        $query =
            "UPDATE $this->table_mail " .
            'INNER JOIN mail_obj_data ' .
            'ON mail_obj_data.obj_id = %s AND mail_obj_data.user_id = %s ' .
            "SET $this->table_mail.folder_id = mail_obj_data.obj_id " .
            "WHERE $this->table_mail.user_id = %s";
        $types[] = 'integer';
        $types[] = 'integer';
        $types[] = 'integer';
        $values[] = $folder_id;
        $values[] = $this->user_id;
        $values[] = $this->user_id;

        $query .= ' AND ' . $this->db->in('mail_id', $mail_ids, false, 'integer');

        $affected_rows = $this->db->manipulateF($query, $types, $values);

        return $affected_rows > 0;
    }

    /**
     * @param int[] $mail_ids
     */
    public function deleteMails(array $mail_ids): void
    {
        $mail_ids = array_filter(array_map('intval', $mail_ids));
        foreach ($mail_ids as $id) {
            $this->db->manipulateF(
                "DELETE FROM $this->table_mail WHERE user_id = %s AND mail_id = %s",
                ['integer', 'integer'],
                [$this->user_id, $id]
            );
            $this->mail_file_data->deassignAttachmentFromDirectory($id);
        }
    }

    private function fetchMailData(?array $row): ?array
    {
        if (!is_array($row) || empty($row)) {
            return null;
        }

        if (isset($row['attachments'])) {
            $unserialized = unserialize(stripslashes((string) $row['attachments']), ['allowed_classes' => false]);
            $row['attachments'] = is_array($unserialized) ? $unserialized : [];
        } else {
            $row['attachments'] = [];
        }

        if (isset($row['tpl_ctx_params']) && is_string($row['tpl_ctx_params'])) {
            $decoded = json_decode($row['tpl_ctx_params'], true, 512, JSON_THROW_ON_ERROR);
            $row['tpl_ctx_params'] = (array) ($decoded ?? []);
        } else {
            $row['tpl_ctx_params'] = [];
        }

        if (isset($row['mail_id'])) {
            $row['mail_id'] = (int) $row['mail_id'];
        }

        if (isset($row['user_id'])) {
            $row['user_id'] = (int) $row['user_id'];
        }

        if (isset($row['folder_id'])) {
            $row['folder_id'] = (int) $row['folder_id'];
        }

        if (isset($row['sender_id'])) {
            $row['sender_id'] = (int) $row['sender_id'];
        }

        if (isset($row['use_placeholders'])) {
            $row['use_placeholders'] = (bool) $row['use_placeholders'];
        }

        $null_to_string_properties = ['m_subject', 'm_message', 'rcp_to', 'rcp_cc', 'rcp_bcc'];
        foreach ($null_to_string_properties as $null_to_string_property) {
            if (!isset($row[$null_to_string_property])) {
                $row[$null_to_string_property] = '';
            }
        }

        return $row;
    }

    public function getNewDraftId(int $folder_id): int
    {
        $next_id = $this->db->nextId($this->table_mail);
        $this->db->insert($this->table_mail, [
            'mail_id' => ['integer', $next_id],
            'user_id' => ['integer', $this->user_id],
            'folder_id' => ['integer', $folder_id],
            'sender_id' => ['integer', $this->user_id],
        ]);

        return $next_id;
    }

    /**
     * @param list<string> $a_attachments
     */
    public function updateDraft(
        int $a_folder_id,
        array $a_attachments,
        string $a_rcp_to,
        string $a_rcp_cc,
        string $a_rcp_bcc,
        string $a_m_subject,
        string $a_m_message,
        int $a_draft_id = 0,
        bool $a_use_placeholders = false,
        ?string $a_tpl_context_id = null,
        array $a_tpl_context_params = []
    ): int {
        $this->db->update(
            $this->table_mail,
            [
                'folder_id' => ['integer', $a_folder_id],
                'attachments' => ['clob', serialize($a_attachments)],
                'send_time' => ['timestamp', date('Y-m-d H:i:s')],
                'rcp_to' => ['clob', $a_rcp_to],
                'rcp_cc' => ['clob', $a_rcp_cc],
                'rcp_bcc' => ['clob', $a_rcp_bcc],
                'm_status' => ['text', 'read'],
                'm_subject' => ['text', $a_m_subject],
                'm_message' => ['clob', $a_m_message],
                'use_placeholders' => ['integer', (int) $a_use_placeholders],
                'tpl_ctx_id' => ['text', $a_tpl_context_id],
                'tpl_ctx_params' => ['blob', json_encode($a_tpl_context_params, JSON_THROW_ON_ERROR)],
            ],
            [
                'mail_id' => ['integer', $a_draft_id],
            ]
        );

        return $a_draft_id;
    }

    private function sendInternalMail(
        int $folder_id,
        int $sender_usr_id,
        array $attachments,
        string $to,
        string $cc,
        string $bcc,
        string $status,
        string $subject,
        string $message,
        int $usr_id = 0,
        bool $use_placeholders = false,
        ?string $template_contenxt_id = null,
        array $template_context_parameters = []
    ): int {
        $usr_id = $usr_id ?: $this->user_id;

        if ($use_placeholders) {
            $message = $this->replacePlaceholders($message, $usr_id);
        }
        $message = str_ireplace(['<br />', '<br>', '<br/>'], "\n", $message);

        $next_id = $this->db->nextId($this->table_mail);
        $this->db->insert($this->table_mail, [
            'mail_id' => ['integer', $next_id],
            'user_id' => ['integer', $usr_id],
            'folder_id' => ['integer', $folder_id],
            'sender_id' => ['integer', $sender_usr_id],
            'attachments' => ['clob', serialize($attachments)],
            'send_time' => ['timestamp', date('Y-m-d H:i:s')],
            'rcp_to' => ['clob', $to],
            'rcp_cc' => ['clob', $cc],
            'rcp_bcc' => ['clob', $bcc],
            'm_status' => ['text', $status],
            'm_subject' => ['text', $subject],
            'm_message' => ['clob', $message],
            'tpl_ctx_id' => ['text', $template_contenxt_id],
            'tpl_ctx_params' => ['blob', json_encode($template_context_parameters, JSON_THROW_ON_ERROR)],
        ]);

        $sender_equals_reveiver = $usr_id === $this->mailbox->getUsrId();
        $is_sent_folder_of_sender = false;
        if ($sender_equals_reveiver) {
            $current_folder_id = $this->getSubjectSentFolderId();
            $is_sent_folder_of_sender = $folder_id === $current_folder_id;
        }

        $raise_event = !$sender_equals_reveiver || !$is_sent_folder_of_sender;

        if ($raise_event) {
            $this->event_handler->raise('components/ILIAS/Mail', 'sentInternalMail', [
                'id' => $next_id,
                'subject' => $subject,
                'body' => $message,
                'from_usr_id' => $sender_usr_id,
                'to_usr_id' => $usr_id,
                'rcp_to' => $to,
                'rcp_cc' => $cc,
                'rcp_bcc' => $bcc,
            ]);
        }

        return $next_id;
    }

    private function replacePlaceholders(
        string $message,
        int $usr_id = 0
    ): string {
        try {
            if ($this->context_id) {
                $context = ilMailTemplateContextService::getTemplateContextById($this->context_id);
            } else {
                $context = new ilMailTemplateGenericContext();
            }

            $user = $usr_id > 0 ? $this->getUserInstanceById($usr_id) : null;
            $message = $this->placeholder_resolver->resolve(
                $context,
                $message,
                $user,
                $this->context_parameters
            );
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                '%s has been called with invalid context: %s / %s',
                __METHOD__,
                $e->getMessage(),
                $e->getTraceAsString()
            ));
        }

        return $message;
    }

    private function replacePlaceholdersEmpty(string $message): string
    {
        return $this->placeholder_to_empty_resolver->resolve($message);
    }

    private function distributeMail(MailDeliveryData $mail_data): bool
    {
        $this->auto_responder_service->emptyAutoresponderData();
        $to_usr_ids = $this->getUserIds([$mail_data->getTo()]);
        $this->logger->debug(sprintf(
            'Parsed TO user ids from given recipients for serial letter notification: %s',
            implode(', ', $to_usr_ids)
        ));

        $other_usr_ids = $this->getUserIds([$mail_data->getCc(), $mail_data->getBcc()]);
        $cc_bcc_recipients = array_map(
            $this->createRecipient(...),
            $other_usr_ids
        );
        $this->logger->debug(sprintf(
            'Parsed CC/BCC user ids from given recipients for serial letter notification: %s',
            implode(', ', $other_usr_ids)
        ));

        if ($mail_data->isUsePlaceholder()) {
            $this->sendMailWithReplacedPlaceholder($mail_data, $to_usr_ids);
            $this->sendMailWithReplacedEmptyPlaceholder($mail_data, $cc_bcc_recipients);
        } else {
            $this->sendMailWithoutReplacedPlaceholder($mail_data, $to_usr_ids, $cc_bcc_recipients);
        }

        $this->auto_responder_service->disableAutoresponder();
        $this->auto_responder_service->handleAutoresponderMails($this->user_id);

        return true;
    }

    private function sendMailWithReplacedPlaceholder(
        MailDeliveryData $mail_data,
        array $to_usr_ids
    ): void {
        foreach ($to_usr_ids as $user_id) {
            $recipient = $this->createRecipient($user_id);

            $this->sendChanneledMails(
                $mail_data,
                [$recipient],
                $this->replacePlaceholders($mail_data->getMessage(), $user_id),
            );
        }
    }

    private function sendMailWithReplacedEmptyPlaceholder(
        MailDeliveryData $mail_data,
        array $recipients,
    ): void {
        $this->sendChanneledMails(
            $mail_data,
            $recipients,
            $this->replacePlaceholdersEmpty($mail_data->getMessage()),
        );
    }

    private function sendMailWithoutReplacedPlaceholder(
        MailDeliveryData $mail_data,
        array $to_usr_ids,
        array $cc_bcc_recipients
    ): void {
        $to_recipients = array_map(
            $this->createRecipient(...),
            $to_usr_ids
        );

        $this->sendChanneledMails(
            $mail_data,
            array_merge($to_recipients, $cc_bcc_recipients),
            $mail_data->getMessage()
        );
    }

    /**
     * @param list<Recipient> $recipients
     */
    private function sendChanneledMails(
        MailDeliveryData $mail_data,
        array $recipients,
        string $message
    ): void {
        $usr_id_to_external_email_addresses_map = [];

        foreach ($recipients as $recipient) {
            if (!$recipient->isUser()) {
                $this->logger->critical(sprintf(
                    'Skipped recipient with id %s (User not found)',
                    $recipient->getUserId()
                ));
                continue;
            }

            $can_read_internal = $recipient->evaluateInternalMailReadability();
            if ($this->isSystemMail() && !$can_read_internal->isOk()) {
                $this->logger->debug(sprintf(
                    'Skipped recipient with id %s and reason: %s',
                    $recipient->getUserId(),
                    is_string($can_read_internal->error()) ? $can_read_internal->error() : $can_read_internal->error()->getMessage()
                ));
                continue;
            }

            if ($recipient->isUserActive()) {
                if (!$can_read_internal->isOk() || $recipient->userWantsToReceiveExternalMails()) {
                    $email_addresses = $recipient->getExternalMailAddress();
                    $usr_id_to_external_email_addresses_map[$recipient->getUserId()] = $email_addresses;

                    if ($recipient->onlyToExternalMailAddress()) {
                        $this->logger->debug(sprintf(
                            'Recipient with id %s will only receive external emails sent to: %s',
                            $recipient->getUserId(),
                            implode(', ', $email_addresses)
                        ));
                        continue;
                    }

                    $this->logger->debug(sprintf(
                        'Recipient with id %s will additionally receive external emails ' .
                        '(because the user wants to receive it externally, or the user cannot access ' .
                        'the internal mail system) sent to: %s',
                        $recipient->getUserId(),
                        implode(', ', $email_addresses)
                    ));
                } else {
                    $this->logger->debug(sprintf(
                        'Recipient with id %s is does not want to receive external emails',
                        $recipient->getUserId()
                    ));
                }
            } else {
                $this->logger->debug(sprintf(
                    'Recipient with id %s is inactive and will not receive external emails',
                    $recipient->getUserId()
                ));
            }

            $mbox = clone $this->mailbox;
            $mbox->setUsrId($recipient->getUserId());
            $recipient_inbox_id = $mbox->getInboxFolder();

            $internal_mail_id = $this->sendInternalMail(
                $recipient_inbox_id,
                $this->user_id,
                $mail_data->getAttachments(),
                $mail_data->getTo(),
                $mail_data->getCc(),
                '',
                'unread',
                $mail_data->getSubject(),
                $message,
                $recipient->getUserId()
            );

            $this->auto_responder_service->enqueueAutoresponderIfEnabled(
                $recipient->getUserId(),
                $recipient->getMailOptions(),
                $this->getMailOptionsByUserId($this->user_id),
            );

            if ($mail_data->getAttachments() !== []) {
                $this->mail_file_data->assignAttachmentsToDirectory($internal_mail_id, $mail_data->getInternalMailId());
            }
        }

        $this->delegateExternalEmails(
            $mail_data->getSubject(),
            $mail_data->getAttachments(),
            $message,
            $usr_id_to_external_email_addresses_map
        );
    }

    /**
     * @param list<string>         $attachments
     * @param array<int, string[]> $usr_id_to_external_email_addresses_map
     */
    private function delegateExternalEmails(
        string $subject,
        array $attachments,
        string $message,
        array $usr_id_to_external_email_addresses_map
    ): void {
        if (count($usr_id_to_external_email_addresses_map) === 1) {
            $usr_id_to_external_email_addresses_map = array_values($usr_id_to_external_email_addresses_map);
            $first_addresses = current($usr_id_to_external_email_addresses_map);

            $this->sendMimeMail(
                implode(',', $first_addresses),
                '',
                '',
                $subject,
                $message,
                $attachments
            );
        } elseif (count($usr_id_to_external_email_addresses_map) > 1) {
            $flattened_email_addresses = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator(
                $usr_id_to_external_email_addresses_map
            )), false);

            $flattened_email_addresses = array_unique($flattened_email_addresses);

            // https://mantis.ilias.de/view.php?id=23981 and https://www.ietf.org/rfc/rfc2822.txt
            $remaining_addresses = '';
            foreach ($flattened_email_addresses as $email_address) {
                $sep = '';
                if ($remaining_addresses !== '') {
                    $sep = ',';
                }

                $recipients_line_length = ilStr::strLen($remaining_addresses) +
                    ilStr::strLen($sep . $email_address);
                if ($recipients_line_length >= $this->max_recipient_character_length) {
                    $this->sendMimeMail(
                        '',
                        '',
                        $remaining_addresses,
                        $subject,
                        $message,
                        $attachments
                    );

                    $remaining_addresses = '';
                    $sep = '';
                }

                $remaining_addresses .= ($sep . $email_address);
            }

            if ($remaining_addresses !== '') {
                $this->sendMimeMail(
                    '',
                    '',
                    $remaining_addresses,
                    $subject,
                    $message,
                    $attachments
                );
            }
        }
    }

    /**
     * @param list<string> $recipients
     * @return list<int>
     */
    private function getUserIds(array $recipients): array
    {
        $parsed_usr_ids = [];

        $joined_recipients = implode(',', array_filter(array_map('trim', $recipients)));

        $addresses = $this->parseAddresses($joined_recipients);
        foreach ($addresses as $address) {
            $address_type = $this->mail_address_type_factory->getByPrefix($address);
            $parsed_usr_ids[] = $address_type->resolve();
        }

        return array_unique(array_merge(...$parsed_usr_ids));
    }

    /**
     * @return list<ilMailError>
     */
    private function checkMail(string $to, string $cc, string $bcc, string $subject): array
    {
        $errors = [];

        $checks = [
            $subject => 'mail_add_subject',
            $to => 'mail_add_recipient',
        ];
        foreach ($checks as $string => $error) {
            if ($string === '') {
                $errors[] = new ilMailError($error);
            }
        }

        if (ilStr::strLen($subject) > 255) {
            // https://mantis.ilias.de/view.php?id=37881
            $errors[] = new ilMailError('mail_subject_too_long');
        }

        return $errors;
    }

    /**
     * @return list<ilMailError>
     */
    private function checkRecipients(string $recipients): array
    {
        $errors = [];

        try {
            $addresses = $this->parseAddresses($recipients);
            foreach ($addresses as $address) {
                $address_type = $this->mail_address_type_factory->getByPrefix($address);
                if (!$address_type->validate($this->user_id)) {
                    $errors[] = $address_type->getErrors();
                }
            }
        } catch (Exception $e) {
            $position = strpos($e->getMessage(), ':');
            throw new ilMailException(
                ($position === false) ? $e->getMessage() : substr($e->getMessage(), $position + 2),
                $e->getCode(),
                $e
            );
        }

        return array_merge(...$errors);
    }

    /**
     * @param list<string> $a_attachments
     */
    public function persistToStage(
        int $a_user_id,
        array $a_attachments,
        string $a_rcp_to,
        string $a_rcp_cc,
        string $a_rcp_bcc,
        string $a_m_subject,
        string $a_m_message,
        bool $a_use_placeholders = false,
        ?string $a_tpl_context_id = null,
        ?array $a_tpl_ctx_params = []
    ): bool {
        $this->db->replace(
            $this->table_mail_saved,
            [
                'user_id' => ['integer', $this->user_id],
            ],
            [
                'attachments' => ['clob', serialize($a_attachments)],
                'rcp_to' => ['clob', $a_rcp_to],
                'rcp_cc' => ['clob', $a_rcp_cc],
                'rcp_bcc' => ['clob', $a_rcp_bcc],
                'm_subject' => ['text', $a_m_subject],
                'm_message' => ['clob', $a_m_message],
                'use_placeholders' => ['integer', (int) $a_use_placeholders],
                'tpl_ctx_id' => ['text', $a_tpl_context_id],
                'tpl_ctx_params' => ['blob', json_encode((array) $a_tpl_ctx_params, JSON_THROW_ON_ERROR)],
            ]
        );

        $this->retrieveFromStage();

        return true;
    }

    public function retrieveFromStage(): array
    {
        $res = $this->db->queryF(
            "SELECT * FROM $this->table_mail_saved WHERE user_id = %s",
            ['integer'],
            [$this->user_id]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));
        if (!is_array($this->mail_data)) {
            $this->persistToStage($this->user_id, [], '', '', '', '', '', false);
        }

        return $this->mail_data;
    }

    /**
     * Should be used to enqueue a 'mail'. A validation is executed before, errors are returned
     * @param list<string> $a_attachment
     * @return list<ilMailError>
     */
    public function enqueue(
        string $a_rcp_to,
        string $a_rcp_cc,
        string $a_rcp_bcc,
        string $a_m_subject,
        string $a_m_message,
        array $a_attachment,
        bool $a_use_placeholders = false
    ): array {
        global $DIC;

        $this->logger->info(
            'New mail system task:' .
            ' To: ' . $a_rcp_to .
            ' | CC: ' . $a_rcp_cc .
            ' | BCC: ' . $a_rcp_bcc .
            ' | Subject: ' . $a_m_subject .
            ' | Attachments: ' . print_r($a_attachment, true)
        );

        if ($a_attachment && !$this->mail_file_data->checkFilesExist($a_attachment)) {
            return [new ilMailError('mail_attachment_file_not_exist', [implode(', ', $a_attachment)])];
        }

        $errors = $this->checkMail($a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_m_subject);
        if ($errors !== []) {
            return $errors;
        }

        $errors = $this->validateRecipients($a_rcp_to, $a_rcp_cc, $a_rcp_bcc);
        if ($errors !== []) {
            return $errors;
        }

        $rcp_to = $a_rcp_to;
        $rcp_cc = $a_rcp_cc;
        $rcp_bcc = $a_rcp_bcc;

        $number_of_external_addresses = $this->getCountRecipients($rcp_to, $rcp_cc, $rcp_bcc);
        if (
            $number_of_external_addresses > 0 &&
            !$this->isSystemMail() &&
            !$DIC->rbac()->system()->checkAccessOfUser($this->user_id, 'smtp_mail', $this->mail_obj_ref_id)
        ) {
            return [new ilMailError('mail_no_permissions_write_smtp')];
        }

        if ($this->appendInstallationSignature()) {
            $a_m_message .= self::_getInstallationSignature();
        }

        if (ilContext::getType() === ilContext::CONTEXT_CRON) {
            $mail_data = new MailDeliveryData(
                $rcp_to,
                $rcp_cc,
                $rcp_bcc,
                $a_m_subject,
                $a_m_message,
                $a_attachment,
                $a_use_placeholders
            );
            return $this->sendMail($mail_data);
        }

        $task_factory = $DIC->backgroundTasks()->taskFactory();
        $task_manager = $DIC->backgroundTasks()->taskManager();

        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);

        $task = $task_factory->createTask(ilMailDeliveryJob::class, [
            $this->user_id,
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            $a_m_subject,
            $a_m_message,
            serialize($a_attachment),
            $a_use_placeholders,
            $this->getSaveInSentbox(),
            (string) $this->context_id,
            serialize(array_merge(
                $this->context_parameters,
                [
                    'auto_responder' => $this->auto_responder_service->isAutoresponderEnabled()
                ]
            ))
        ]);
        $interaction = $task_factory->createTask(ilMailDeliveryJobUserInteraction::class, [
            $task,
            $this->user_id,
        ]);

        $bucket->setTask($interaction);
        $bucket->setTitle($this->lng->txt('mail_bg_task_title'));
        $bucket->setDescription(sprintf($this->lng->txt('mail_bg_task_desc'), $a_m_subject));

        $this->logger->info('Delegated delivery to background task');
        $task_manager->run($bucket);

        return [];
    }

    /**
     * This method is used to finally send internal messages and external emails
     * To use the mail system as a consumer, please use ilMail::enqueue
     * @param list<string> $attachments
     * @return list<ilMailError>
     * @see ilMail::enqueue()
     * @internal
     */
    public function sendMail(
        MailDeliveryData $mail_data
    ): array {
        $internal_message_id = $this->saveInSentbox(
            $mail_data->getAttachments(),
            $mail_data->getTo(),
            $mail_data->getCc(),
            $mail_data->getBcc(),
            $mail_data->getSubject(),
            $mail_data->getMessage()
        );
        $mail_data = $mail_data->withInternalMailId($internal_message_id);

        if ($mail_data->getAttachments() !== []) {
            $this->mail_file_data->assignAttachmentsToDirectory($internal_message_id, $internal_message_id);
            $this->mail_file_data->saveFiles($internal_message_id, $mail_data->getAttachments());
        }

        $num_external_email_addresses = $this->getCountRecipients(
            $mail_data->getTo(),
            $mail_data->getCc(),
            $mail_data->getBcc()
        );

        if ($num_external_email_addresses > 0) {
            $external_mail_recipients_to = $this->getEmailRecipients($mail_data->getTo());
            $external_mail_recipients_cc = $this->getEmailRecipients($mail_data->getCc());
            $external_eail_recipients_bcc = $this->getEmailRecipients($mail_data->getBcc());

            $this->logger->debug(
                'Parsed external email addresses from given recipients /' .
                ' To: ' . $external_mail_recipients_to .
                ' | CC: ' . $external_mail_recipients_cc .
                ' | BCC: ' . $external_eail_recipients_bcc .
                ' | Subject: ' . $mail_data->getSubject()
            );

            $this->sendMimeMail(
                $external_mail_recipients_to,
                $external_mail_recipients_cc,
                $external_eail_recipients_bcc,
                $mail_data->getSubject(),
                $mail_data->isUsePlaceholder() ?
                    $this->replacePlaceholders($mail_data->getMessage(), 0) :
                    $mail_data->getMessage(),
                $mail_data->getAttachments()
            );
        } else {
            $this->logger->debug('No external email addresses given in recipient string');
        }

        $errors = [];
        if (!$this->distributeMail($mail_data)) {
            $errors['mail_send_error'] = new ilMailError('mail_send_error');
        }

        if (!$this->getSaveInSentbox()) {
            $this->deleteMails([$internal_message_id]);
        }

        if ($this->isSystemMail()) {
            $random = new Random\Randomizer();
            if ($random->getInt(0, 50) === 2) {
                (new ilMailAttachmentStageCleanup(
                    $this->logger,
                    $this->mail_file_data
                ))->run();
            }
        }

        return array_values($errors);
    }

    /**
     * @return list<ilMailError>
     */
    public function validateRecipients(string $to, string $cc, string $bcc): array
    {
        try {
            $errors = [];
            $errors = array_merge($errors, $this->checkRecipients($to));
            $errors = array_merge($errors, $this->checkRecipients($cc));
            $errors = array_merge($errors, $this->checkRecipients($bcc));

            if ($errors !== []) {
                return array_merge([new ilMailError('mail_following_rcp_not_valid')], $errors);
            }
        } catch (ilMailException $e) {
            return [new ilMailError('mail_generic_rcp_error', [$e->getMessage()])];
        }

        return [];
    }

    private function getSubjectSentFolderId(): int
    {
        $send_folder_id = 0;
        if (!$this->isSystemMail()) {
            $send_folder_id = $this->mailbox->getSentFolder();
        }

        return $send_folder_id;
    }

    /**
     * @param list<string> $attachment
     */
    private function saveInSentbox(
        array $attachment,
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message
    ): int {
        return $this->sendInternalMail(
            $this->getSubjectSentFolderId(),
            $this->user_id,
            $attachment,
            $to,
            $cc,
            $bcc,
            'read',
            $subject,
            $message,
            $this->user_id
        );
    }

    /**
     * @param list<string> $attachments
     */
    private function sendMimeMail(
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message,
        array $attachments
    ): void {
        $mailer = new ilMimeMail();
        $mailer->From($this->sender_factory->getSenderByUsrId($this->user_id));
        $mailer->To($to);
        $mailer->Subject(
            $subject,
            true,
            (string) ($this->context_parameters[self::PROP_CONTEXT_SUBJECT_PREFIX] ?? '')
        );

        if (!$this->isSystemMail()) {
            $message .= $this->signature_service->user($this->user_id);
        }
        $mailer->Body($message);

        if ($cc !== '') {
            $mailer->Cc($cc);
        }

        if ($bcc !== '') {
            $mailer->Bcc($bcc);
        }


        foreach ($attachments as $attachment) {
            $mailer->Attach(
                $this->mail_file_data->getAbsoluteAttachmentPoolPathByFilename($attachment),
                '',
                'inline',
                $attachment
            );
        }

        $mailer->Send();
    }

    /**
     * @param list<string> $attachments
     */
    public function saveAttachments(array $attachments): void
    {
        $this->db->update(
            $this->table_mail_saved,
            [
                'attachments' => ['clob', serialize($attachments)],
            ],
            [
                'user_id' => ['integer', $this->user_id],
            ]
        );
    }

    /**
     * Explode recipient string, allowed separators are ',' ';' ' '
     * @return list<ilMailAddress>
     */
    private function parseAddresses(string $addresses): array
    {
        if ($addresses !== '') {
            $this->logger->debug(sprintf(
                'Started parsing of recipient string: %s',
                $addresses
            ));
        }

        $parser = $this->mail_address_parser_factory->getParser($addresses);
        $parsed_addresses = $parser->parse();

        if ($addresses !== '') {
            $this->logger->debug(sprintf(
                'Parsed addresses: %s',
                implode(',', array_map(static fn(ilMailAddress $address): string => (string) $address, $parsed_addresses))
            ));
        }

        return $parsed_addresses;
    }

    private function getCountRecipient(string $recipients, bool $only_external_addresses = true): int
    {
        $addresses = new ilMailAddressListImpl($this->parseAddresses($recipients));
        if ($only_external_addresses) {
            $addresses = new ilMailOnlyExternalAddressList(
                $addresses,
                self::ILIAS_HOST,
                $this->usr_id_by_login_callable
            );
        }

        return count($addresses->value());
    }

    private function getCountRecipients(
        string $to_recipients,
        string $cc_recipients,
        string $bcc_recipients,
        bool $only_external_addresses = true
    ): int {
        return (
            $this->getCountRecipient($to_recipients, $only_external_addresses) +
            $this->getCountRecipient($cc_recipients, $only_external_addresses) +
            $this->getCountRecipient($bcc_recipients, $only_external_addresses)
        );
    }

    private function getEmailRecipients(string $recipients): string
    {
        $addresses = new ilMailOnlyExternalAddressList(
            new ilMailAddressListImpl($this->parseAddresses($recipients)),
            self::ILIAS_HOST,
            $this->usr_id_by_login_callable
        );

        $email_recipients = array_map(static fn(ilMailAddress $address): string => (string) $address, $addresses->value());

        return implode(',', $email_recipients);
    }

    public static function _getAutoGeneratedMessageString(?ilLanguage $lang = null): string
    {
        global $DIC;

        if (!($lang instanceof ilLanguage)) {
            $lang = ilLanguageFactory::_getLanguage();
        }

        $lang->loadLanguageModule('mail');

        return sprintf(
            $lang->txt('mail_auto_generated_info'),
            $DIC->settings()->get('inst_name', 'ILIAS ' . ((int) ILIAS_VERSION_NUMERIC)),
            ilUtil::_getHttpPath()
        ) . "\n\n";
    }

    public static function _getIliasMailerName(): string
    {
        global $DIC;
        $sender_factory = $DIC->mail()->mime()->senderFactory();

        return $sender_factory->system()->getFromName();
    }

    /**
     * @return self|bool
     */
    public function appendInstallationSignature(?bool $a_flag = null)
    {
        if ($a_flag === null) {
            return $this->append_installation_signature;
        }

        $this->append_installation_signature = $a_flag;
        return $this;
    }

    public static function _getInstallationSignature(): string
    {
        global $DIC;
        return $DIC->mail()->signature()->installation();
    }

    public static function getSalutation(int $a_usr_id, ?ilLanguage $a_language = null): string
    {
        global $DIC;

        $lang = ($a_language instanceof ilLanguage) ? $a_language : $DIC->language();
        $lang->loadLanguageModule('mail');

        $gender = ilObjUser::_lookupGender($a_usr_id);
        $gender = $gender ?: 'n';
        $name = ilObjUser::_lookupName($a_usr_id);

        if ($name['firstname'] === '') {
            return $lang->txt('mail_salutation_anonymous') . ',';
        }

        return
            $lang->txt('mail_salutation_' . $gender) . ' ' .
            ($name['title'] ? $name['title'] . ' ' : '') .
            ($name['firstname'] ? $name['firstname'] . ' ' : '') .
            $name['lastname'] . ',';
    }

    private function getUserInstanceById(int $usr_id): ?ilObjUser
    {
        if (!array_key_exists($usr_id, $this->user_instances_by_id_map)) {
            try {
                $user = new ilObjUser($usr_id);
            } catch (Exception) {
                $user = null;
            }

            $this->user_instances_by_id_map[$usr_id] = $user;
        }

        return $this->user_instances_by_id_map[$usr_id];
    }

    /**
     * @param array<int, ilObjUser> $user_instances_by_id_map
     */
    public function setUserInstanceById(array $user_instances_by_id_map): void
    {
        $this->user_instances_by_id_map = $user_instances_by_id_map;
    }

    private function getMailOptionsByUserId(int $usr_id): ilMailOptions
    {
        if (!isset($this->mail_options_by_usr_id_map[$usr_id])) {
            $this->mail_options_by_usr_id_map[$usr_id] = new ilMailOptions($usr_id);
        }

        return $this->mail_options_by_usr_id_map[$usr_id];
    }

    /**
     * @param ilMailOptions[] $mail_options_by_usr_id_map
     */
    public function setMailOptionsByUserIdMap(array $mail_options_by_usr_id_map): void
    {
        $this->mail_options_by_usr_id_map = $mail_options_by_usr_id_map;
    }

    public function formatLinebreakMessage(string $message): string
    {
        return $message;
    }

    private function createRecipient(int $user_id): Recipient
    {
        return new Recipient(
            $user_id,
            $this->getUserInstanceById($user_id),
            $this->getMailOptionsByUserId($user_id),
            $this->legal_documents
        );
    }
}
