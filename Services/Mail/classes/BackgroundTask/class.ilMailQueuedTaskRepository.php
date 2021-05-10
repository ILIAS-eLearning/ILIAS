<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailQueuedTaskRepository
 * @author Michael Jansen
 */
class ilMailQueuedTaskRepository
{
    private const TABLE_NAME = 'mail_task_queue';
    
    /** @var ilDBInterface */
    private $db;
    /** @var \ILIAS\Data\UUID\Factory */
    private $uuidFactory;

    /**
     * ilMailQueuedTaskRepository constructor.
     * @param ilDBInterface $db
     */
    public function __construct(ilDBInterface $db, \ILIAS\Data\UUID\Factory $uuidFactory)
    {
        $this->db = $db;
        $this->uuidFactory = $uuidFactory;
    }

    public function save(ilMailValueObject $mailTask) : \ILIAS\Data\UUID\Uuid
    {
        $uuid = $this->uuidFactory->uuid4();

        $this->db->insert(self::TABLE_NAME, [
            'queue_item_id' => ['text', $uuid->toString()],
            'actor_usr_id' => ['integer', $mailTask->getActorUsrId()],
            'rcp_to' => ['clob', $mailTask->getRecipients()],
            'rcp_cc' => ['clob', $mailTask->getRecipientsCC()],
            'rcp_bcc' => ['clob', $mailTask->getRecipientsBCC()],
            'mail_subject' => ['text', $mailTask->getSubject()],
            'mail_body' => ['clob', $mailTask->getBody()],
            'attachments' => ['blob', serialize($mailTask->getAttachments())],
            'use_placeholders' => ['integer', $mailTask->isUsingPlaceholders()],
            'save_in_sentbox' => ['integer', $mailTask->shouldSaveInSentBox()],
            'tpl_ctx_id' => ['text', $mailTask->getTemplateContextId()],
            'tpl_ctx_params' => ['blob', serialize($mailTask->getTemplateContextParams())],
            'created_ts' => ['integer', time()]
        ]);

        return $uuid;
    }

    public function delete(string $uuidString) : void
    {
        $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE queue_item_id = %s",
            ['text'],
            [$uuidString]
        );
    }

    public function findByUuid(string $uuidString) : ilMailValueObject
    {
        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE queue_item_id = %s",
            ['text'],
            [$uuidString]
        );
        if (1 !== (int) $this->db->numRows($result)) {
            throw new ilCouldNotFindQueueTaskException("Could not find mail task by uuid $uuidString");
        }

        $row = $this->db->fetchAssoc($result);

        $mailObject = new ilMailValueObject(
            (string) $row['rcp_to'],
            (string) $row['rcp_cc'],
            (string) $row['rcp_bcc'],
            (string) $row['mail_subject'],
            (string) $row['mail_body'],
            (array) unserialize(
                $row['attachments'],
                ['allowed_classes' => false]
            ),
            (bool) $row['use_placeholders'],
            (bool) $row['save_in_sentbox'],
        );
        
        if ($row['actor_usr_id']) {
            $mailObject = $mailObject->withActorUsrId((int) $row['actor_usr_id']);
        }

        if ($row['tpl_ctx_id']) {
            $mailObject = $mailObject->withTemplateContextId((string) $row['tpl_ctx_id']);
        }

        if ($row['tpl_ctx_params']) {
            $mailObject = $mailObject->withTemplateContextParams((array) unserialize(
                $row['tpl_ctx_params'],
                ['allowed_classes' => false]
            ));
        }

        return $mailObject;
    }
}
