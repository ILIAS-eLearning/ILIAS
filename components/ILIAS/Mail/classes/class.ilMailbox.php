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

use ILIAS\Mail\Folder\MailFolderData;
use ILIAS\Mail\Folder\MailFolderType;

class ilMailbox
{
    private readonly ilLanguage $lng;
    private readonly ilDBInterface $db;
    private readonly ilTree $mtree;

    /** @var array{b_inbox: string, c_trash: string, d_drafts: string, e_sent: string, z_local : string} */
    private array $default_folders = [
        'b_inbox' => 'inbox',
        'c_trash' => 'trash',
        'd_drafts' => 'drafts',
        'e_sent' => 'sent',
        'z_local' => 'local',
    ];
    private readonly string $table_mail_obj_data;
    private readonly string $table_tree;

    public function __construct(protected int $usr_id)
    {
        global $DIC;

        if ($usr_id < 1) {
            throw new InvalidArgumentException('Cannot create mailbox without user id');
        }

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->table_mail_obj_data = 'mail_obj_data';
        $this->table_tree = 'mail_tree';

        $this->mtree = new ilTree($this->usr_id);
        $this->mtree->setTableNames($this->table_tree, $this->table_mail_obj_data);

        $this->lng->loadLanguageModule('mail');
    }

    public function getRooFolder(): int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usr_id, 'root']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    public function getInboxFolder(): int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usr_id, 'inbox']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    public function getDraftsFolder(): int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usr_id, 'drafts']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    public function getTrashFolder(): int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usr_id, 'trash']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    public function getSentFolder(): int
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND m_type = %s',
            ['integer', 'text'],
            [$this->usr_id, 'sent']
        );

        $row = $this->db->fetchAssoc($res);

        return (int) $row['obj_id'];
    }

    /**
     * Creates all default folders for a user. This method should only be called when a user object is created.
     */
    public function createDefaultFolder(): void
    {
        $root_folder_id = $this->db->nextId($this->table_mail_obj_data);
        $this->db->manipulateF(
            'INSERT INTO ' . $this->table_mail_obj_data .
            ' (obj_id, user_id, title, m_type) VALUES(%s, %s, %s, %s)',
            ['integer', 'integer', 'text', 'text'],
            [$root_folder_id, $this->usr_id, 'a_root', 'root']
        );
        $this->mtree->addTree($this->usr_id, $root_folder_id);

        foreach ($this->default_folders as $key => $folder) {
            $last_id = $this->db->nextId($this->table_mail_obj_data);
            $this->db->manipulateF(
                'INSERT INTO ' . $this->table_mail_obj_data .
                ' (obj_id, user_id, title, m_type) VALUES(%s, %s, %s, %s)',
                ['integer', 'integer', 'text', 'text'],
                [$last_id, $this->usr_id, $key, $folder]
            );
            $this->mtree->insertNode($last_id, $root_folder_id);
        }
    }

    public function addFolder(int $parent_folder_id, string $name): int
    {
        if ($this->folderNameExists($name)) {
            return 0;
        }

        $next_id = $this->db->nextId($this->table_mail_obj_data);
        $this->db->manipulateF(
            'INSERT INTO ' . $this->table_mail_obj_data .
            ' (obj_id, user_id, title, m_type) VALUES(%s,%s,%s,%s)',
            ['integer', 'integer', 'text', 'text'],
            [$next_id, $this->usr_id, $name, 'user_folder']
        );
        $this->mtree->insertNode($next_id, $parent_folder_id);

        return $next_id;
    }

    public function renameFolder(int $folder_id, string $name): bool
    {
        if ($this->folderNameExists($name)) {
            return false;
        }

        $this->db->manipulateF(
            'UPDATE ' . $this->table_mail_obj_data . ' SET title = %s WHERE obj_id = %s AND user_id = %s',
            ['text', 'integer', 'integer'],
            [$name, $folder_id, $this->usr_id]
        );

        return true;
    }

    protected function folderNameExists(string $name): bool
    {
        $res = $this->db->queryF(
            'SELECT obj_id FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND title = %s',
            ['integer', 'text'],
            [$this->usr_id, $name]
        );
        $row = $this->db->fetchAssoc($res);

        return is_array($row) && $row['obj_id'] > 0;
    }

    /**
     * @throws ilInvalidTreeStructureException
     */
    public function deleteFolder(int $folder_id): bool
    {
        $query = $this->db->queryF(
            'SELECT obj_id, title FROM ' . $this->table_mail_obj_data . ' WHERE obj_id = %s AND user_id = %s',
            ['integer', 'integer'],
            [$folder_id, $this->usr_id]
        );
        $row = $this->db->fetchAssoc($query);

        if (!is_array($row) || array_key_exists($row['title'], $this->default_folders)) {
            return false;
        }

        $mailer = new ilMail($this->usr_id);

        $subtree = $this->mtree->getSubTree($this->mtree->getNodeData($folder_id));
        $this->mtree->deleteTree($this->mtree->getNodeData($folder_id));

        foreach ($subtree as $node) {
            $node_id = (int) $node['obj_id'];

            $mails = $mailer->getMailsOfFolder($node_id);

            $mail_ids = [];
            foreach ($mails as $mail) {
                $mail_ids[] = (int) $mail['mail_id'];
            }

            $mailer->deleteMails($mail_ids);

            $this->db->manipulateF(
                'DELETE FROM ' . $this->table_mail_obj_data . ' WHERE obj_id = %s AND user_id = %s',
                ['integer', 'integer'],
                [$node_id, $this->usr_id]
            );
        }

        return true;
    }

    public function getFolderData(int $folder_id): ?MailFolderData
    {
        $res = $this->db->queryF(
            'SELECT * FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND obj_id = %s',
            ['integer', 'integer'],
            [$this->usr_id, $folder_id]
        );
        $row = $this->db->fetchAssoc($res);

        if (is_array($row)) {
            return $this->getFolderDataFromRow($row);
        }

        return null;
    }

    private function getFolderDataFromRow(array $row): MailFolderData
    {
        return new MailFolderData(
            (int) $row['obj_id'],
            (int) $row['user_id'],
            MailFolderType::from($row['m_type']),
            (string) ($row['m_type'] === MailFolderType::USER->value
                ? $row['title']
                : $this->lng->txt('mail_' . $row['title']))
        );
    }

    public function getParentFolderId(int $folder_id): int
    {
        $res = $this->db->queryF(
            'SELECT * FROM  ' . $this->table_tree . ' WHERE child = %s AND tree = %s',
            ['integer', 'integer'],
            [$folder_id, $this->usr_id]
        );
        $row = $this->db->fetchAssoc($res);

        return is_array($row) ? (int) $row['parent'] : 0;
    }

    /**
     * @return list<MailFolderData>
     */
    public function getSubFolders(): array
    {
        $user_folders = [];

        foreach (array_keys($this->default_folders) as $key) {
            $res = $this->db->queryF(
                'SELECT * FROM ' . $this->table_mail_obj_data . ' WHERE user_id = %s AND title = %s',
                ['integer', 'text'],
                [$this->usr_id, $key]
            );
            $row = $this->db->fetchAssoc($res);
            if (is_array($row)) {
                $user_folders[] = $this->getFolderDataFromRow($row);
            }
        }

        $query = implode(' ', [
            'SELECT * FROM ' . $this->table_tree . ', ' . $this->table_mail_obj_data,
            'WHERE ' . $this->table_mail_obj_data . '.obj_id = ' . $this->table_tree . '.child',
            'AND ' . $this->table_tree . '.depth  > %s',
            'AND ' . $this->table_tree . '.tree  = %s',
            'ORDER BY ' . $this->table_tree . '.lft, ' . $this->table_mail_obj_data . '.title',
        ]);
        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [2, $this->usr_id]
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $user_folders[] = $this->getFolderDataFromRow($row);
        }

        return $user_folders;
    }

    public function setUsrId(int $usr_id): void
    {
        $this->usr_id = $usr_id;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function delete(): void
    {
        $this->db->manipulateF(
            'DELETE FROM mail_obj_data WHERE user_id = %s',
            ['integer'],
            [$this->usr_id]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_options WHERE user_id = %s',
            ['integer'],
            [$this->usr_id]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_saved WHERE user_id = %s',
            ['integer'],
            [$this->usr_id]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_tree WHERE tree = %s',
            ['integer'],
            [$this->usr_id]
        );

        $this->db->manipulateF(
            'DELETE FROM mail_auto_responder WHERE sender_id = %s OR receiver_id = %s',
            ['integer', 'integer'],
            [$this->usr_id, $this->usr_id]
        );

        // Delete the user's files from filesystem:
        // This has to be done before deleting the database entries in table 'mail'
        $fdm = new ilFileDataMail($this->usr_id);
        $fdm->onUserDelete();

        // Delete mails of deleted user
        $this->db->manipulateF(
            'DELETE FROM mail WHERE user_id = %s',
            ['integer'],
            [$this->usr_id]
        );
    }

    /**
     * Update existing mails. Set sender id to 0 and import name to login name.
     * This is only necessary for deleted users.
     */
    public function updateMailsOfDeletedUser(string $name_to_show): void
    {
        $this->db->manipulateF(
            'UPDATE mail SET sender_id = %s, import_name = %s WHERE sender_id = %s',
            ['integer', 'text', 'integer'],
            [0, $name_to_show, $this->usr_id]
        );
    }

    public function isOwnedFolder(int $folder_id): bool
    {
        $folder_data = $this->getFolderData($folder_id);

        return $folder_data?->getFolderId() === $folder_id;
    }
}
