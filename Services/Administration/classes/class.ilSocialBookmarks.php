<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Form and GUI functions for Social Bookmark Administration
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
* @package ilias
*/

class ilSocialBookmarks
{
	/**
	* Init Social Bookmark edit/create Form
	*
	* @param        ilObjectGUI	$formhandlerObject        taken as form target
	* @param        int        	$mode        "create" / "edit"
	*/
	public static function _initForm($formhandlerObject, $mode = "create", $id = 0)
	{
		global $lng, $ilCtrl;
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setMultipart(true);
		
		// File Title
		$in_title = new ilTextInputGUI($lng->txt("title"), "title");
		$in_title->setMaxLength(128);
		$in_title->setSize(40);
		$in_title->setRequired(true);
		$form->addItem($in_title);
		
		// Link
		$in_link = new ilTextInputGUI($lng->txt("link"), "link");
		$in_link->setMaxLength(300);
		$in_link->setSize(40);
		$in_link->setRequired(true);
		$in_link->setInfo($lng->txt('socialbm_link_description'));
		$form->addItem($in_link);
		
		// File
		$in_file = new ilFileInputGUI($lng->txt("file"), "image_file");
		$in_file->setSuffixes(array('bmp', 'gif', 'jpg', 'jpeg','png'));
		$form->addItem($in_file);
		
		// Activate on submit
		$in_activate = new ilCheckboxInputGUI($lng->txt("activate"), "activate");
		$in_activate->setValue('1');
		$form->addItem($in_activate);

		// save and cancel commands
		if ($mode == "create")
		{
			$form->addCommandButton("createSocialBookmark", $lng->txt("create"));
			$form->addCommandButton("editSocialBookmarks", $lng->txt("cancel"));
			$form->setTitle($lng->txt("social_bm_create"));
			$in_file->setRequired(true);
		}
		else if ($mode == "update")
		{
			$in_hidden = new ilHiddenInputGUI("sbm_id", $id);
			$form->addItem($in_hidden);

			$form->addCommandButton("updateSocialBookmark", $lng->txt("update"));
			$form->addCommandButton("cancel", $lng->txt("cancel"));
			$form->setTitle($lng->txt("social_bm_edit"));
			$in_file->setRequired(false);
		}
		
		$form->setTableWidth("60%");

		$form->setFormAction($ilCtrl->getFormAction($formhandlerObject));
		return $form;
	}

	/**
	* insert new social bookmark service
	*
	* @param        string		the title to display
	* @param        string        	the link with placeholders {TITLE} and {LINK}
	* @param        int        	activate on insert 0/1
	* @param        string       	a relative path (base is <datadir>/social_bm_icons
	*/	
	public static function _insertSocialBookmark($title, $link, $active, $icon_path)
	{
		global $ilDB;

		$id = $ilDB->nextId('bookmark_social_bm');

		$q = 'INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)';
		$ilDB->manipulateF
		(
			$q,
			array('integer', 'text', 'text', 'text', 'integer'),
			array($id, $title, $link, $icon_path, $active)
		);
		
	}

	/**
	* update a social bookmark service
	*
	* @param	int		the id to edit
	* @param        string		the title to display
	* @param        string        	the link with placeholders {TITLE} and {LINK}
	* @param        int        	activate on insert 0/1
	* @param        string       	a relative path (base is <datadir>/social_bm_icons. If none is given, the icon will not be altered
	*/	
	public static function _updateSocialBookmark($id, $title, $link, $active, $icon_path = false)
	{
		global $ilDB;

		if ($icon_path)
		{
			$q = 'UPDATE bookmark_social_bm SET sbm_title=%s, sbm_link=%s, sbm_icon=%s, sbm_active=%s WHERE sbm_id=%s';
			$ilDB->manipulateF
			(
				$q,
				array('text', 'text', 'text', 'integer', 'integer'),
				array($title, $link, $icon_path, $active, $id)
			);
		}
		else
		{
			$q = 'UPDATE bookmark_social_bm SET sbm_title=%s, sbm_link=%s, sbm_active=%s WHERE sbm_id=%s';
			$ilDB->manipulateF
			(
				$q,
				array('text', 'text', 'integer', 'integer'),
				array($title, $link, $active, $id)
			);
		}
	}
	
	/**
	* update a social bookmark service
	*
	* @param	int		the id to edit
	* @param        bool		set the active status to param
	*/	
	public static function _setActive($id, $active = true)
	{
		global $ilDB;

		if (!is_array($id))
		{
			$q = 'UPDATE bookmark_social_bm SET sbm_active=%s WHERE sbm_id=%s';
			$ilDB->manipulateF
			(
				$q,
				array('integer', 'integer'),
				array($active, $id)
			);
		}
		else if (count($id))
		{
			$q = 'UPDATE bookmark_social_bm SET sbm_active=%s WHERE ';
			$parts = array();
			foreach($id as $i)
			{
				$parts[] = 'sbm_id=' . $ilDB->quote($i, 'integer');
			}
			$q .= ' ' . join(' OR ', $parts);
			$ilDB->manipulateF
			(
				$q,
				array('integer'),
				array($active)
			);
		}
	}

	/**
	* update a social bookmark service
	*
	* @param	int		the id to edit
	* @param        bool		set the active status to param
	*/	
	public static function _delete($id)
	{
		global $ilDB;

		if (!is_array($id))
		{
			self::_deleteImage($id);

			$q = 'DELETE FORM bookmark_social_bm WHERE sbm_id=%s';
			$ilDB->manipulateF
			(
				$q,
				array('integer'),
				array($id)
			);
		}
		else if (count($id))
		{
			$q = 'DELETE FROM bookmark_social_bm WHERE ';
			$parts = array();
			foreach($id as $i)
			{
				self::_deleteImage($i);
				$parts[] = 'sbm_id=' . $ilDB->quote($i, 'integer');
			}
			$q .= ' ' . join(' OR ', $parts);
			$ilDB->manipulateF
			(
				$q,
				array('integer'),
				array($active)
			);
		}
	}

	/**
	* delete image of an service
	*
	* @param	int		the id to edit
	*/	
	public static function _deleteImage($id)
	{
		global $ilDB;
		$q = 'SELECT sbm_icon FROM bookmark_social_bm WHERE sbm_id=%s';
		$rset = $ilDB->queryF
		(
			$q,
			array('integer'),
			array($id)
		);
		$row = $ilDB->fetchObject($rset);
		//$path = ilUtil::getWebspaceDir() . DIRECTORY_SEPARATOR . 'social_bm_icons' . DIRECTORY_SEPARATOR . $row->sbm_icon;
		if ($row->sbm_icon && is_file($row->sbm_icon) && substr($row->sbm_icon, 0, strlen('templates')) != 'templates')
			unlink($row->sbm_icon);
	}

	public static function _getEntry($id = 0)
	{
		global $ilDB;
		if ($id)
		{
			$q = 'SELECT sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active FROM bookmark_social_bm WHERE sbm_id=%s';
			$rset = $ilDB->queryF
			(
				$q,
				array('integer'),
				array($id)
			);

			return $ilDB->fetchObject($rset);
		}
		else
		{
			$q = 'SELECT sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active FROM bookmark_social_bm';
			$rset = $ilDB->query($q);

			$result = array();
			while($row = $ilDB->fetchObject($rset))
			{
				$result[] = $row;
			}
			return $result;
		}
	}
}
