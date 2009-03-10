<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Mail Box class
* Base class for creating and handling mail boxes
*
* @author Stefan Meyer <smeyer@databay.de>
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
	function ilMailbox($a_user_id = 0)
	{
		global $ilias,$lng;


		$this->ilias = &$ilias;
		$this->lng = &$lng;
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
	function getInboxFolder()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND type = %s',
			array('integer', 'text'),
			array($this->user_id, 'inbox'));

		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		return $row->obj_id;
	}

	/**
	* get Id of the inbox folder of an user
	* @access	public
	*/
	function getDraftsFolder()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND type = %s',
			array('integer', 'text'),
			array($this->user_id, 'drafts'));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		
		return $row->obj_id;
	}

	/**
	* get Id of the trash folder of an user
	* @access	public
	*/
	function getTrashFolder()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND type = %s',
			array('integer', 'text'),
			array($this->user_id, 'trash'));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
				
		return $row->obj_id;
	}

	/**
	* get Id of the sent folder of an user
	* @access	public
	*/
	function getSentFolder()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM '.$this->table_mail_obj_data.'
			WHERE user_id = %s
			AND type = %s',
			array('integer', 'text'),
			array($this->user_id, 'sent'));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		
		return $row->obj_id;
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
	 * check if new mail exists in inbox folder
	 * @access	public
	 * @static
	 * @return	integer id of last mail or 0
	 */
	function hasNewMail($a_user_id)
	{
		global $ilDB;
		global $ilias;

		if (!$a_user_id)
		{
			return 0;
		}

		// CHECK FOR SYSTEM MAIL
		$res = $ilDB->queryf('
			SELECT mail_id FROM mail 
			WHERE folder_id = %s 
			AND user_id = %s
			AND m_status = %s',
			array('integer', 'integer', 'text'),
			array('0', $a_user_id, 'unread'));

		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		if($row->mail_id)
		{
			return $row->mail_id;
		}

		$res = $ilDB->queryf('
			SELECT m.mail_id FROM mail m,mail_obj_data mo 
			WHERE m.user_id = mo.user_id 
			AND m.folder_id = mo.obj_id 
			AND mo.type = %s
			AND m.user_id = %s
			AND m.m_status = %s',
			array('text', 'integer', 'text'),
			array('inbox', $a_user_id, 'unread'));

		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row ? $row->mail_id : 0;
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
		global $ilDB;
		global $ilias;

		if (!$a_user_id)
		{
			return 0;
		}

		// CHECK FOR SYSTEM MAIL
		$res = $ilDB->queryf('
			SELECT count(mail_id) cnt FROM mail 
			WHERE folder_id = %s 
			AND user_id = %s
			AND m_status = %s
			GROUP BY mail_id',
			array('integer', 'integer', 'text'),
			array('0', $a_user_id, 'unread'));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
					
		$res2 = $ilDB->queryf('
			SELECT count(mail_id) as cnt FROM mail m,mail_obj_data mo 
		 	WHERE m.user_id = mo.user_id 
		 	AND m.folder_id = mo.obj_id 
		 	AND mo.type = %s
			AND m.user_id = %s
	 		AND m.m_status = %s
	 		GROUP BY mail_id',
			array('text', 'integer', 'text'),
			array('inbox', $a_user_id, 'unread'));
			
		
		$row2 = $res2->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row->cnt + $row2->cnt;
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
				type
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
					type
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
			 	type 
			 )
			 VALUES(%s,%s,%s,%s)',
			array('integer','integer', 'text', 'text'),
			array($next_id, $this->user_id, $a_folder_name, 'user_folder'));
		
		// ENTRY IN mail_tree
		/*	$new_id = $this->getLastInsertId();
		$this->mtree->insertNode($new_id,$a_parent_id);
		*/	
		$new_id = $ilDB->nextId($this->table_mail_obj_data);	
		$this->mtree->insertNode($new_id,$a_parent_id);	
		
		return $new_id;
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
					"title"    => stripslashes($row->title),
					"type"     => $row->type
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
				SELECT obj_id,type FROM '. $this->table_mail_obj_data .' 
				WHERE user_id = %s
				AND title = %s',
				array('integer', 'text'),
				array($this->user_id, $key));
						
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			
			$user_folder[] = array(
				"title"    => $key,
				"type"     => $row->type,
				"obj_id"   => $row->obj_id);
		} 

		$res = $ilDB->queryf('
			SELECT * FROM '. $this->table_tree. ', .'. $this->table_mail_obj_data .'
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
				"type"    => $row->type,
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
		global $ilDB;

		$data = array($this->user_id);
		
		$statement = $ilDB->manipulateF('
			DELETE FROM mail_obj_data WHERE user_id = %s',
			array('integer'), array($this->user_id)
		);

		$statement = $ilDB->manipulateF('
			DELETE FROM mail_options WHERE user_id = %s',
			array('integer'), array($this->user_id)
		);

		$statement = $ilDB->manipulateF('
			DELETE FROM mail_saved WHERE user_id = %s',
			array('integer'), array($this->user_id)
		);
		
		$statement = $ilDB->manipulateF('
			DELETE FROM mail_tree WHERE tree = %s',
			array('integer'), array($this->user_id)
		);
		
		return true;
	}

	/**
	 * Update existing mails. Set sender id to null and import name to login name.
	 * This is only necessary for deleted users.
	 *
	 * @access	public
	 * @return	boolean	true on successful deletion
	 */
	function updateMailsOfDeletedUser()
	{
		global $ilDB;

		$tmp_user =& ilObjectFactory::getInstanceByObjId($this->user_id,false);

		$statement = $ilDB->manipulateF('
			UPDATE mail 
			SET sender_id = %s,
				import_name = %s
			WHERE sender_id = %s',
			array('integer', 'text', 'integer'),
			array('0', $tmp_user->getLogin(), $this->user_id));
		
		return true;
	}
		
}
?>