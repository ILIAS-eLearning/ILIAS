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
require_once("Services/Mail/classes/class.ilMail.php");

class ilMailbox
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;

	/**
	* lng object
	* @var		object language
	* @access	private
	*/
	var $lng;

	/**
	* tree object
	* @var		object tree
	* @access	private
	*/
	var $mtree;

	/**
	* user_id
	* @var		integer	user_id
	* @access	private
	*/
	var $user_id;

	/**
	* actions
	*
	* @var		array contains all possible actions
	* @access	private
	*/	
	var $actions;

	/**
	* default folders which are created for every new user
	* @var		array
	* @access	private
	*/
	var $default_folder;

	/**
	* table name of table mail object data
	* @var		string
	* @access	private
	*/
	var $table_mail_obj_data;

	/**
	* table name of tree table
	* @var		string
	* @access	private
	*/
	var $table_tree;

	/**
	* Constructor
	* @param	integer user_id of mailbox
	* @access	public
	*/
	public function __construct($a_user_id = 0)
	{
		global $ilias,$lng;

		$this->ilias = $ilias;
		$this->lng = $lng;
		$this->user_id = $a_user_id;

		$this->table_mail_obj_data = 'mail_obj_data';
		$this->table_tree = 'mail_tree';

		if ($a_user_id)
		{
			$this->mtree = new ilTree($this->user_id);
			$this->mtree->setTableNames($this->table_tree,$this->table_mail_obj_data);
		}

		// i added this, becaus if i create a new user automatically during
		// CAS authentication, we have no $lng variable (alex, 16.6.2006)
		// (alternative: make createDefaultFolder call static in ilObjUser->saveAsNew())
		if (is_object($this->lng))
		{
			$this->lng->loadLanguageModule("mail");

			$this->actions = array(
				"moveMails"        => $this->lng->txt("mail_move_to"),
				"markMailsRead"   => $this->lng->txt("mail_mark_read"),
				"markMailsUnread" => $this->lng->txt("mail_mark_unread"),
				"deleteMails"      => $this->lng->txt("delete"));
		}
		
		// array contains basic folders and there lng translation for every new user
		$this->default_folder = array(
			"b_inbox"     => "inbox",
			"c_trash"     => "trash",
			"d_drafts"    => "drafts",
			"e_sent"      => "sent",
			"z_local"     => "local");

	}
	/**
	* get Id of the inbox folder of an user
	* @access	public
	*/
	public function getInboxFolder()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT obj_id FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND m_type = %s',
			array('integer', 'text'),
			array($this->user_id, 'inbox')
		);

		$row = $ilDB->fetchAssoc($res);

		return $row['obj_id'];
	}

	/**
	* get Id of the inbox folder of an user
	* @access	public
	*/
	public function getDraftsFolder()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT obj_id FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND m_type = %s',
			array('integer', 'text'),
			array($this->user_id, 'drafts')
		);

		$row = $ilDB->fetchAssoc($res);

		return $row['obj_id'];
	}

	/**
	* get Id of the trash folder of an user
	* @access	public
	*/
	public function getTrashFolder()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT obj_id FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND m_type = %s',
			array('integer', 'text'),
			array($this->user_id, 'trash')
		);

		$row = $ilDB->fetchAssoc($res);

		return $row['obj_id'];
	}

	/**
	* get Id of the sent folder of an user
	* @access	public
	*/
	public function getSentFolder()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT obj_id FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND m_type = %s',
			array('integer', 'text'),
			array($this->user_id, 'sent')
		);

		$row = $ilDB->fetchAssoc($res);

		return $row['obj_id'];
	}

	/**
	* get Id of the root folder of an user
	* @access	public
	*/
	function getRootFolderId()
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
	function getActions($a_mobj_id)
	{
		if ($a_mobj_id)
		{
			$folder_data = $this->getFolderData($a_mobj_id);

			if ($folder_data["type"] == "user_folder" or $folder_data["type"] == "local")
			{
				#return array_merge($this->actions,array("add" => $this->lng->txt("mail_add_subfolder")));
				return $this->actions;
			}
		}

		return $this->actions;
	}

	/**
	 * Static method 
	 * check how many unread mails are in inbox
	 * @access	public
	 * @static
	 * @return	int		number of mails
	 */
	function _countNewMails($a_user_id)
	{
		include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
		return ilMailGlobalServices::getNumberOfNewMailsByUserId($a_user_id);
	}

	/**
	* create all default folders
	* @access	public
	*/
	function createDefaultFolder()
	{
		global $ilDB;

/*		$root_id = $this->getLastInsertId();
		++$root_id;
*/
		$root_id = $ilDB->nextId($this->table_mail_obj_data);
		
		$res = $ilDB->manipulateF('
			INSERT INTO '. $this->table_mail_obj_data .' 
			(	obj_id,
				user_id,
				title,
				m_type
			)
			VALUES( %s, %s, %s, %s)',
			array('integer','integer', 'text', 'text'),
			array($root_id, $this->user_id, 'a_root', 'root'));
		
		$this->mtree->addTree($this->user_id,$root_id);
		
		foreach ($this->default_folder as $key => $folder)
		{
			/*$last_id = $this->getLastInsertId();
			++$last_id;
			*/
			$last_id = $ilDB->nextId($this->table_mail_obj_data);
			$statement = $ilDB->manipulateF('
				INSERT INTO '. $this->table_mail_obj_data .' 
				(	obj_id,
					user_id,
					title,
					m_type
				)
				VALUES( %s, %s, %s, %s)',
				array('integer','integer', 'text', 'text'),
				array($last_id,$this->user_id, $key, $folder));
			
			$this->mtree->insertNode($last_id,$root_id);
		}
	}
	/**
	* add folder
	* @param	integer id of parent folder
	* @param	string name of folder
	* @return	integer new id of folder
	* @access	public
	*/
	function addFolder($a_parent_id,$a_folder_name)
	{
		global $ilDB;

		if ($this->folderNameExists($a_folder_name))
		{
			return 0;
		}
		// ENTRY IN mail_obj_data
		$next_id = $ilDB->nextId($this->table_mail_obj_data);
		$statement = $ilDB->manipulateF('
			INSERT INTO '. $this->table_mail_obj_data .'
			(	obj_id,
			 	user_id,
				title,
			 	m_type 
			 )
			 VALUES(%s,%s,%s,%s)',
			array('integer','integer', 'text', 'text'),
			array($next_id, $this->user_id, $a_folder_name, 'user_folder'));
		
		// ENTRY IN mail_tree
		$this->mtree->insertNode($next_id,$a_parent_id);	
		return $next_id;
	}

	/**
	* rename folder and check if the name already exists
	* @param	integer	id folder
	* @param	string	new name of folder
	* @return	boolean
	* @access	public
	*/
	function renameFolder($a_obj_id, $a_new_folder_name)
	{
		global $ilDB;

		if ($this->folderNameExists($a_new_folder_name))
		{
			return false;
		}
		
		$statement = $ilDB->manipulateF('
			UPDATE '. $this->table_mail_obj_data .'
			SET title = %s
			WHERE obj_id = %s',
			array('text', 'integer'),
			array($a_new_folder_name, $a_obj_id));

		return true;
	}

	/**
	* rename folder and check if the name already exists
	* @param string new name of folder
	* @return boolean
	* @access	public
	*/
	function folderNameExists($a_folder_name)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT obj_id FROM '. $this->table_mail_obj_data .'
			WHERE user_id = %s
			AND title = %s',
			array('integer', 'text'),
			array($this->user_id, $a_folder_name));
	
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			
		return $row->obj_id ? true : false;
	}

	/**
	* add folder
	* @param	integer id of parent folder
	* @access	public
	*/
	function deleteFolder($a_folder_id)
	{
		global $ilDB;

		$query = $ilDB->queryf('
			SELECT title FROM mail_obj_data
			WHERE obj_id = %s',
				array('integer'),
				array($a_folder_id)
		);

		$row = $ilDB->fetchAssoc($query);

		if( array_key_exists($row['title'], $this->default_folder) )
		{
			return false;
		}

		include_once("Services/Mail/classes/class.ilMail.php");
		$umail = new ilMail($this->user_id);

		// SAVE SUBTREE DATA
		$subtree = $this->mtree->getSubtree($this->mtree->getNodeData($a_folder_id));

		// DELETE ENTRY IN TREE
		$this->mtree->deleteTree($this->mtree->getNodeData($a_folder_id));

		// DELETE ENTRY IN mobj_data
		foreach($subtree as $node)
		{
			// DELETE mail(s) of folder(s)
			$mails = $umail->getMailsOfFolder($node["obj_id"]);

			foreach ($mails as $mail)
			{
				$mail_ids[] = $mail["mail_id"];
			}

			if (is_array($mail_ids))
			{
				$umail->deleteMails($mail_ids);
			}

			// DELETE mobj_data entries
			$statement = $ilDB->manipulateF('
				DELETE FROM '. $this->table_mail_obj_data .' 
				WHERE obj_id = %s',
				array('integer'),
				array($node['obj_id']));
		}

		return true;
	}

	// DONE: can be substituted by ilUtil::getLastInsertId
	function getLastInsertId()
	{
		global $ilDB;
		
		return $ilDB->getLastInsertId();
	}
	
	/**
	* get data of a specific folder
	* @param int id of parent folder
	* @access	public
	*/
	function getFolderData($a_obj_id)
	{
		global $ilDB;
	
		$res = $ilDB->queryf('
			SELECT * FROM '. $this->table_mail_obj_data .' 
			WHERE user_id = %s
			AND obj_id = %s',
			array('integer', 'integer'),
			array($this->user_id, $a_obj_id));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return array(
			"obj_id"   => $row->obj_id,
			"title"    => stripslashes($row->title),
			"type"     => $row->m_type
		);
	}
	/**
	* get id of parent folder
	* @param	integer id of folder
	* @access	public
	*/
	function getParentFolderId($a_obj_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM  '. $this->table_tree .' 
			WHERE child = %s',
			array('integer'),
			array($a_obj_id));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		
		return $row->parent;
	}
	/**
	* get all folders under given node
	* @param	integer	obj_id
	* @param	integer	parent_id
	* @access	public
	*/
	function getSubFolders($a_folder = 0,$a_folder_parent = 0)
	{
	
		global $ilDB;

		if (!$a_folder)
		{
			$a_folder = $this->getRootFolderId();
		}
		
		foreach ($this->default_folder as $key => $value)
		{
			$res = $ilDB->queryf('
				SELECT obj_id,m_type FROM '. $this->table_mail_obj_data .' 
				WHERE user_id = %s
				AND title = %s',
				array('integer', 'text'),
				array($this->user_id, $key));
						
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			
			$user_folder[] = array(
				"title"    => $key,
				"type"     => $row->m_type,
				"obj_id"   => $row->obj_id);
		} 

		$res = $ilDB->queryf('
			SELECT * FROM '. $this->table_tree. ', '. $this->table_mail_obj_data .'
			WHERE '. $this->table_mail_obj_data.'.obj_id = '. $this->table_tree.'.child 
			AND '. $this->table_tree.'.depth  > %s
			AND '. $this->table_tree.'.tree  = %s
			ORDER BY '. $this->table_mail_obj_data.'.title  ',
			array('integer', 'integer'),
			array('2', $this->user_id));
		
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_folder[] = array(
				"title"      => stripslashes($row->title),
				"type"    => $row->m_type,
				"obj_id"  => $row->child);
		}

		return $user_folder;
	}

	/**
	* set user_id
	* @param	integer id of user
	* @access	public
	*/
	function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
	}
	
	/**
	* deletes user's mailbox and all db entries related to mailbox
	* TODO: stefan, bitte nochmal kontrollieren, ob auch wirklich alles gel�scht wird.
	* Vielleicht hab ich was �bersehen. - shofmann, 15.7.03
	*
	* @access	public
	* @return	boolean	true on successful deletion
	*/
	function delete()
	{
		/**
 		 * @var $ilDB ilDB
		 */
		global $ilDB;
		
		$ilDB->manipulateF('
			DELETE FROM mail_obj_data WHERE user_id = %s',
			array('integer'), array($this->user_id)
		);

		$ilDB->manipulateF('
			DELETE FROM mail_options WHERE user_id = %s',
			array('integer'), array($this->user_id)
		);

		$ilDB->manipulateF('
			DELETE FROM mail_saved WHERE user_id = %s',
			array('integer'), array($this->user_id)
		);
		
		$ilDB->manipulateF('
			DELETE FROM mail_tree WHERE tree = %s',
			array('integer'), array($this->user_id)
		);

		// Delete the user's files from filesystem: This has to be done before deleting the database entries in table 'mail'
		require_once 'Services/Mail/classes/class.ilFileDataMail.php';
		$fdm = new ilFileDataMail($this->user_id);
		$fdm->onUserDelete();
		
		// Delete mails of deleted user
		$ilDB->manipulateF(
			'DELETE FROM mail WHERE user_id = %s',
			array('integer'),
			array($this->user_id)
		);

		return true;
	}

	/**
	 * Update existing mails. Set sender id to 0 and import name to login name.
	 * This is only necessary for deleted users.
	 *
	 * @access	public
	 * @param string $nameToShow
	 */
	public function updateMailsOfDeletedUser($nameToShow)
	{
		/**
 		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$ilDB->manipulateF('
			UPDATE mail 
			SET sender_id = %s,
				import_name = %s
			WHERE sender_id = %s',
			array('integer', 'text', 'integer'),
			array(0, $nameToShow, $this->user_id));
	}
}
?>