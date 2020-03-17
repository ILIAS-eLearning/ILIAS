<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOnScreenChatUserDataProvider
 */
class ilOnScreenChatUserDataProvider
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * ilOnScreenChatUserDataProvider constructor.
     * @param \ilDBInterface $db
     * @param \ilObjUser     $user
     */
    public function __construct(\ilDBInterface $db, \ilObjUser $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * @return int[]
     * @throws \ilWACException
     */
    public function getInitialUserProfileData()
    {
        $conversationIds = [];

        $res = $this->db->queryF(
            'SELECT DISTINCT(conversation_id) FROM osc_activity WHERE user_id = %s',
            ['integer'],
            [$this->user->getId()]
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $conversationIds[$row['conversation_id']] = $row['conversation_id'];
        }

        $res = $this->db->queryF(
            'SELECT DISTINCT(conversation_id) FROM osc_messages WHERE user_id = %s',
            ['integer'],
            [$this->user->getId()]
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $conversationIds[$row['conversation_id']] = $row['conversation_id'];
        }

        if (0 === count($conversationIds)) {
            return [];
        }

        $usrIds = [];

        $in = $this->db->in('id', $conversationIds, false, 'text');
        $res = $this->db->query('SELECT DISTINCT(participants) FROM osc_conversation WHERE ' . $in);
        while ($row = $this->db->fetchAssoc($res)) {
            $participants = json_decode($row['participants'], true);

            if (is_array($participants)) {
                $usrIds = array_unique(array_merge($usrIds, array_map(function ($user) {
                    return $user['id'];
                }, $participants)));
            }
        }

        return $this->getDataByUserIds($usrIds);
    }

    /**
     * @param int[] $usrIds
     * @throws ilWACException
     * @return $data
     */
    public function getDataByUserIds(array $usrIds)
    {
        $usrIds = array_filter(array_map('intval', array_map('trim', $usrIds)));

        $oldWacTokenValue = \ilWACSignedPath::getTokenMaxLifetimeInSeconds();
        \ilWACSignedPath::setTokenMaxLifetimeInSeconds(60);

        $publicData = \ilUserUtil::getNamePresentation($usrIds, true, false, '', false, true, false, true);

        $data = [];

        foreach ($usrIds as $usr_id) {
            $publicImage = isset($publicData[$usr_id]) && isset($publicData[$usr_id]['img']) ? $publicData[$usr_id]['img'] : '';

            $publicName = '';
            if (isset($publicData[$usr_id])) {
                $login = '';
                if (isset($publicData[$usr_id]['login'])) {
                    $publicName = $login = $publicData[$usr_id]['login'];
                }

                if (isset($publicData[$usr_id]['public_profile']) && $publicData[$usr_id]['public_profile']) {
                    $publicName = implode(', ', [
                        $publicData[$usr_id]['lastname'],
                        $publicData[$usr_id]['firstname'],
                    ]);

                    if ($publicName !== '') {
                        $publicName .= ' [' . $login . ']';
                    } else {
                        $publicName = $login;
                    }
                }
            }

            $data[$usr_id] = array(
                'public_name' => $publicName,
                'profile_image' => $publicImage
            );
        }

        \ilWACSignedPath::setTokenMaxLifetimeInSeconds($oldWacTokenValue);

        return $data;
    }
}
