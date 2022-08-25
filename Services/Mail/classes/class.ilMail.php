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

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;

/**
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMail
{
    public const ILIAS_HOST = 'ilias';
    public const PROP_CONTEXT_SUBJECT_PREFIX = 'subject_prefix';
    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected ilFileDataMail $mfile;
    protected ilMailOptions $mail_options;
    protected ilMailbox $mailbox;
    public int $user_id;
    protected string $table_mail;
    protected string $table_mail_saved;
    /** @var string[]|null */
    protected ?array $mail_data = [];
    protected ?int $mail_obj_ref_id = null;
    protected bool $save_in_sentbox;
    protected bool $appendInstallationSignature = false;
    private ilAppEventHandler $eventHandler;
    private ilMailAddressTypeFactory $mailAddressTypeFactory;
    private ilMailRfc822AddressParserFactory $mailAddressParserFactory;
    protected ?string $contextId = null;
    protected array $contextParameters = [];
    protected ilLogger $logger;
    /** @var array<int, ilMailOptions> */
    protected array $mailOptionsByUsrIdMap = [];
    /** @var array<int, ilObjUser> */
    protected array $userInstancesByIdMap = [];
    protected $usrIdByLoginCallable;
    protected int $maxRecipientCharacterLength = 998;
    protected ilMailMimeSenderFactory $senderFactory;
    protected ilObjUser $actor;

    public function __construct(
        int $a_user_id,
        ilMailAddressTypeFactory $mailAddressTypeFactory = null,
        ilMailRfc822AddressParserFactory $mailAddressParserFactory = null,
        ilAppEventHandler $eventHandler = null,
        ilLogger $logger = null,
        ilDBInterface $db = null,
        ilLanguage $lng = null,
        ilFileDataMail $mailFileData = null,
        ilMailOptions $mailOptions = null,
        ilMailbox $mailBox = null,
        ilMailMimeSenderFactory $senderFactory = null,
        callable $usrIdByLoginCallable = null,
        int $mailAdminNodeRefId = null,
        ilObjUser $actor = null
    ) {
        global $DIC;
        $this->logger = $logger ?? ilLoggerFactory::getLogger('mail');
        $this->mailAddressTypeFactory = $mailAddressTypeFactory ?? new ilMailAddressTypeFactory(null, $logger);
        $this->mailAddressParserFactory = $mailAddressParserFactory ?? new ilMailRfc822AddressParserFactory();
        $this->eventHandler = $eventHandler ?? $DIC->event();
        $this->db = $db ?? $DIC->database();
        $this->lng = $lng ?? $DIC->language();
        $this->actor = $actor ?? $DIC->user();
        $this->mfile = $mailFileData ?? new ilFileDataMail($a_user_id);
        $this->mail_options = $mailOptions ?? new ilMailOptions($a_user_id);
        $this->mailbox = $mailBox ?? new ilMailbox($a_user_id);
        $this->senderFactory = $senderFactory ?? $GLOBALS["DIC"]["mail.mime.sender.factory"];
        $this->usrIdByLoginCallable = $usrIdByLoginCallable ?? static function (string $login): int {
            return (int) ilObjUser::_lookupId($login);
        };
        $this->user_id = $a_user_id;
        $this->mail_obj_ref_id = $mailAdminNodeRefId;
        if (null === $this->mail_obj_ref_id) {
            $this->readMailObjectReferenceId();
        }
        $this->lng->loadLanguageModule('mail');
        $this->table_mail = 'mail';
        $this->table_mail_saved = 'mail_saved';
        $this->setSaveInSentbox(false);
    }

    public function withContextId(string $contextId): self
    {
        $clone = clone $this;

        $clone->contextId = $contextId;

        return $clone;
    }

    public function withContextParameters(array $parameters): self
    {
        $clone = clone $this;

        $clone->contextParameters = $parameters;

        return $clone;
    }

    protected function isSystemMail(): bool
    {
        return $this->user_id === ANONYMOUS_USER_ID;
    }

    public function existsRecipient(string $newRecipient, string $existingRecipients): bool
    {
        $newAddresses = new ilMailAddressListImpl($this->parseAddresses($newRecipient));
        $addresses = new ilMailAddressListImpl($this->parseAddresses($existingRecipients));

        $list = new ilMailDiffAddressList($newAddresses, $addresses);

        $diffedAddresses = $list->value();

        return count($diffedAddresses) === 0;
    }

    public function setSaveInSentbox(bool $saveInSentbox): void
    {
        $this->save_in_sentbox = $saveInSentbox;
    }

    public function getSaveInSentbox(): bool
    {
        return $this->save_in_sentbox;
    }

    protected function readMailObjectReferenceId(): void
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
            $usrId = ilObjUser::_lookupId($recipient);
            if (is_int($usrId) && $usrId > 0) {
                $pp = ilObjUser::_lookupPref($usrId, 'public_profile');
                if ($pp === 'g' || ($pp === 'y' && !$this->actor->isAnonymous())) {
                    $user = $this->getUserInstanceById($usrId);
                    $names[] = $user->getFullname() . ' [' . $recipient . ']';
                    continue;
                }
            }

            $names[] = $recipient;
        }

        return implode(', ', $names);
    }

    public function getPreviousMail(int $mailId): ?array
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
            [$this->user_id, $mailId]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    public function getNextMail(int $mailId): ?array
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
            [$this->user_id, $mailId]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    public function getMailsOfFolder(int $a_folder_id, array $filter = []): array
    {
        $mails = [];

        $query =
            "SELECT sender_id, m_subject, mail_id, m_status, send_time " .
            "FROM $this->table_mail " .
            "LEFT JOIN object_data ON obj_id = sender_id " .
            "WHERE user_id = %s AND folder_id = %s " .
            "AND ((sender_id > 0 AND sender_id IS NOT NULL AND obj_id IS NOT NULL) " .
            "OR (sender_id = 0 OR sender_id IS NULL))";

        if (isset($filter['status']) && $filter['status'] !== '') {
            $query .= ' AND m_status = ' . $this->db->quote($filter['status'], 'text');
        }

        $query .= " ORDER BY send_time DESC";

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

    public function countMailsOfFolder(int $folderId): int
    {
        $res = $this->db->queryF(
            "SELECT COUNT(*) FROM $this->table_mail WHERE user_id = %s AND folder_id = %s",
            ['integer', 'integer'],
            [$this->user_id, $folderId]
        );

        return $this->db->numRows($res);
    }

    public function deleteMailsOfFolder(int $folderId): void
    {
        $mails = $this->getMailsOfFolder($folderId);
        foreach ($mails as $mail_data) {
            $this->deleteMails([$mail_data['mail_id']]);
        }
    }

    public function getMail(int $mailId): ?array
    {
        $res = $this->db->queryF(
            "SELECT * FROM $this->table_mail WHERE user_id = %s AND mail_id = %s",
            ['integer', 'integer'],
            [$this->user_id, $mailId]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    /**
     * @param int[] $mailIds
     */
    public function markRead(array $mailIds): void
    {
        $values = [];
        $types = [];

        $query = "UPDATE $this->table_mail SET m_status = %s WHERE user_id = %s ";
        array_push($types, 'text', 'integer');
        array_push($values, 'read', $this->user_id);

        if (count($mailIds) > 0) {
            $query .= ' AND ' . $this->db->in('mail_id', $mailIds, false, 'integer');
        }

        $this->db->manipulateF($query, $types, $values);
    }

    /**
     * @param int[] $mailIds
     */
    public function markUnread(array $mailIds): void
    {
        $values = [];
        $types = [];

        $query = "UPDATE $this->table_mail SET m_status = %s WHERE user_id = %s ";
        array_push($types, 'text', 'integer');
        array_push($values, 'unread', $this->user_id);

        if (count($mailIds) > 0) {
            $query .= ' AND ' . $this->db->in('mail_id', $mailIds, false, 'integer');
        }

        $this->db->manipulateF($query, $types, $values);
    }

    /**
     * @param int[] $mailIds
     * @param int $folderId
     * @return bool
     */
    public function moveMailsToFolder(array $mailIds, int $folderId): bool
    {
        $values = [];
        $types = [];

        $mailIds = array_filter(array_map('intval', $mailIds));

        if (0 === count($mailIds)) {
            return false;
        }

        $query =
            "UPDATE $this->table_mail " .
            "INNER JOIN mail_obj_data " .
            "ON mail_obj_data.obj_id = %s AND mail_obj_data.user_id = %s " .
            "SET $this->table_mail.folder_id = mail_obj_data.obj_id " .
            "WHERE $this->table_mail.user_id = %s";
        array_push($types, 'integer', 'integer', 'integer');
        array_push($values, $folderId, $this->user_id, $this->user_id);

        $query .= ' AND ' . $this->db->in('mail_id', $mailIds, false, 'integer');

        $affectedRows = $this->db->manipulateF($query, $types, $values);

        return $affectedRows > 0;
    }

    /**
     * @param int[] $mailIds
     */
    public function deleteMails(array $mailIds): void
    {
        $mailIds = array_filter(array_map('intval', $mailIds));
        foreach ($mailIds as $id) {
            $this->db->manipulateF(
                "DELETE FROM $this->table_mail WHERE user_id = %s AND mail_id = %s",
                ['integer', 'integer'],
                [$this->user_id, $id]
            );
            $this->mfile->deassignAttachmentFromDirectory($id);
        }
    }

    /**
     * @param array|null $row
     * @return array|null
     */
    protected function fetchMailData(?array $row): ?array
    {
        if (!is_array($row) || empty($row)) {
            return null;
        }

        if (isset($row['attachments'])) {
            $row['attachments'] = unserialize(stripslashes($row['attachments']), ['allowed_classes' => false]);
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

        return $row;
    }

    public function getNewDraftId(int $folderId): int
    {
        $nextId = $this->db->nextId($this->table_mail);
        $this->db->insert($this->table_mail, [
            'mail_id' => ['integer', $nextId],
            'user_id' => ['integer', $this->user_id],
            'folder_id' => ['integer', $folderId],
            'sender_id' => ['integer', $this->user_id],
        ]);

        return $nextId;
    }

    /**
     * @param int $a_folder_id
     * @param string[] $a_attachments
     * @param string $a_rcp_to
     * @param string $a_rcp_cc
     * @param string $a_rcp_bcc
     * @param string $a_m_subject
     * @param string $a_m_message
     * @param int $a_draft_id
     * @param bool $a_use_placeholders
     * @param string|null $a_tpl_context_id
     * @param array $a_tpl_context_params
     * @return int
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
        int $folderId,
        int $senderUsrId,
        array $attachments,
        string $to,
        string $cc,
        string $bcc,
        string $status,
        string $subject,
        string $message,
        int $usrId = 0,
        bool $usePlaceholders = false,
        ?string $templateContextId = null,
        array $templateContextParameters = []
    ): int {
        $usrId = $usrId ?: $this->user_id;

        if ($usePlaceholders) {
            $message = $this->replacePlaceholders($message, $usrId);
        }
        $message = $this->formatLinebreakMessage($message);
        $message = str_ireplace(["<br />", "<br>", "<br/>"], "\n", $message);

        $nextId = $this->db->nextId($this->table_mail);
        $this->db->insert($this->table_mail, [
            'mail_id' => ['integer', $nextId],
            'user_id' => ['integer', $usrId],
            'folder_id' => ['integer', $folderId],
            'sender_id' => ['integer', $senderUsrId],
            'attachments' => ['clob', serialize($attachments)],
            'send_time' => ['timestamp', date('Y-m-d H:i:s')],
            'rcp_to' => ['clob', $to],
            'rcp_cc' => ['clob', $cc],
            'rcp_bcc' => ['clob', $bcc],
            'm_status' => ['text', $status],
            'm_subject' => ['text', $subject],
            'm_message' => ['clob', $message],
            'tpl_ctx_id' => ['text', $templateContextId],
            'tpl_ctx_params' => ['blob', json_encode($templateContextParameters, JSON_THROW_ON_ERROR)],
        ]);

        $sender_equals_reveiver = $usrId === $this->mailbox->getUsrId();
        $is_sent_folder_of_sender = false;
        if ($sender_equals_reveiver) {
            $current_folder_id = $this->getSubjectSentFolderId();
            $is_sent_folder_of_sender = $folderId === $current_folder_id;
        }

        $raise_event = !$sender_equals_reveiver || !$is_sent_folder_of_sender;

        if ($raise_event) {
            $this->eventHandler->raise('Services/Mail', 'sentInternalMail', [
                'id' => $nextId,
                'subject' => $subject,
                'body' => (string) $message,
                'from_usr_id' => $senderUsrId,
                'to_usr_id' => $usrId,
                'rcp_to' => $to,
                'rcp_cc' => $cc,
                'rcp_bcc' => $bcc,
            ]);
        }

        return $nextId;
    }

    protected function replacePlaceholders(
        string $message,
        int $usrId = 0,
        bool $replaceEmptyPlaceholders = true
    ): string {
        try {
            if ($this->contextId) {
                $context = ilMailTemplateContextService::getTemplateContextById($this->contextId);
            } else {
                $context = new ilMailTemplateGenericContext();
            }

            $user = $usrId > 0 ? $this->getUserInstanceById($usrId) : null;

            $processor = new ilMailTemplatePlaceholderResolver($context, $message);
            $message = $processor->resolve($user, $this->contextParameters, $replaceEmptyPlaceholders);
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' has been called with invalid context.');
        }

        return $message;
    }

    /**
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $message
     * @param string[] $attachments
     * @param int $sentMailId
     * @param bool $usePlaceholders
     * @return bool
     */
    protected function distributeMail(
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message,
        array $attachments,
        int $sentMailId,
        bool $usePlaceholders = false
    ): bool {
        if ($usePlaceholders) {
            $toUsrIds = $this->getUserIds([$to]);
            $this->logger->debug(sprintf(
                "Parsed TO user ids from given recipients for serial letter notification: %s",
                implode(', ', $toUsrIds)
            ));

            $this->sendChanneledMails(
                $to,
                $cc,
                $bcc,
                $toUsrIds,
                $subject,
                $message,
                $attachments,
                $sentMailId,
                true
            );

            $otherUsrIds = $this->getUserIds([$cc, $bcc]);
            $this->logger->debug(sprintf(
                "Parsed CC/BCC user ids from given recipients for serial letter notification: %s",
                implode(', ', $otherUsrIds)
            ));

            $this->sendChanneledMails(
                $to,
                $cc,
                $bcc,
                $otherUsrIds,
                $subject,
                $this->replacePlaceholders($message, 0, false),
                $attachments,
                $sentMailId
            );
        } else {
            $usrIds = $this->getUserIds([$to, $cc, $bcc]);
            $this->logger->debug(sprintf(
                "Parsed TO/CC/BCC user ids from given recipients: %s",
                implode(', ', $usrIds)
            ));

            $this->sendChanneledMails(
                $to,
                $cc,
                $bcc,
                $usrIds,
                $subject,
                $message,
                $attachments,
                $sentMailId
            );
        }

        return true;
    }

    /**
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param int[] $usrIds
     * @param string $subject
     * @param string $message
     * @param string[] $attachments
     * @param int $sentMailId
     * @param bool $usePlaceholders
     */
    protected function sendChanneledMails(
        string $to,
        string $cc,
        string $bcc,
        array $usrIds,
        string $subject,
        string $message,
        array $attachments,
        int $sentMailId,
        bool $usePlaceholders = false
    ): void {
        $usrIdToExternalEmailAddressesMap = [];
        $usrIdToMessageMap = [];

        foreach ($usrIds as $usrId) {
            $user = $this->getUserInstanceById($usrId);
            $mailOptions = $this->getMailOptionsByUserId($user->getId());

            $canReadInternalMails = !$user->hasToAcceptTermsOfService() && $user->checkTimeLimit();

            $individualMessage = $message;
            if ($usePlaceholders) {
                $individualMessage = $this->replacePlaceholders($message, $user->getId());
                $usrIdToMessageMap[$user->getId()] = $individualMessage;
            }

            if ($user->getActive()) {
                $wantsToReceiveExternalEmail = (
                    $mailOptions->getIncomingType() === ilMailOptions::INCOMING_EMAIL ||
                    $mailOptions->getIncomingType() === ilMailOptions::INCOMING_BOTH
                );

                if (!$canReadInternalMails || $wantsToReceiveExternalEmail) {
                    $emailAddresses = $mailOptions->getExternalEmailAddresses();
                    $usrIdToExternalEmailAddressesMap[$user->getId()] = $emailAddresses;

                    if ($mailOptions->getIncomingType() === ilMailOptions::INCOMING_EMAIL) {
                        $this->logger->debug(sprintf(
                            "Recipient with id %s will only receive external emails sent to: %s",
                            $user->getId(),
                            implode(', ', $emailAddresses)
                        ));
                        continue;
                    }

                    $this->logger->debug(sprintf(
                        "Recipient with id %s will additionally receive external emails " .
                        "(because the user wants to receive it externally, or the user cannot access " .
                        "the internal mail system) sent to: %s",
                        $user->getId(),
                        implode(', ', $emailAddresses)
                    ));
                } else {
                    $this->logger->debug(sprintf(
                        "Recipient with id %s is does not want to receive external emails",
                        $user->getId()
                    ));
                }
            } else {
                $this->logger->debug(sprintf(
                    "Recipient with id %s is inactive and will not receive external emails",
                    $user->getId()
                ));
            }

            $mbox = clone $this->mailbox;
            $mbox->setUsrId($user->getId());
            $recipientInboxId = $mbox->getInboxFolder();

            $internalMailId = $this->sendInternalMail(
                $recipientInboxId,
                $this->user_id,
                $attachments,
                $to,
                $cc,
                '',
                'unread',
                $subject,
                $individualMessage,
                $user->getId()
            );

            if (count($attachments) > 0) {
                $this->mfile->assignAttachmentsToDirectory($internalMailId, $sentMailId);
            }
        }

        $this->delegateExternalEmails(
            $subject,
            $message,
            $attachments,
            $usePlaceholders,
            $usrIdToExternalEmailAddressesMap,
            $usrIdToMessageMap
        );
    }

    /**
     * @param string $subject
     * @param string $message
     * @param string[] $attachments
     * @param bool $usePlaceholders
     * @param array<int, string[]> $usrIdToExternalEmailAddressesMap
     * @param array<int, string> $usrIdToMessageMap
     */
    protected function delegateExternalEmails(
        string $subject,
        string $message,
        array $attachments,
        bool $usePlaceholders,
        array $usrIdToExternalEmailAddressesMap,
        array $usrIdToMessageMap
    ): void {
        if (1 === count($usrIdToExternalEmailAddressesMap)) {
            if ($usePlaceholders) {
                $message = array_values($usrIdToMessageMap)[0];
            }

            $usrIdToExternalEmailAddressesMap = array_values($usrIdToExternalEmailAddressesMap);
            $firstAddresses = current($usrIdToExternalEmailAddressesMap);

            $this->sendMimeMail(
                implode(',', $firstAddresses),
                '',
                '',
                $subject,
                $this->formatLinebreakMessage($message),
                $attachments
            );
        } elseif (count($usrIdToExternalEmailAddressesMap) > 1) {
            if ($usePlaceholders) {
                foreach ($usrIdToExternalEmailAddressesMap as $usrId => $addresses) {
                    if (0 === count($addresses)) {
                        continue;
                    }

                    $this->sendMimeMail(
                        implode(',', $addresses),
                        '',
                        '',
                        $subject,
                        $this->formatLinebreakMessage($usrIdToMessageMap[$usrId]),
                        $attachments
                    );
                }
            } else {
                $flattenEmailAddresses = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator(
                    $usrIdToExternalEmailAddressesMap
                )), false);

                $flattenEmailAddresses = array_unique($flattenEmailAddresses);

                // https://mantis.ilias.de/view.php?id=23981 and https://www.ietf.org/rfc/rfc2822.txt
                $remainingAddresses = '';
                foreach ($flattenEmailAddresses as $emailAddress) {
                    $sep = '';
                    if ($remainingAddresses !== '') {
                        $sep = ',';
                    }

                    $recipientsLineLength = ilStr::strLen($remainingAddresses) +
                        ilStr::strLen($sep . $emailAddress);
                    if ($recipientsLineLength >= $this->maxRecipientCharacterLength) {
                        $this->sendMimeMail(
                            '',
                            '',
                            $remainingAddresses,
                            $subject,
                            $this->formatLinebreakMessage($message),
                            $attachments
                        );

                        $remainingAddresses = '';
                        $sep = '';
                    }

                    $remainingAddresses .= ($sep . $emailAddress);
                }

                if ('' !== $remainingAddresses) {
                    $this->sendMimeMail(
                        '',
                        '',
                        $remainingAddresses,
                        $subject,
                        $this->formatLinebreakMessage($message),
                        $attachments
                    );
                }
            }
        }
    }

    /**
     * @param string[] $recipients
     * @return int[]
     */
    protected function getUserIds(array $recipients): array
    {
        $parsed_usr_ids = [];

        $joined_recipients = implode(',', array_filter(array_map('trim', $recipients)));

        $addresses = $this->parseAddresses($joined_recipients);
        foreach ($addresses as $address) {
            $address_type = $this->mailAddressTypeFactory->getByPrefix($address);
            $parsed_usr_ids[] = $address_type->resolve();
        }

        return array_unique(array_merge(...$parsed_usr_ids));
    }

    /**
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @return ilMailError[]
     */
    protected function checkMail(string $to, string $cc, string $bcc, string $subject): array
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

        return $errors;
    }

    /**
     * @param string $recipients
     * @return ilMailError[]
     * @throws ilMailException
     */
    protected function checkRecipients(string $recipients): array
    {
        $errors = [];

        try {
            $addresses = $this->parseAddresses($recipients);
            foreach ($addresses as $address) {
                $address_type = $this->mailAddressTypeFactory->getByPrefix($address);
                if (!$address_type->validate($this->user_id)) {
                    $errors[] = $address_type->getErrors();
                }
            }
        } catch (ilException $e) {
            $colonPosition = strpos($e->getMessage(), ':');
            throw new ilMailException(
                ($colonPosition === false) ?
                    $e->getMessage() :
                    substr($e->getMessage(), $colonPosition + 2)
            );
        }

        return array_merge(...$errors);
    }

    /**
     * @param int $a_user_id
     * @param string[] $a_attachments
     * @param string $a_rcp_to
     * @param string $a_rcp_cc
     * @param string $a_rcp_bcc
     * @param string $a_m_subject
     * @param string $a_m_message
     * @param bool $a_use_placeholders
     * @param string|null $a_tpl_context_id
     * @param array|null $a_tpl_ctx_params
     * @return bool
     */
    public function savePostData(
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

        $this->getSavedData();

        return true;
    }

    public function getSavedData(): ?array
    {
        $res = $this->db->queryF(
            "SELECT * FROM $this->table_mail_saved WHERE user_id = %s",
            ['integer'],
            [$this->user_id]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    /**
     * Should be used to enqueue a 'mail'. A validation is executed before, errors are returned
     * @param string $a_rcp_to
     * @param string $a_rcp_cc
     * @param string $a_rcp_bcc
     * @param string $a_m_subject
     * @param string $a_m_message
     * @param string[] $a_attachment
     * @param bool $a_use_placeholders
     * @return ilMailError[]
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

        $this->logger->debug(
            "New mail system task:" .
            " To: " . $a_rcp_to .
            " | CC: " . $a_rcp_cc .
            " | BCC: " . $a_rcp_bcc .
            " | Subject: " . $a_m_subject
        );

        if ($a_attachment && !$this->mfile->checkFilesExist($a_attachment)) {
            return [new ilMailError('mail_attachment_file_not_exist', [$a_attachment])];
        }

        $errors = $this->checkMail($a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_m_subject);
        if (count($errors) > 0) {
            return $errors;
        }

        $errors = $this->validateRecipients($a_rcp_to, $a_rcp_cc, $a_rcp_bcc);
        if (count($errors) > 0) {
            return $errors;
        }

        $rcp_to = $a_rcp_to;
        $rcp_cc = $a_rcp_cc;
        $rcp_bcc = $a_rcp_bcc;

        $numberOfExternalAddresses = $this->getCountRecipients($rcp_to, $rcp_cc, $rcp_bcc);
        if (
            $numberOfExternalAddresses > 0 &&
            !$this->isSystemMail() &&
            !$DIC->rbac()->system()->checkAccessOfUser($this->user_id, 'smtp_mail', $this->mail_obj_ref_id)
        ) {
            return [new ilMailError('mail_no_permissions_write_smtp')];
        }

        if ($this->appendInstallationSignature()) {
            $a_m_message .= self::_getInstallationSignature();
        }

        if (ilContext::getType() === ilContext::CONTEXT_CRON) {
            return $this->sendMail(
                $rcp_to,
                $rcp_cc,
                $rcp_bcc,
                $a_m_subject,
                $a_m_message,
                $a_attachment,
                $a_use_placeholders
            );
        }

        $taskFactory = $DIC->backgroundTasks()->taskFactory();
        $taskManager = $DIC->backgroundTasks()->taskManager();

        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);

        $task = $taskFactory->createTask(ilMailDeliveryJob::class, [
            $this->user_id,
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            $a_m_subject,
            $a_m_message,
            serialize($a_attachment),
            $a_use_placeholders,
            $this->getSaveInSentbox(),
            (string) $this->contextId,
            serialize($this->contextParameters),
        ]);
        $interaction = $taskFactory->createTask(ilMailDeliveryJobUserInteraction::class, [
            $task,
            $this->user_id,
        ]);

        $bucket->setTask($interaction);
        $bucket->setTitle($this->lng->txt('mail_bg_task_title'));
        $bucket->setDescription(sprintf($this->lng->txt('mail_bg_task_desc'), $a_m_subject));

        $this->logger->info('Delegated delivery to background task');
        $taskManager->run($bucket);

        return [];
    }

    /**
     * This method is used to finally send internal messages and external emails
     * To use the mail system as a consumer, please use ilMail::enqueue
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $message
     * @param string[] $attachments
     * @param bool $usePlaceholders
     * @return ilMailError[]
     * @see ilMail::enqueue()
     * @internal
     */
    public function sendMail(
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message,
        array $attachments,
        bool $usePlaceholders
    ): array {
        $internalMessageId = $this->saveInSentbox(
            $attachments,
            $to,
            $cc,
            $bcc,
            $subject,
            $message
        );

        if (count($attachments) > 0) {
            $this->mfile->assignAttachmentsToDirectory($internalMessageId, $internalMessageId);
            $this->mfile->saveFiles($internalMessageId, $attachments);
        }

        $numberOfExternalAddresses = $this->getCountRecipients($to, $cc, $bcc);

        if ($numberOfExternalAddresses > 0) {
            $externalMailRecipientsTo = $this->getEmailRecipients($to);
            $externalMailRecipientsCc = $this->getEmailRecipients($cc);
            $externalMailRecipientsBcc = $this->getEmailRecipients($bcc);

            $this->logger->debug(
                "Parsed external email addresses from given recipients /" .
                " To: " . $externalMailRecipientsTo .
                " | CC: " . $externalMailRecipientsCc .
                " | BCC: " . $externalMailRecipientsBcc .
                " | Subject: " . $subject
            );

            $this->sendMimeMail(
                $externalMailRecipientsTo,
                $externalMailRecipientsCc,
                $externalMailRecipientsBcc,
                $subject,
                $this->formatLinebreakMessage(
                    $usePlaceholders ?
                        $this->replacePlaceholders($message, 0, false) :
                        $message
                ),
                $attachments
            );
        } else {
            $this->logger->debug('No external email addresses given in recipient string');
        }

        $errors = [];

        if (!$this->distributeMail(
            $to,
            $cc,
            $bcc,
            $subject,
            $message,
            $attachments,
            $internalMessageId,
            $usePlaceholders
        )) {
            $errors['mail_send_error'] = new ilMailError('mail_send_error');
        }

        if (!$this->getSaveInSentbox()) {
            $this->deleteMails([$internalMessageId]);
        }

        return array_values($errors);
    }

    /**
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @return ilMailError[]
     */
    public function validateRecipients(string $to, string $cc, string $bcc): array
    {
        try {
            $errors = [];
            $errors = array_merge($errors, $this->checkRecipients($to));
            $errors = array_merge($errors, $this->checkRecipients($cc));
            $errors = array_merge($errors, $this->checkRecipients($bcc));

            if (count($errors) > 0) {
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
     * @param string[] $attachment
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $message
     * @return int
     */
    protected function saveInSentbox(
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
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $message
     * @param string[] $attachments
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
        $mailer->From($this->senderFactory->getSenderByUsrId($this->user_id));
        $mailer->To($to);
        $mailer->Subject(
            $subject,
            true,
            (string) ($this->contextParameters[self::PROP_CONTEXT_SUBJECT_PREFIX] ?? '')
        );
        $mailer->Body($message);

        if ($cc) {
            $mailer->Cc($cc);
        }

        if ($bcc) {
            $mailer->Bcc($bcc);
        }

        foreach ($attachments as $attachment) {
            $mailer->Attach(
                $this->mfile->getAbsoluteAttachmentPoolPathByFilename($attachment),
                '',
                'inline',
                $attachment
            );
        }

        $mailer->Send();
    }

    /**
     * @param string[] $attachments
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
     * @param string $addresses
     * @return ilMailAddress[]
     */
    protected function parseAddresses(string $addresses): array
    {
        if ($addresses !== '') {
            $this->logger->debug(sprintf(
                "Started parsing of recipient string: %s",
                $addresses
            ));
        }

        $parser = $this->mailAddressParserFactory->getParser($addresses);
        $parsedAddresses = $parser->parse();

        if ($addresses !== '') {
            $this->logger->debug(sprintf(
                "Parsed addresses: %s",
                implode(',', array_map(static function (ilMailAddress $address): string {
                    return (string) $address;
                }, $parsedAddresses))
            ));
        }

        return $parsedAddresses;
    }

    protected function getCountRecipient(string $recipients, bool $onlyExternalAddresses = true): int
    {
        $addresses = new ilMailAddressListImpl($this->parseAddresses($recipients));
        if ($onlyExternalAddresses) {
            $addresses = new ilMailOnlyExternalAddressList(
                $addresses,
                self::ILIAS_HOST,
                $this->usrIdByLoginCallable
            );
        }

        return count($addresses->value());
    }

    protected function getCountRecipients(
        string $toRecipients,
        string $ccRecipients,
        string $bccRecipients,
        bool $onlyExternalAddresses = true
    ): int {
        return (
            $this->getCountRecipient($toRecipients, $onlyExternalAddresses) +
            $this->getCountRecipient($ccRecipients, $onlyExternalAddresses) +
            $this->getCountRecipient($bccRecipients, $onlyExternalAddresses)
        );
    }

    protected function getEmailRecipients(string $recipients): string
    {
        $addresses = new ilMailOnlyExternalAddressList(
            new ilMailAddressListImpl($this->parseAddresses($recipients)),
            self::ILIAS_HOST,
            $this->usrIdByLoginCallable
        );

        $emailRecipients = array_map(static function (ilMailAddress $address): string {
            return (string) $address;
        }, $addresses->value());

        return implode(',', $emailRecipients);
    }

    public static function _getAutoGeneratedMessageString(ilLanguage $lang = null): string
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
        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

        return $senderFactory->system()->getFromName();
    }

    /**
     * @param bool|null $a_flag
     * @return self|bool
     */
    public function appendInstallationSignature(bool $a_flag = null)
    {
        if (null === $a_flag) {
            return $this->appendInstallationSignature;
        }

        $this->appendInstallationSignature = $a_flag;
        return $this;
    }

    public static function _getInstallationSignature(): string
    {
        global $DIC;

        $signature = $DIC->settings()->get('mail_system_sys_signature', '');

        $clientUrl = ilUtil::_getHttpPath();
        $clientdirs = glob(ILIAS_WEB_DIR . '/*', GLOB_ONLYDIR);
        if (is_array($clientdirs) && count($clientdirs) > 1) {
            $clientUrl .= '/login.php?client_id=' . CLIENT_ID; // #18051
        }

        $signature = str_ireplace(
            '[INSTALLATION_NAME]',
            $DIC['ilClientIniFile']->readVariable('client', 'name'),
            $signature
        );
        $signature = str_ireplace(
            '[INSTALLATION_DESC]',
            $DIC['ilClientIniFile']->readVariable('client', 'description'),
            $signature
        );
        $signature = str_ireplace('[ILIAS_URL]', $clientUrl, $signature);

        if (!preg_match('/^[\n\r]+/', $signature)) {
            $signature = "\n" . $signature;
        }

        return $signature;
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

    protected function getUserInstanceById(int $usrId): ilObjUser
    {
        if (!isset($this->userInstancesByIdMap[$usrId])) {
            $this->userInstancesByIdMap[$usrId] = new ilObjUser($usrId);
        }

        return $this->userInstancesByIdMap[$usrId];
    }

    /**
     * @param array<int, ilObjUser> $userInstanceByIdMap
     */
    public function setUserInstanceById(array $userInstanceByIdMap): void
    {
        $this->userInstancesByIdMap = $userInstanceByIdMap;
    }

    protected function getMailOptionsByUserId(int $usrId): ilMailOptions
    {
        if (!isset($this->mailOptionsByUsrIdMap[$usrId])) {
            $this->mailOptionsByUsrIdMap[$usrId] = new ilMailOptions($usrId);
        }

        return $this->mailOptionsByUsrIdMap[$usrId];
    }

    /**
     * @param ilMailOptions[] $mailOptionsByUsrIdMap
     */
    public function setMailOptionsByUserIdMap(array $mailOptionsByUsrIdMap): void
    {
        $this->mailOptionsByUsrIdMap = $mailOptionsByUsrIdMap;
    }

    public function formatLinebreakMessage(string $message): string
    {
        return $message;
    }
}
