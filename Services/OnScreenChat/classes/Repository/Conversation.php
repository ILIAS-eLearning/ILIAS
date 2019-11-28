<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\Repository;

use ILIAS\OnScreenChat\DTO\ConversationDto;
use ILIAS\OnScreenChat\DTO\MessageDto;

/**
 * Class Conversation
 * @package ILIAS\OnScreenChat\DTO
 */
class Conversation
{
    /** @var \ilDBInterface */
    private $db;
    /** @var \ilObjUser */
    protected $user;

    /**
     * Conversation constructor.
     * @param \ilDBInterface $db
     * @param \ilObjUser $user
     */
    public function __construct(\ilDBInterface $db, \ilObjUser $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * @param string[] $conversationIds
     * @return ConversationDto[]
     */
    public function findByIds(array $conversationIds) : array
    {
        $conversations = [];

        $res = $this->db->query(
            'SELECT * FROM osc_conversation WHERE ' . $this->db->in(
                'id', $conversationIds, false, 'text'
            )
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $participants = json_decode($row['participants'], true);
            $participantIds = array_filter(array_map(function ($value) {
                if (is_array($value) && isset($value['id'])) {
                    return (int) $value['id'];
                }

                return 0;
            }, $participants));

            if (!in_array((int) $this->user->getId(), $participantIds)) {
                continue;
            }
            
            $conversation = new ConversationDto($row['id']);
            $conversation->setIsGroup((bool) $row['osc_']);
            $conversation->setSubscriberUsrIds($participantIds);

            $this->db->setLimit(1, 0);
            $query = "
                SELECT osc_messages.*
                FROM osc_messages
                WHERE osc_messages.conversation_id = %s
                AND {$this->db->in(
                    'osc_messages.user_id', $participantIds, false, 'text'
                )}
                ORDER BY osc_messages.timestamp DESC
            ";
            $msgRes = $this->db->queryF($query, ['text'], [$conversation->getId()]);

            // Default case 
            $message = new MessageDto('', $conversation);
            $message->setMessage('');
            $message->setAuthorUsrId((int) $this->user->getId());
            $message->setCreatedTimestamp((int) time() * 1000);
            $conversation->setLastMessage($message);

            while ($msgRow = $this->db->fetchAssoc($msgRes)) {
                $message = new MessageDto($msgRow['id'], $conversation);
                $message->setMessage($msgRow['message']);
                $message->setAuthorUsrId((int) $msgRow['user_id']);
                $message->setCreatedTimestamp((int) $msgRow['timestamp']);
                $conversation->setLastMessage($message);
                break;
            }

            $conversations[] = $conversation;
        }

        return $conversations;
    }
}