<?php
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
    /**
     * @var \ILIAS
     */
    protected $ilias;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilTree
     */
    protected $mtree;

    /**
     * user_id
     * @var integer
     */
    protected $user_id;

    /**
     * actions
     * @var array
     */
    protected $actions = array();

    /**
     * default folders which are created for every new user
     * @var array
     */
    protected $default_folder = array();

    /**
     * table name of table mail object data
     * @var string
     */
    protected $table_mail_obj_data;

    /**
     * table name of tree table
     * @var string
     */
    protected $table_tree;

    /**
     * ilMailbox constructor.
     * @param int $a_user_id
     */
    public function __construct($a_user_id = 0)
    {
        global $DIC;

        $this->ilias = $DIC['ilias'];
        $this->lng = $DIC->language();
        $this->db = $DIC->database();

        $this->user_id = $a_user_id;

        $this->table_mail_obj_data = 'mail_obj_data';
        $this->table_tree = 'mail_tree';

        if ($a_user_id) {
            $this->mtree = new ilTree($this->user_id);
            $this->mtree->setTableNames($this->table_tree, $this->table_mail_obj_data);
        }

        // i added this, becaus if i create a new user automatically during
        // CAS authentication, we have no $lng variable (alex, 16.6.2006)
        // (alternative: make createDefaultFolder call static in ilObjUser->saveAsNew())
        if (is_object($this->lng)) {
            $this->lng->loadLanguageModule("mail");

            $this->actions = array(
                "moveMails" => $this->lng->txt("mail_move_to"),
                "markMailsRead" => $this->lng->txt("mail_mark_read"),
                "markMailsUnread" => $this->lng->txt("mail_mark_unread"),
                "deleteMails" => $this->lng->txt("delete"));
        }
        
        // array contains basic folders and there lng translation for every new user
        $this->default_folder = array(
            "b_inbox" => "inbox",
            "c_trash" => "trash",
            "d_drafts" => "drafts",
            "e_sent" => "sent",
            "z_local" => "local");
    }

    /**
     * get Id of the inbox folder of an user
     */
    public function getInboxFolder()
    {
        $res = $this->db->queryF(
            '
			SELECT obj_id FROM ' . $this->table_mail_obj_data . '
			WHERE user_id = %s
			AND m_type = %s',
            array('integer', 'text'),
            array($this->user_id, 'inbox')
        );

        $row = $this->db->fetchAssoc($res);

        return $row['obj_id'];
    }

    /**
     * get Id of the inbox folder of an user
     */
    public function getDraftsFolder()
    {
        $res = $this->db->queryF(
            '
			SELECT obj_id FROM ' . $this->table_mail_obj_data . '
			WHERE user_id = %s
			AND m_type = %s',
            array('integer', 'text'),
            array($this->user_id, 'drafts')
        );

        $row = $this->db->fetchAssoc($res);

        return $row['obj_id'];
    }

    /**
     * get Id of the trash folder of an user
     */
    public function getTrashFolder()
    {
        $res = $this->db->queryf(
            '
			SELECT obj_id FROM ' . $this->table_mail_obj_data . '
			WHERE user_id = %s
			AND m_type = %s',
            array('integer', 'text'),
            array($this->user_id, 'trash')
        );

        $row = $this->db->fetchAssoc($res);

        return $row['obj_id'];
    }

    /**
     * get Id of the sent folder of an user
     */
    public function getSentFolder()
    {
        $res = $this->db->queryf(
            '
			SELECT obj_id FROM ' . $this->table_mail_obj_data . '
			WHERE user_id = %s
			AND m_type = %s',
            array('integer', 'text'),
            array($this->user_id, 'sent')
        );

        $row = $this->db->fetchAssoc($res);

        return $row['obj_id'];
    }

    /**
     * get Id of the root folder of an user
     */
    public function getRootFolderId()
    {
        return $this->mtree->getRootID($this->user_id);
    }

    /**
    * get all possible actions if no mobj_id is given
    * or folder specific actions if mobj_id is given
    * @param	integer	mobj_id
    * @access	public
    * @return	array	possible actions
    */
    public function getActions($a_mobj_id)
    {
        if ($a_mobj_id) {
            $folder_data = $this->getFolderData($a_mobj_id);

            if ($folder_data["type"] == "user_folder" or $folder_data["type"] == "local") {
                return $this->actions;
            }
        }

        return $this->actions;
    }

    /**
     * Creates all default folders for a user. This method should only be called when a user object is created.
     */
    public function createDefaultFolder()
    {
        $root_id = $this->db->nextId($this->table_mail_obj_data);
        $this->db->manipulateF(
            '
			INSERT INTO ' . $this->table_mail_obj_data . ' 
			(	obj_id,
				user_id,
				title,
				m_type
			)
			VALUES(%s, %s, %s, %s)',
            array('integer','integer', 'text', 'text'),
            array($root_id, $this->user_id, 'a_root', 'root')
        );
        $this->mtree->addTree($this->user_id, $root_id);

        foreach ($this->default_folder as $key => $folder) {
            $last_id = $this->db->nextId($this->table_mail_obj_data);
            $this->db->manipulateF(
                '
				INSERT INTO ' . $this->table_mail_obj_data . ' 
				(	obj_id,
					user_id,
					title,
					m_type
				)
				VALUES(%s, %s, %s, %s)',
                array('integer','integer', 'text', 'text'),
                array($last_id, $this->user_id, $key, $folder)
            );
            $this->mtree->insertNode($last_id, $root_id);
        }
    }

    /**
     * Adds a new mail folder with the passed name under the given parent folder
     * @param  integer $a_parent_id Id of parent folder
     * @param  string  $a_folder_name Name of tje folder to be created
     * @return integer The new id of the created folder
     */
    public function addFolder($a_parent_id, $a_folder_name)
    {
        if ($this->folderNameExists($a_folder_name)) {
            return 0;
        }

        $next_id = $this->db->nextId($this->table_mail_obj_data);
        $this->db->manipulateF(
            '
			INSERT INTO ' . $this->table_mail_obj_data . '
			(	obj_id,
			 	user_id,
				title,
			 	m_type 
			 )
			 VALUES(%s,%s,%s,%s)',
            array('integer','integer', 'text', 'text'),
            array($next_id, $this->user_id, $a_folder_name, 'user_folder')
        );
        $this->mtree->insertNode($next_id, $a_parent_id);

        return $next_id;
    }

    /**
     * Rename a folder and check if the name already exists
     * @param  integer $a_obj_id The id of the folder to be renamed
     * @param  string  $a_new_folder_name The new name of the folder
     * @return boolean
     */
    public function renameFolder($a_obj_id, $a_new_folder_name)
    {
        if ($this->folderNameExists($a_new_folder_name)) {
            return false;
        }

        $this->db->manipulateF(
            '
			UPDATE ' . $this->table_mail_obj_data . '
			SET title = %s
			WHERE obj_id = %s AND user_id = %s',
            array('text', 'integer', 'integer'),
            array($a_new_folder_name, $a_obj_id, $this->user_id)
        );

        return true;
    }

    /**
     * Checks whether or not the passed folder name exists in the context of the folder owner
     * @param string $a_folder_name The new name of folder
     * @return boolean
     */
    protected function folderNameExists($a_folder_name)
    {
        $res = $this->db->queryF(
            '
			SELECT obj_id FROM ' . $this->table_mail_obj_data . '
			WHERE user_id = %s
			AND title = %s',
            array('integer', 'text'),
            array($this->user_id, $a_folder_name)
        );
        $row = $this->db->fetchAssoc($res);

        return is_array($row) && $row['obj_id'] > 0 ? true : false;
    }

    /**
     * @param int $a_folder_id
     * @return bool
     * @throws ilInvalidTreeStructureException
     */
    public function deleteFolder($a_folder_id)
    {
        $query = $this->db->queryf(
            '
			SELECT obj_id, title FROM mail_obj_data
			WHERE obj_id = %s AND user_id = %s',
            array('integer', 'integer'),
            array($a_folder_id, $this->user_id)
        );
        $row = $this->db->fetchAssoc($query);

        if (!is_array($row) || array_key_exists($row['title'], $this->default_folder)) {
            return false;
        }

        require_once 'Services/Mail/classes/class.ilMail.php';
        $umail = new ilMail($this->user_id);

        $subtree = $this->mtree->getSubtree($this->mtree->getNodeData($a_folder_id));
        $this->mtree->deleteTree($this->mtree->getNodeData($a_folder_id));

        foreach ($subtree as $node) {
            $mails = $umail->getMailsOfFolder($node["obj_id"]);
            $mail_ids = array();
            foreach ($mails as $mail) {
                $mail_ids[] = $mail["mail_id"];
            }

            $umail->deleteMails($mail_ids);

            $this->db->manipulateF(
                '
				DELETE FROM ' . $this->table_mail_obj_data . ' 
				WHERE obj_id = %s AND user_id = %s',
                array('integer', 'integer'),
                array($node['obj_id'], $this->user_id)
            );
        }

        return true;
    }

    /**
     * Fetches the data of a specific folder
     * @param integer $a_obj_id
     * @return array
     */
    public function getFolderData($a_obj_id)
    {
        $res = $this->db->queryF(
            '
			SELECT * FROM ' . $this->table_mail_obj_data . ' 
			WHERE user_id = %s
			AND obj_id = %s',
            array('integer', 'integer'),
            array($this->user_id, $a_obj_id)
        );
        $row = $this->db->fetchAssoc($res);

        return array(
            'obj_id' => $row['obj_id'],
            'title' => $row['title'],
            'type' => $row['m_type']
        );
    }

    /**
     * Get id of parent folder
     * @param integer $a_obj_id
     * @return int
     */
    public function getParentFolderId($a_obj_id)
    {
        $res = $this->db->queryF(
            '
			SELECT * FROM  ' . $this->table_tree . ' 
			WHERE child = %s AND tree = %s',
            array('integer', 'integer'),
            array($a_obj_id, $this->user_id)
        );
        $row = $this->db->fetchAssoc($res);

        return is_array($row) ? $row['parent'] : 0;
    }

    /**
     * Get all folders under a given folder/node id
     * @param int $a_folder
     * @param int $a_folder_parent
     * @return array
     */
    public function getSubFolders($a_folder = 0, $a_folder_parent = 0)
    {
        if (!$a_folder) {
            $a_folder = $this->getRootFolderId();
        }

        $user_folder = array();

        foreach ($this->default_folder as $key => $value) {
            $res = $this->db->queryF(
                '
				SELECT obj_id, m_type
				FROM ' . $this->table_mail_obj_data . ' 
				WHERE user_id = %s
				AND title = %s',
                array('integer', 'text'),
                array($this->user_id, $key)
            );
            $row = $this->db->fetchAssoc($res);

            $user_folder[] = array(
                'title' => $key,
                'type' => $row['m_type'],
                'obj_id' => $row['obj_id']
            );
        }

        $res = $this->db->queryF(
            '
			SELECT * FROM ' . $this->table_tree . ', ' . $this->table_mail_obj_data . '
			WHERE ' . $this->table_mail_obj_data . '.obj_id = ' . $this->table_tree . '.child 
			AND ' . $this->table_tree . '.depth  > %s
			AND ' . $this->table_tree . '.tree  = %s
			ORDER BY ' . $this->table_tree . '.lft, ' . $this->table_mail_obj_data . '.title  ',
            array('integer', 'integer'),
            array(2, $this->user_id)
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $user_folder[] = array(
                'title' => $row['title'],
                'type' => $row['m_type'],
                'obj_id' => $row['child']
            );
        }

        return $user_folder;
    }

    /**
     * @param integer $a_user_id
     */
    public function setUserId($a_user_id)
    {
        $this->user_id = $a_user_id;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return (int) $this->user_id;
    }

    /**
    * deletes user's mailbox and all db entries related to mailbox
    * TODO: stefan, bitte nochmal kontrollieren, ob auch wirklich alles gel�scht wird.
    * Vielleicht hab ich was �bersehen. - shofmann, 15.7.03
    * @return	boolean	true on successful deletion
    */
    public function delete()
    {
        $this->db->manipulateF(
            '
			DELETE FROM mail_obj_data WHERE user_id = %s',
            array('integer'),
            array($this->user_id)
        );

        $this->db->manipulateF(
            '
			DELETE FROM mail_options WHERE user_id = %s',
            array('integer'),
            array($this->user_id)
        );

        $this->db->manipulateF(
            '
			DELETE FROM mail_saved WHERE user_id = %s',
            array('integer'),
            array($this->user_id)
        );

        $this->db->manipulateF(
            '
			DELETE FROM mail_tree WHERE tree = %s',
            array('integer'),
            array($this->user_id)
        );

        // Delete the user's files from filesystem: This has to be done before deleting the database entries in table 'mail'
        require_once 'Services/Mail/classes/class.ilFileDataMail.php';
        $fdm = new ilFileDataMail($this->user_id);
        $fdm->onUserDelete();
        
        // Delete mails of deleted user
        $this->db->manipulateF(
            'DELETE FROM mail WHERE user_id = %s',
            array('integer'),
            array($this->user_id)
        );

        return true;
    }

    /**
     * Update existing mails. Set sender id to 0 and import name to login name.
     * This is only necessary for deleted users.
     * @param string $nameToShow
     */
    public function updateMailsOfDeletedUser($nameToShow)
    {
        $this->db->manipulateF(
            '
			UPDATE mail 
			SET sender_id = %s, import_name = %s
			WHERE sender_id = %s',
            array('integer', 'text', 'integer'),
            array(0, $nameToShow, $this->user_id)
        );
    }

    /**
     * @param int $folderId
     * @return bool
     */
    public function isOwnedFolder(int $folderId) : bool
    {
        $folderData = $this->getFolderData($folderId);

        return $folderData['obj_id'] == $folderId;
    }
}
