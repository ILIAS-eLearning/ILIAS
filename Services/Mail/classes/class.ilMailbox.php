<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Mail Box class
 * Base class for creating and handling mail boxes
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 */
class ilMailbox
{
    /** @var ilLanguage */
    protected $lng;

    /** @var ilDBInterface */
    protected $db;

    /** @var ilTree */
    protected $mtree;

    /** @var int */
    protected $usrId;

    /** @var array */
    protected $actions = [];

    /** @var array */
    protected $defaultFolders = [];

    /** @var string */
    protected $table_mail_obj_data;

    /** @var string */
    protected $table_tree;

    /**
     * ilMailbox constructor.
     * @param int $a_user_id
     */
    public function __construct($a_user_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();

        $this->usrId = (int) $a_user_id;
        $this->table_mail_obj_data = 'mail_obj_data';
        $this->table_tree = 'mail_tree';

        if ($this->usrId) {
            $this->mtree = new ilTree($this->usrId);
            $this->mtree->setTableNames($this->table_tree, $this->table_mail_obj_data);
        }

        // i added this, becaus if i create a new user automatically during
        // CAS authentication, we have no $lng variable (alex, 16.6.2006)
        // (alternative: make createDefaultFolder call static in ilObjUser->saveAsNew())
        if (is_object($this->lng)) {
            $this->lng->loadLanguageModule("mail");

            $this->actions = [
                'moveMails' => $this->lng->txt('mail_move_to'),
                'markMailsRead' => $this->lng->txt('mail_mark_read'),
                'markMailsUnread' => $this->lng->txt('mail_mark_unread'),
                'deleteMails' => $this->lng->txt('delete')
            ];
        }

        // array contains basic folders and there lng translation for every new user
        $this->defaultFolders = [
            'b_inbox' => 'inbox',
            'c_trash' => 'trash',
            'd_drafts' => 'drafts',
            'e_sent' => 'sent',
            'z_local' => 'local'
        ];
    }

    /**
     * @return int
     */
    public function getInboxFolder() : int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usrId, 'inbox']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    /**
     * @return int
     */
    public function getDraftsFolder() : int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usrId, 'drafts']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    /**
     * @return int
     */
    public function getTrashFolder() : int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usrId, 'trash']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    /**
     * @return int
     */
    public function getSentFolder() : int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usrId, 'sent']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    /**
     * @return int
     */
    private function getRootFolderId() : int
    {
        return (int) $this->mtree->getRootId();
    }

    /**
     * @param int $folderId
     * @return array
     */
    public function getActions($folderId) : array
    {
        if ($folderId) {
            $folder_data = $this->getFolderData($folderId);
            if ($folder_data['type'] === 'user_folder' || $folder_data['type'] === 'local') {
                return $this->actions;
            }
        }

        return $this->actions;
    }

    /**
     * Creates all default folders for a user. This method should only be called when a user object is created.
     */
    public function createDefaultFolder() : void
    {
        $rootFolderId = (int) $this->db->nextId($this->table_mail_obj_data);
        $this->db->manipulateF(
            'INSERT INTO ' . $this->table_mail_obj_data . ' (obj_id, user_id, title, m_type) VALUES(%s, %s, %s, %s)',
            ['integer', 'integer', 'text', 'text'],
            [$rootFolderId, $this->usrId, 'a_root', 'root']
        );
        $this->mtree->addTree($this->usrId, $rootFolderId);

        foreach ($this->defaultFolders as $key => $folder) {
            $last_id = $this->db->nextId($this->table_mail_obj_data);
            $this->db->manipulateF(
                'INSERT INTO ' . $this->table_mail_obj_data . ' (obj_id, user_id, title, m_type) VALUES(%s, %s, %s, %s)',
                ['integer', 'integer', 'text', 'text'],
                [$last_id, $this->usrId, $key, $folder]
            );
            $this->mtree->insertNode($last_id, $rootFolderId);
        }
    }

    /**
     * @param int $parentFolderId
     * @param string $name
     * @return int
     */
    public function addFolder(int $parentFolderId, string $name) : int
    {
        if ($this->folderNameExists($name)) {
            return 0;
        }

        $nextId = (int) $this->db->nextId($this->table_mail_obj_data);
        $this->db->manipulateF(
            'INSERT INTO ' . $this->table_mail_obj_data . ' (obj_id, user_id, title, m_type) VALUES(%s,%s,%s,%s)',
            ['integer', 'integer', 'text', 'text'],
            [$nextId, $this->usrId, $name, 'user_folder']
        );
        $this->mtree->insertNode($nextId, $parentFolderId);

        return $nextId;
    }

    /**
     * @param int $folderId
     * @param string $name
     * @return bool
     */
    public function renameFolder(int $folderId, string $name) : bool
    {
        if ($this->folderNameExists($name)) {
            return false;
        }

        $this->db->manipulateF(
            'UPDATE ' . $this->table_mail_obj_data . ' SET title = %s WHERE obj_id = %s AND user_id = %s',
            ['text', 'integer', 'integer'],
            [$name, $folderId, $this->usrId]
        );

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    protected function folderNameExists(string $name) : bool
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND title = %s',
            ['integer', 'text'],
            [$this->usrId, $name]
        );
        $row = $this->db->fetchAssoc($res);

        return is_array($row) && $row['obj_id'] > 0 ? true : false;
    }

    /**
     * @param int $folderId
     * @return bool
     * @throws ilInvalidTreeStructureException
     */
    public function deleteFolder($folderId) : bool
    {
        $query = $this->db->queryF(
            'SELECT obj_id, title FROM ' . $this->table_mail_obj_data . ' WHERE obj_id = %s AND user_id = %s',
            ['integer', 'integer'],
            [$folderId, $this->usrId]
        );
        $row = $this->db->fetchAssoc($query);

        if (!is_array($row) || array_key_exists($row['title'], $this->defaultFolders)) {
            return false;
        }

        $mailer = new ilMail($this->usrId);

        $subtree = $this->mtree->getSubtree($this->mtree->getNodeData($folderId));
        $this->mtree->deleteTree($this->mtree->getNodeData($folderId));

        foreach ($subtree as $node) {
            $nodeId = (int) $node['obj_id'];

            $mails = $mailer->getMailsOfFolder($nodeId);

            $mailIds = [];
            foreach ($mails as $mail) {
                $mailIds[] = $mail['mail_id'];
            }

            $mailer->deleteMails($mailIds);

            $this->db->manipulateF(
                'DELETE FROM ' . $this->table_mail_obj_data . ' WHERE obj_id = %s AND user_id = %s',
                ['integer', 'integer'],
                [$nodeId, $this->usrId]
            );
        }

        return true;
    }

    /**
     * @param $folderId
     * @return array
     */
    public function getFolderData($folderId) : array
    {
        $res = $this->db->queryF(
            'SELECT * FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND obj_id = %s',
            ['integer', 'integer'],
            [$this->usrId, $folderId]
        );
        $row = $this->db->fetchAssoc($res);

        return [
            'obj_id' => (int) $row['obj_id'],
            'title' => (string) $row['title'],
            'type' => (string) $row['m_type'],
        ];
    }

    /**
     * @param int $folderId
     * @return int
     */
    public function getParentFolderId(int $folderId) : int
    {
        $res = $this->db->queryF(
            'SELECT * FROM  ' . $this->table_tree . ' WHERE child = %s AND tree = %s',
            ['integer', 'integer'],
            [$folderId, $this->usrId]
        );
        $row = $this->db->fetchAssoc($res);

        return is_array($row) ? (int) $row['parent'] : 0;
    }

    /**
     * @return array
     */
    public function getSubFolders() : array
    {
        $userFolders = [];

        foreach ($this->defaultFolders as $key => $value) {
            $res = $this->db->queryF(
                'SELECT obj_id, m_type FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND title = %s',
                ['integer', 'text'],
                [$this->usrId, $key]
            );
            $row = $this->db->fetchAssoc($res);

            $userFolders[] = [
                'title' => (string) $key,
                'type' => (string) $row['m_type'],
                'obj_id' => (int) $row['obj_id'],
            ];
        }

        $query = implode(' ', [
            'SELECT * FROM ' . $this->table_tree . ', ' . $this->table_mail_obj_data,
            'WHERE ' . $this->table_mail_obj_data . '.obj_id = ' . $this->table_tree . '.child',
            'AND ' . $this->table_tree . '.depth  > %s',
            'AND ' . $this->table_tree . '.tree  = %s',
            'ORDER BY ' . $this->table_tree . '.lft, ' . $this->table_mail_obj_data . '.title'
        ]);
        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [2, $this->usrId]
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $userFolders[] = [
                'title' => (string) $row['title'],
                'type' => (string) $row['m_type'],
                'obj_id' => (int) $row['child'],
            ];
        }

        return $userFolders;
    }

    /**
     * @param int $usrId
     */
    public function setUsrId(int $usrId) : void
    {
        $this->usrId = $usrId;
    }

    /**
     * @return int
     */
    public function getUsrId() : int
    {
        return (int) $this->usrId;
    }

    /**
     *
     */
    public function delete() : void
    {
        $this->db->manipulateF(
            'DELETE FROM mail_obj_data WHERE user_id = %s',
            ['integer'],
            [$this->usrId]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_options WHERE user_id = %s',
            ['integer'],
            [$this->usrId]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_saved WHERE user_id = %s',
            ['integer'],
            [$this->usrId]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_tree WHERE tree = %s',
            ['integer'],
            [$this->usrId]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_auto_responder WHERE sender_id = %s OR receiver_id = %s',
            ['integer', 'integer'],
            [$this->usrId, $this->usrId]
        );

        // Delete the user's files from filesystem: This has to be done before deleting the database entries in table 'mail'
        $fdm = new ilFileDataMail($this->usrId);
        $fdm->onUserDelete();

        // Delete mails of deleted user
        $this->db->manipulateF(
            'DELETE FROM mail WHERE user_id = %s',
            ['integer'],
            [$this->usrId]
        );
    }

    /**
     * Update existing mails. Set sender id to 0 and import name to login name.
     * This is only necessary for deleted users.
     * @param string $nameToShow
     */
    public function updateMailsOfDeletedUser(string $nameToShow) : void
    {
        $this->db->manipulateF(
            'UPDATE mail SET sender_id = %s, import_name = %s WHERE sender_id = %s',
            ['integer', 'text', 'integer'],
            [0, $nameToShow, $this->usrId]
        );
    }

    /**
     * @param int $folderId
     * @return bool
     */
    public function isOwnedFolder(int $folderId) : bool
    {
        $folderData = $this->getFolderData($folderId);

        return (int) $folderData['obj_id'] === $folderId;
    }
}
